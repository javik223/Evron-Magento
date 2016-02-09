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
class Magestore_Inventoryplus_Model_Install extends Mage_Core_Model_Abstract {

    const STATUS_PROCESSING = 0;
    const STATUS_COMPLETED = 1;
    const IMPORT_PRODUCT_STEP = 50;
    const IMPORT_SHIPPING_PROGRESS_STEP = 50;

    public function _construct() {
        parent::_construct();
        $this->_init('inventoryplus/install');
    }

    /**
     * Get warehouse
     * 
     * @return Magestore_Inventoryplus_Model_Warehouse
     */
    public function getWarehouse() {
        if (!$this->getData('warehouse')) {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
            $this->setData('warehouse', $warehouse);
        }
        return $this->getData('warehouse');
    }

    /**
     * Get existing products in Magento store
     * 
     * @return array
     */
    public function prepareMagentoProductData() {
        if (!$this->getData('magento_product_data')) {
            $productIds = array();
            $productCollection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSelect('sku')
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('type_id')
                    ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
            if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
                $productCollection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'inner');
            }
            foreach ($productCollection as $product) {
                $productIds[$product->getId()] = $product->getQty();
            }
            $this->setData('magento_product_data', $productIds);
        }

        return $this->getData('magento_product_data');
    }

    /**
     * Get existing order data
     *
     * @return array
     */
    public function prepareShippingProgressData() {
        if (!$this->getData('shipping_progress_data')) {
            $data = array();
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $sql = "SELECT `total_qty_ordered`,`entity_id`,`status` FROM `" . $resource->getTableName('sales/order') . "`";
            $orders = $readConnection->fetchAll($sql);

            foreach ($orders as $order) {
                $data[$order['entity_id']] = array (
                    'total_qty_ordered' => $order['total_qty_ordered'],
                    'status' => $order['status']
                );
            }
            $this->setData('shipping_progress_data', $data);
        }

        return $this->getData('shipping_progress_data');
    }

    /**
     * Get the remaining product collection need to be imported
     * 
     * @return array
     */
    public function getNeedImportProducts() {
        $magentoProductData = $this->prepareMagentoProductData();
        if (count($magentoProductData) == 0) {
            return array();
        }
        $lastProductId = $this->getLastProductId();
        $needImportProducts = array();
        $startResume = $lastProductId ? false : true;
        foreach ($magentoProductData as $productId => $productQty) {
            if ($startResume) {
                $needImportProducts[$productId] = $productQty;
            }
            if ($productId == $lastProductId) {
                $startResume = true;
            }
        }

        return $needImportProducts;
    }

    /**
     * Get the remaining shipping progress need to be imported
     *
     * @return array
     */
    public function getNeedImportShippingProgress() {
        $shippingProgressData = $this->prepareShippingProgressData();
        if (count($shippingProgressData) == 0) {
            return array();
        }
        $lastShippingProgressOrderId = $this->getLastShippingProgressOrderId();
        $needImportShippingProgress = array();
        $startResume = $lastShippingProgressOrderId ? false : true;
        foreach ($shippingProgressData as $orderId => $value) {
            if ($startResume) {
                $needImportShippingProgress[$orderId] = array(
                    'total_qty_ordered' => $value['total_qty_ordered'],
                    'status' => $value['status']
                );
            }
            if ($orderId == $lastShippingProgressOrderId) {
                $startResume = true;
            }
        }

        return $needImportShippingProgress;
    }

    /**
     * Get a portion of remaining product collection to import in one step
     * 
     * @return collection
     */
    public function getProcessProducts() {
        $needImportProducts = $this->getNeedImportProducts();
        if (count($needImportProducts) == 0) {
            return array();
        }
        $processProducts = array();
        foreach ($needImportProducts as $productId => $productQty) {
            $processProducts[$productId] = $productQty;
            if (count($processProducts) >= self::IMPORT_PRODUCT_STEP) {
                break;
            }
        }
        return $processProducts;
    }

    /**
     * Get a portion of remaining shipping progress to import in one step
     *
     * @return array
     */
    public function getProcessShippingProgress() {
        $needImportShippingProgress = $this->getNeedImportShippingProgress();
        if (count($needImportShippingProgress) == 0) {
            return array();
        }
        $processShippingProgress = array();
        foreach ($needImportShippingProgress as $orderId => $value) {
            $processShippingProgress[$orderId] = array(
                'total_qty_ordered' => $value['total_qty_ordered'],
                'status' => $value['status']
            );
            if (count($processShippingProgress) >= self::IMPORT_SHIPPING_PROGRESS_STEP) {
                break;
            }
        }
        return $processShippingProgress;
    }

    /**
     * Get order items collection
     * 
     * @param int $productId
     * @return collection
     */
    protected function _getOrderItems($productId)
    {
        $resource = Mage::getModel('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $orderItems = $readConnection->fetchAll("SELECT `item_id`,`order_id`,`qty_ordered`,`qty_canceled`,`qty_shipped`,`qty_refunded`, `parent_item_id` 
                                                            FROM " . $resource->getTableName('sales/order_item') . " as `orderitem`"
            . " JOIN " . $resource->getTableName('sales/order') . " as `order`"
            . " ON orderitem.order_id = order.entity_id"
            . " AND orderitem.product_id = " . $productId
            . " AND order.status NOT IN ('complete', 'canceled', 'closed')");
        return $orderItems;
    }

    /**
     * Get order items collection by parent item id
     * 
     * @param int $parentItemId
     * @return collection
     */
    protected function _getOrderParentItems($parentItemId)
    {
        $resource = Mage::getModel('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $orderParentItems = $readConnection->fetchAll("SELECT `qty_shipped`,`qty_ordered`,`qty_canceled`,`product_type` 
                                                                                 FROM " . $resource->getTableName('sales/order_item') . " as `orderitem`"
            . " JOIN " . $resource->getTableName('sales/order') . " as `order`"
            . " ON orderitem.order_id = order.entity_id"
            . " AND orderitem.item_id = " . $parentItemId
            . " AND order.status NOT IN ('complete', 'canceled', 'closed')");
        return $orderParentItems;
    }

    /**
     * Get array of warehouse product data to insert to table erp_inventory_warehouse_product
     * 
     * @param array $processProducts
     * @return array
     */
    protected function _prepareImportWarehouseProduct($processProducts) {
        foreach ($processProducts as $productId => $productQty) {
            $qtyNotShipped = array();
            $this->setLastProductId($productId);

            // Get manage stock
            $manageStock = Mage::helper('inventoryplus')->getManageStockOfProduct($productId);

            // Get import warehouse product array
            if (!$manageStock) {
                $importWarehouseProduct[] = array(
                    'product_id' => $productId,
                    'warehouse_id' => $this->getWarehouse()->getId(),
                    'total_qty' => 0,
                    'available_qty' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
            } else {
                $orderItems = $this->_getOrderItems($productId);
                foreach ($orderItems as $orderItem) {
                    $qtyOrdered = $orderItem['qty_ordered'];
                    $qtyCanceled = $orderItem['qty_canceled'];
                    $qtyShipped = $orderItem['qty_shipped'];
                    $qtyRefunded = $orderItem['qty_refunded'];
                    $parentItemId = $orderItem['parent_item_id'];
                    if ($parentItemId) {
                        $orderParentItems = $this->_getOrderParentItems($parentItemId);
                        if ($qtyShipped == 0) {
                            foreach ($orderParentItems as $p) {
                                if ($p['product_type'] == 'configurable') {
                                    $qtyShipped = $p['qty_shipped'];
                                }
                            }
                        }
                        if ($qtyOrdered == 0) {
                            foreach ($orderParentItems as $p) {
                                $qtyOrdered = $p['qty_ordered'];
                            }
                        }
                        if ($qtyCanceled == 0) {
                            $qtyCanceled = $p['qty_canceled'];
                        }
                    }
                    $qtyNotShip = $qtyOrdered - $qtyCanceled - max($qtyShipped, $qtyRefunded);
                    if ($qtyNotShip > 0) {
                        try {
                            $this->getWarehouse()->setWarehouseOrderItem($orderItem, $productId, $qtyNotShip);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $qtyNotShipped[] = $qtyNotShip;
                }
                $qtyForDefault = (int) array_sum($qtyNotShipped) + (int) $productQty;
                $importWarehouseProduct[] = array(
                    'product_id' => $productId,
                    'warehouse_id' => $this->getWarehouse()->getId(),
                    'total_qty' => $qtyForDefault,
                    'available_qty' => (int) $productQty,
                    'created_at' => now(),
                    'updated_at' => now()
                );
            }
        }
        return $importWarehouseProduct;
    }

    /**
     * Get sql string of shipping progress to insert to table sales_flat_order
     *
     * @param $processShippingProgress
     * @return string
     */
    protected function _prepareImportShippingProgress($processShippingProgress) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $importShippingProgressSql = '';
        foreach ($processShippingProgress as $orderId => $value) {
            $this->setLastShippingProgressOrderId($orderId);
            if ($value['status'] == 'complete') {
                $shippingProgress = 2;
            } else if ($value['status'] == 'canceled') {
                $shippingProgress = 3;
            } else if ($value['status'] == 'closed') {
                $shippingProgress = 4;
            } else {
                $totalQtyOrder = $value['total_qty_ordered'];
                $totalQtyShipped = array();
                $orderItems = $readConnection->fetchAll("SELECT `qty_shipped`, `qty_ordered`, `product_type`, `parent_item_id` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (order_id = $orderId)");
                foreach ($orderItems as $c) {
                    if ($c['parent_item_id'] == null) {
                        if ($c['product_type'] == 'virtual' || $c['product_type'] == 'downloadable') {
                            $totalQtyOrder += -(int)$c['qty_ordered'];
                        }
                        $totalQtyShipped[] = $c['qty_shipped'];
                    }
                }
                $totalProductsShipped = array_sum($totalQtyShipped);
                //end get total qty shipped
                //set status for shipment
                if ($totalQtyOrder == 0) {
                    $shippingProgress = 2;
                } else {
                    if ((int)$totalProductsShipped == 0) {
                        $shippingProgress = 0;
                    } elseif ((int)$totalProductsShipped < (int)$totalQtyOrder) {
                        $shippingProgress = 1;
                    } elseif ((int)$totalProductsShipped == (int)$totalQtyOrder) {
                        $shippingProgress = 2;
                    }
                }
            }
            $importShippingProgressSql .= 'UPDATE ' . $resource->getTableName('sales/order') . '
                                SET `shipping_progress` = \'' . $shippingProgress . '\'
                                     WHERE `entity_id` =' . $orderId . ';';
        }
        return $importShippingProgressSql;
    }

    public function doInstallByStep($type) {
        $resource = Mage::getModel('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        switch ($type) {
            case 'Import Product':
                $processProducts = $this->getProcessProducts();
                if (count($processProducts) == 0) {
                    return 0;
                }
                $importWarehouseProduct = $this->_prepareImportWarehouseProduct($processProducts);

                try {
                    $writeConnection->beginTransaction();
                    if ($importWarehouseProduct) {
                        $writeConnection->insertMultiple($resource->getTableName('inventoryplus/warehouse_product'), $importWarehouseProduct);
                    }
                    $writeConnection->commit();
                } catch (Exception $e) {
                    $writeConnection->rollback();
                }
                break;
            case 'Import Shipping Progress':
                $processShippingProgress = $this->getProcessShippingProgress();
                if (count($processShippingProgress) == 0) {
                    return 0;
                }
                $importShippingProgressSql = $this->_prepareImportShippingProgress($processShippingProgress);
                try {
                    $writeConnection->beginTransaction();
                    if ($importShippingProgressSql) {
                        $writeConnection->query($importShippingProgressSql);
                    }
                    $writeConnection->commit();
                } catch (Exception $e) {
                    $writeConnection->rollback();
                }
                break;
            case 'Finished':
                break;
            default:
                break;
        }
    }

    public function doInstall($type) {
        $this->doInstallByStep($type);
        if (count($this->getNeedImportProducts()) == 0 && count($this->getNeedImportShippingProgress()) == 0) {
            /* install finished */
            $this->setStatus(self::STATUS_COMPLETED);
        } else {
            $this->setStatus(self::STATUS_PROCESSING);
        }
        $this->save();

        return $this;
    }

}
