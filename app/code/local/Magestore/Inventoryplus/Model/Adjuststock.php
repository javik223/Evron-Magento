<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Adjuststock extends Mage_Core_Model_Abstract {

    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_CANCELED = 2;
    const STATUS_PROCESSING = 3;

    protected $_qtyField = 'adjust_qty';
    protected $_adjustStep = 300;


    public function _construct() {
        parent::_construct();
        $this->_init('inventoryplus/adjuststock');
    }

    /**
     * Create new adjust stock
     * 
     * @param array $data
     * @param \Magestore_Inventoryplus_Model_Warehouse $warehouse
     * @return \Magestore_Inventoryplus_Model_Adjuststock
     */
    public function create($data, $warehouse) {
        $this->setWarehouseId($warehouse->getId())
                ->setWarehouseName($warehouse->getWarehouseName())
                ->setCreatedAt(now())
                ->setReason($data['reason'])
                ->setData('create_by', $data['created_by'])
                ->setStatus(self::STATUS_PENDING)
        ;
        $this->setData('stock_data', $data['adjuststock_products']);
        $this->save();
        return $this;
    }

    /**
     * Process adjust stock
     * 
     * @param array $data
     * @return \Magestore_Inventoryplus_Model_Adjuststock
     */
    public function doAdjust($data) {
        if (isset($data['reason'])) {
            $this->setReason($data['reason']);
        }
        if (isset($data['adjuststock_products'])) {
            $this->setStockData($data['adjuststock_products']);
        }

        $this->doAdjustByStep();
        if (!count($this->getNeedUpdateStocks())) {
            /* finished */
            $this->confirm($data);
        } else {
            $this->setStatus(self::STATUS_PROCESSING);
        }
        $this->save();

        return $this;
    }

    /**
     * Save products to adjust stock
     * 
     * 
     */
    public function saveProducts() {
        $processStocks = $this->getNeedUpdateStocks();
        $updateAdjustProductSql = $this->_prepareUpdateAdjustProduct($processStocks);
        if (!$updateAdjustProductSql)
            return $this;
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $writeConnection->beginTransaction();
            $writeConnection->query($updateAdjustProductSql);
            $writeConnection->commit();
        } catch (Exception $e) {
            $writeConnection->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * Process adjust stocks step by step
     * 
     * @return int
     */
    public function doAdjustByStep() {
        $processStocks = $this->getProcessStocks();
        if (!count($processStocks)) {
            return 0;
        }
        $updateWarehouseProductSql = $this->_prepareUpdateWarehouseProduct($processStocks);
        $updateCatalogProductSql = $this->_prepareUpdateCatalogProduct($processStocks);
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $writeConnection->beginTransaction();
            if ($updateWarehouseProductSql)
                $writeConnection->query($updateWarehouseProductSql);
            if ($updateCatalogProductSql)
                $writeConnection->query($updateCatalogProductSql);
            $writeConnection->commit();
        } catch (Exception $e) {
            $writeConnection->rollback();
            throw $e;
        }
        return count($processStocks);
    }

    /**
     * Prepare update adjust product sql
     * 
     * @param array $processStocks
     * @return string
     */
    protected function _prepareUpdateAdjustProduct(&$processStocks) {
        $updateSql = null;
        $adjustProducts = $this->getArrayProducts(array_keys($processStocks));
        $warehouseProducts = $this->getWarehouse()->getArrayProducts(array_keys($processStocks));
        foreach ($processStocks as $productId => $qty) {
            $oldQty = 0;
            if (isset($warehouseProducts[$productId])) {
                $oldQty = $warehouseProducts[$productId]->getTotalQty();
            }
            $qtyChanged = $qty['adjust'] - $oldQty;
            if ($qtyChanged == 0) {
                /* don't change product qty */
                //continue;
            }
            if (isset($adjustProducts[$productId])) {
                /* product existed in warehouse */
                $adjustProduct = $adjustProducts[$productId];
                //$oldQty = $adjustProduct->getAdjustQty();
                /* change product qty in warehouse */
                $updateSql .= ' UPDATE ' . $this->getResource()->getTable('inventoryplus/adjuststock_product')
                        . ' SET `old_qty` = \'' . $oldQty . '\', '
                        . ' `adjust_qty` = \'' . $qty['adjust'] . '\''
                        . ' WHERE `adjuststock_product_id` =' . $adjustProduct->getId() . ';';
            } else {
                /* add product to adjust */
                $updateSql .= ' INSERT INTO ' . $this->getResource()->getTable('inventoryplus/adjuststock_product')
                        . ' (`adjuststock_id`, `product_id`, `old_qty`, `adjust_qty`)'
                        . " VALUES ('" . $this->getId() . "', '$productId', '$oldQty', '" . $qty['adjust'] . "');";
            }
        }

        $updateSql .= 'DELETE FROM ' . $this->getResource()->getTable('inventoryplus/adjuststock_product')
                . " WHERE `adjuststock_id` = '" . $this->getId() . "'"
                . " AND `product_id` NOT IN ('" . implode("','", array_keys($processStocks)) . "');";

        return $updateSql;
    }

    /**
     * Prepare update warehouse product sql
     * 
     * @param array $processStocks
     * @return string
     */
    protected function _prepareUpdateWarehouseProduct(&$processStocks) {
        $updateSql = null;
        $warehouseProducts = $this->getWarehouse()->getArrayProducts(array_keys($processStocks));
        foreach ($processStocks as $productId => $qty) {
            if (isset($warehouseProducts[$productId])) {
                $this->setLastProductId($productId);
                /* product existed in warehouse */
                $warehouseProduct = $warehouseProducts[$productId];
                $qtyChanged = $qty['adjust'] - $warehouseProduct->getTotalQty();
                $processStocks[$productId]['change'] = $qtyChanged;
                if ($qtyChanged == 0) {
                    /* don't change product qty in warehouse */
                    continue;
                }
                $oldQtyAvailable = $warehouseProduct->getAvailableQty();
                $newQtyAvailable = $oldQtyAvailable + $qtyChanged;
                /* change product qty in warehouse */
                $updateSql .= ' UPDATE ' . $this->getResource()->getTable('inventoryplus/warehouse_product')
                        . ' SET `total_qty` = \'' . $qty['adjust'] . '\', '
                        . ' `available_qty` = \'' . $newQtyAvailable . '\''
                        . ' WHERE `warehouse_product_id` =' . $warehouseProduct->getId() . ';';
            } else {
                $processStocks[$productId]['change'] = $qty['adjust'];
                /* add product to warehouse */
                $updateSql .= ' INSERT INTO ' . $this->getResource()->getTable('inventoryplus/warehouse_product')
                        . ' (`warehouse_id`, `product_id`, `total_qty`, `available_qty`)'
                        . " VALUES ('" . $this->getWarehouse()->getId() . "', '$productId', '" . $qty['adjust'] . "', '" . $qty['adjust'] . "');";
            }
        }
        return $updateSql;
    }

    /**
     * Prepare update catalog product sql
     * 
     * @param array $processStocks
     * @return string
     */
    protected function _prepareUpdateCatalogProduct(&$processStocks) {
        $updateSql = null;
        $processProducts = array();
        $products = Mage::getResourceModel('catalog/product_collection')
                ->addFieldToFilter('entity_id', array('in' => array_keys($processStocks)))
        ;
        if ($products->getSize()) {
            foreach ($products as $product)
                $processProducts[$product->getId()] = $product;
        }
        $thresholdStockStatus = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
        $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
        $backorders = Mage::getStoreConfig('cataloginventory/item_options/backorders', Mage::app()->getStore()->getStoreId());
        foreach ($processStocks as $productId => $qty) {
            $stockStatus = Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK;
            $this->setLastProductId($productId);
            if (!isset($qty['change']) || !$qty['change']) {
                /* do not update stock in catalog */
                continue;
            }
            $product = $processProducts[$productId];
            if ($product->isComposite()) {
                /* do nothing */
                continue;
            }
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if (!$stockItem->getUseConfigManageStock()) {
                $manageStock = $stockItem->getManageStock();
            }
            if (!$manageStock) {
                continue;
            }
            if (!$stockItem->getUseConfigBackorders()) {
                $backorders = $stockItem->getBackorders();
            }
            if (!$stockItem->getUseConfigMinQty()) {
                $thresholdStockStatus = $stockItem->getMinQty();
            }
            if ($stockItem->getQty() + $qty['change'] <= $thresholdStockStatus) {
                $stockStatus = Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;
            }
            $stockStatus = $backorders ? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : $stockStatus;

            $updateSql .= ' UPDATE ' . $this->getResource()->getTable('cataloginventory/stock_item')
                    . ' SET `qty` = `qty` + ' . $qty['change'] . ', `is_in_stock` = ' . $stockStatus
                    . ' WHERE (`product_id` = ' . $productId . ');';
            $updateSql .= ' UPDATE ' . $this->getResource()->getTable('cataloginventory/stock_status')
                    . ' SET `qty` = `qty` + ' . $qty['change'] . ', `stock_status` = ' . $stockStatus
                    . ' WHERE (`product_id` = ' . $productId . ');';
        }
        return $updateSql;
    }

    /**
     * Get stocks needed to process
     * 
     * @return array[$productId=>$adjustQty]
     */
    public function getProcessStocks() {
        $needUpdateStocks = $this->getNeedUpdateStocks();
        if (!count($needUpdateStocks))
            return array();
        $processStocks = array();
        foreach ($needUpdateStocks as $productId => $qty) {
            $processStocks[$productId]['adjust'] = $qty['adjust'];
            if (count($processStocks) >= $this->_adjustStep) {
                break;
            }
        }
        return $processStocks;
    }
    
    /**
     * Get all needed update stocks
     * 
     * @return array
     */
    public function getNeedUpdateStocks() {
        $stockData = $this->prepareStockData();
        if (!count($stockData))
            return array();
        $lastProductId = $this->getLastProductId();
        $needUpdateStocks = array();
        $startResum = $lastProductId ? false : true;
        foreach ($stockData as $productId => $value) {
            if ($startResum) {
                $valueArr = array();
                parse_str(base64_decode($value), $valueArr);
                $needUpdateStocks[$productId]['adjust'] = $valueArr[$this->_qtyField];
            }
            if ($productId == $lastProductId) {
                $startResum = true;
            }
        }
        return $needUpdateStocks;
    }

    /**
     * Get products in an adjust
     * 
     * @param array $productIds
     * @return \Magestore_Inventoryplus_Model_Adjuststock_Product
     */
    public function getProducts($productIds = array()) {
        $products = Mage::getResourceModel('inventoryplus/adjuststock_product_collection')
                ->addFieldToFilter('adjuststock_id', $this->getId());
        if (count($productIds)) {
            $products->addFieldToFilter('product_id', array('in' => $productIds));
        }
        return $products;
    }

    /**
     * Get array products from an adjust
     * 
     * @param array $productIds
     * @return array
     */
    public function getArrayProducts($productIds = array()) {
        $list = array();
        $products = $this->getProducts($productIds);
        if (count($products)) {
            foreach ($products as $product) {
                $list[$product->getProductId()] = $product;
            }
        }
        return $list;
    }

    /**
     * Convert stock data to array
     * 
     * @return array
     */
    public function prepareStockData() {
        $adjuststockProducts = array();
        $adjuststockProductsExplodes = explode('&', urldecode($this->getStockData()));
        if (count($adjuststockProductsExplodes) <= 900) {
            parse_str(urldecode($this->getStockData()), $adjuststockProducts);
        } else {
            foreach ($adjuststockProductsExplodes as $adjuststockProductsExplode) {
                $adjuststockProduct = '';
                parse_str($adjuststockProductsExplode, $adjuststockProduct);
                $adjuststockProducts = $adjuststockProducts + $adjuststockProduct;
            }
        }
        return $adjuststockProducts;
    }

    /**
     * Confirm stock adjust
     * 
     * @param array $data
     * @return \Magestore_Inventoryplus_Model_Adjuststock
     */
    public function confirm($data) {
        $this->setData('confirmed_by', $data['created_by'])
                ->setData('confirmed_at', now())
                ->setStatus(self::STATUS_COMPLETED);
        $this->save();
        return $this;
    }

    /**
     * Get warehouse
     * 
     * @return \Magestore_Inventoryplus_Model_Warehouse
     */
    public function getWarehouse() {
        if (!$this->getData('warehouse')) {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($this->getWarehouseId());
            $this->setData('warehouse', $warehouse);
        }
        return $this->getData('warehouse');
    }
    
    public function setQtyField($field){
        $this->_qtyField = $field;
    }
    
    public function setAdjustStep($total){
        $this->_adjustStep = $total;
    }

}
