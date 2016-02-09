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
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Model_Physicalstocktaking extends Mage_Core_Model_Abstract {

    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_CANCELED = 2;

    public function _construct() {
        parent::_construct();
        $this->_init('inventoryphysicalstocktaking/physicalstocktaking');
    }

    /**
     * Create new adjust stock
     * 
     * @param array $data
     * @param \Magestore_Inventoryplus_Model_Warehouse $warehouse
     * @return \Magestore_Inventoryphysicalstocktaking_Model_Physicalstocktaking
     */
    public function create($data, $warehouse) {
        $this->setWarehouseId($warehouse->getId())
                ->setWarehouseName($warehouse->getWarehouseName())
                ->setCreatedAt(now())
                ->setData('create_by', $data['created_by'])
                ->setStatus(self::STATUS_PENDING)
        ;
        $this->save();
        return $this;
    }

    /**
     * Confirm physical stock-taking
     * 
     * @param array $data
     * @return \Magestore_Inventoryphysicalstocktaking_Model_Physicalstocktaking
     */
    public function confirm($data) {
        $this->setData('confirm_by', $data['created_by'])
                ->setData('confirm_at', now())
                ->setStatus(self::STATUS_COMPLETED);
        $this->save();
        return $this;
    }
    /**
     * Confirm stock-taking
     * 
     * @return \Magestore_Inventoryplus_Model_Adjuststock
     */
    public function confirmAdjust() {
        $adjuststockData['warehouse_id'] = $this->getWarehouse()->getId();
        $adjuststockData['warehouse_name'] = $this->getWarehouse()->getWarehouseName();
        $adjuststockData['file_path'] = $this->getFilePath();
        $adjuststockData['created_at'] = now();
        $adjuststockData['created_by'] = $this->getData('create_by');
        $adjuststockData['reason'] = $this->getData('reason');
        $adjuststockData['status'] = Magestore_Inventoryplus_Model_Adjuststock::STATUS_PENDING;
        $adjuststockData['adjuststock_products'] = $this->getData('physicalstocktaking_products');
        $adjustModel = Mage::getModel('inventoryplus/adjuststock');
        $adjustModel->setData($adjuststockData);
        $adjustModel->create($adjuststockData, $this->getWarehouse());
        $adjustModel->saveProducts();
        return $adjustModel;
    }

    /**
     * Save products to adjust stock
     * 
     * 
     */
    public function saveProducts() {
        $processStocks = $this->getNeedUpdateStocks();
        $updateStockTakingProductSql = $this->_prepareUpdateStockTakingProduct($processStocks);
        if (!$updateStockTakingProductSql)
            return $this;
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $writeConnection->beginTransaction();
            $writeConnection->query($updateStockTakingProductSql);
            $writeConnection->commit();
        } catch (Exception $e) {
            $writeConnection->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * Prepare update stock-taking product sql
     * 
     * @param array $processStocks
     * @return string
     */
    protected function _prepareUpdateStockTakingProduct(&$processStocks) {
        $updateSql = null;
        $processProducts = $this->getArrayProducts(array_keys($processStocks));
        $warehouseProducts = $this->getWarehouse()->getArrayProducts(array_keys($processStocks));
        foreach ($processStocks as $productId => $qty) {
            $oldQty = 0;
            if (isset($warehouseProducts[$productId])) {
                $oldQty = $warehouseProducts[$productId]->getTotalQty();
            }
            if (isset($processProducts[$productId])) {
                /* product existed in warehouse */
                $processProduct = $processProducts[$productId];
                $qtyChanged = $qty['adjust'] - $processProduct->getAdjustQty();
                if ($qtyChanged == 0) {
                    /* don't change product qty */
                    continue;
                }
                /* update ajust qty */
                $updateSql .= ' UPDATE ' . $this->getResource()->getTable('inventoryphysicalstocktaking/physicalstocktaking_product')
                        . ' SET `old_qty` = \'' . $oldQty . '\', '
                        . ' `adjust_qty` = \'' . $qty['adjust'] . '\''
                        . ' WHERE `physicalstocktakingproduct_id` =' . $processProduct->getId() . ';';
            } else {
                /* add product to physical stock-taking */
                $updateSql .= ' INSERT INTO ' . $this->getResource()->getTable('inventoryphysicalstocktaking/physicalstocktaking_product')
                        . ' (`physicalstocktaking_id`, `product_id`, `old_qty`, `adjust_qty`)'
                        . " VALUES ('" . $this->getId() . "', '$productId', '$oldQty', '" . $qty['adjust'] . "');";
            }
        }

        $updateSql .= 'DELETE FROM ' . $this->getResource()->getTable('inventoryphysicalstocktaking/physicalstocktaking_product')
                    . " WHERE `physicalstocktaking_id` = '".$this->getId()."'"
                    . " AND `product_id` NOT IN ('". implode("','",array_keys($processStocks)) ."');";

        return $updateSql;
    }

    /**
     * 
     * @return array
     */
    public function getNeedUpdateStocks() {
        $stockData = $this->prepareStockData();
        if (!count($stockData))
            return array();
        $needUpdateStocks = array();
        foreach ($stockData as $productId => $value) {
            $valueArr = array();
            parse_str(base64_decode($value), $valueArr);
            $needUpdateStocks[$productId]['adjust'] = $valueArr['adjust_qty'];
        }
        return $needUpdateStocks;
    }

    /**
     * Convert stock data to array
     * 
     * @return array
     */
    public function prepareStockData() {
        $stockProducts = array();
        if (!$this->getData('physicalstocktaking_products'))
            return $stockProducts;
        $stockProductsExplodes = explode('&', urldecode($this->getData('physicalstocktaking_products')));
        if (count($stockProductsExplodes) <= 900) {
            parse_str(urldecode($this->getData('physicalstocktaking_products')), $stockProducts);
        } else {
            foreach ($stockProductsExplodes as $stockProductsExplode) {
                $stockProduct = '';
                parse_str($stockProductsExplode, $stockProduct);
                $stockProducts = $stockProducts + $stockProduct;
            }
        }
        return $stockProducts;
    }

    /**
     * Get products in an adjust
     * 
     * @param array $productIds
     * @return \Magestore_Inventoryphysicalstocktaking_Model_Mysql4_Physicalstocktaking_Product_Collection
     */
    public function getProducts($productIds = array()) {
        $products = Mage::getResourceModel('inventoryphysicalstocktaking/physicalstocktaking_product_collection')
                ->addFieldToFilter('physicalstocktaking_id', $this->getId());
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
     * Cancel
     * 
     * @return \Magestore_Inventoryphysicalstocktaking_Model_Physicalstocktaking
     */
    public function cancel() {
        $this->setStatus(self::STATUS_CANCELED)
                ->save();
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

}
