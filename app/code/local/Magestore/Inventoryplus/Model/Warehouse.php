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
class Magestore_Inventoryplus_Model_Warehouse extends Mage_Core_Model_Abstract {

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'inventoryplus_warehouse';
    
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'warehouse';
    
    
    public function _construct() {
        parent::_construct();
        $this->_init('inventoryplus/warehouse');
    }

    public function toOptionArray() {
        $warehouseCollection = $this->getCollection();
        foreach ($warehouseCollection as $warehouse) {
            $key = $warehouse->getWarehouseId();
            $value = $warehouse->getWarehouseName();
            $options[$key] = $value;
        }
        return $options;
    }

    /**
     * Get product collection in warehouse
     * 
     * @param array $productIds
     * @return \Magestore_Inventoryplus_Model_Warehouse_Product_Collection
     */
    public function getProducts($productIds = array()) {
        $products = Mage::getResourceModel('inventoryplus/warehouse_product_collection                           ')
                ->addFieldToFilter('warehouse_id', $this->getId());
        if (count($productIds)) {
            $products->addFieldToFilter('product_id', array('in' => $productIds));
        }
        return $products;
    }

    /**
     * Get array products in warehouse
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
    
    public function getWarehouseOrderItems() {
        $orderItems = Mage::get('inventoryplus/warehouse_order')->getCollection()
                ->addFieldToFilter('warehouse_id', $this->getId());
        return $orderItems;
    }
    
    public function getWarehouseOrderItem($id) {
        $warehouseOrderItem = Mage::getModel('inventoryplus/warehouse_order')->load($id);
        return $warehouseOrderItem;
    }

    public function setWarehouseOrderItem($orderItem, $productId, $qty) {
        $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order');
        $warehouseOrder->setOrderId($orderItem['order_id'])
                ->setWarehouseId($this->getId())
                ->setProductId($productId)
                ->setItemId($orderItem['item_id'])
                ->setQty($qty)
                ->save();
    }
    /**
     * Update order item to Warehouse
     * 
     * @param string $itemId
     * @param float $qty
     * @return \Magestore_Inventoryplus_Model_Warehouse
     */
    public function updateOrderItem($itemId, $qty){
        $WHOrder = Mage::getModel('inventoryplus/warehouse_order')
                                ->getCollection()
                                ->addFieldToFilter('warehouse_id', $this->getId())
                                ->addFieldToFilter('item_id', $itemId)
                                ->getFirstItem();
        if($WHOrder->getId()){
            $WHOrder->setQty($WHOrder->getQty() + $qty)
                    ->save();
        }
        return $this;
    }
    
    /**
     * Update stock of product in warehouse
     * 
     * @param int|string $productId
     * @param float $physicalQty
     * @param float $availableQty
     * @return \Magestore_Inventoryplus_Model_Warehouse
     */
    public function updateStock($productId, $physicalQty, $availableQty, $rewrite = false){
        $WHProduct = $this->getProducts(array($productId))->getFirstItem();
        if($WHProduct->getId()){
	    $newPhysicalQty = $rewrite ? $physicalQty : $WHProduct->getTotalQty() + $physicalQty;
	    $newAvailableQty = $rewrite ? $availableQty : $WHProduct->getAvailableQty() + $availableQty;
            $WHProduct->setTotalQty($newPhysicalQty)
                    ->setAvailableQty($newAvailableQty)
                    ->setUpdatedAt(now());
        } else {
            $WHProduct->setData(array(
                'warehouse_id' => $this->getId(),
                'product_id' => $productId,
                'total_qty' => $physicalQty,
                'available_qty' => $availableQty,
                'created_at' => now(),
                'updated_at' => now()
            ));
        }
        $WHProduct->save();
        return $this;
    }
    
    /**
     * Get warehouse id from order id
     * 
     * @param int|string $orderId
     * @return string
     */
    public function getIdFromOrderId($orderId){
        $WHOrder = Mage::getModel('inventoryplus/warehouse_order')
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->getFirstItem();
        $warehouseId = $WHOrder->getWarehouseId() ? $WHOrder->getWarehouseId() 
                        : Mage::helper('inventoryplus/warehouse')->getPrimaryWarehouse()->getId();
        return $warehouseId;
    }

}
