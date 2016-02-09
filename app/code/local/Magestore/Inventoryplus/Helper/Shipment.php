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
 * Inventory Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Helper_Shipment extends Mage_Core_Helper_Abstract {

    protected $_observerOb;
    protected $_postParams;
    protected $_shipmentOb;
    protected $_orderOb;

    public function sendObject($observer, $postParams, $shipment, $order) {
        $this->_observerOb = $observer;
        $this->_postParams = $postParams;
        $this->_shipmentOb = $shipment;
        $this->_orderOb = $order;
        return;
    }

    /*
     * 	Checking shippment id was exist in warehouse_shipment or not
     * 	@param	var shipmentId
     * 	@return	boolean
     */

    protected function getExistWarehouseShipment($shipmentId) {
        $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
        $warehouse_sm = $inventoryShipmentModel->getCollection()
                ->addFieldToFilter('shipment_id', $shipmentId)
                ->getFirstItem();
        if ($warehouse_sm->getId())
            return true;
        else
            return false;
    }

    /*
     * 	Check for break sales_order_shipment_save_after events
     * 	@param	var observer
     * 	@return	boolean
     */

    public function isIgnoreObserver() {
        $ignore = false;
        $action_name = Mage::app()->getRequest()->getActionName();
        if ($action_name == 'email')
            $ignore = true;
        $shipmentId = $this->_shipmentOb->getId();
        $isExistShipmentId = $this->getExistWarehouseShipment($shipmentId);
        if ($isExistShipmentId == true)
            $ignore = true;
        /* Dropship */
        $data = $this->_postParams;
        if (isset($data['echeck_dropship']) && $data['echeck_dropship'] == 1)
            $ignore = true;
        return $ignore;
    }

    /*
     * 	prepare data for creating shipment transaction 
     * 	@param	var observer
     * 	@return	array
     */

    public function _prepareTransactionData() {
        $transactionSendData = array();
        $order = $this->_orderOb;
        if ($order->getCustomerIsGuest() != 0) {
            $customerName = "Guest";
            $customerId = "0";
        } else {
            $customerFirstName = $order->getData('customer_firstname');
            $customerLastName = $order->getData('customer_lastname');
            $customerName = $customerFirstName . " " . $customerLastName;
            $customerId = $order->getData('customer_id');
        }
        $createdAt = date('Y-m-d', strtotime(now()));
        if (Mage::getModel('admin/session')->getUser()) {
            $admin = Mage::getModel('admin/session')->getUser()->getUsername();
            if ($admin)
                $createdBy = $admin;
        } else
            $createdBy = "API or Others";
        $reason = Mage::helper('inventoryplus')->__("Shipment for order #%s", $order->getIncrementId());
        $transactionSendData['type'] = '5';
        $transactionSendData['warehouse_id_to'] = $customerId;
        $transactionSendData['warehouse_name_to'] = $customerName;
        $transactionSendData['created_at'] = $createdAt;
        $transactionSendData['created_by'] = $createdBy;
        $transactionSendData['reason'] = $reason;
        return $transactionSendData;
    }

    /*
     * 	calculating qty shipped by exploding order item and shipment data
     * 	@param	var orderItem, var shipmentItemData
     * 	@return	qty_shipped
     */

    public function _prepareQtyShipped($orderItem, $shipmentItemData) {
        $product_id = $orderItem->getProductId();
        $orderItemId = $orderItem->getItemId();
        $qtyShipped = 0;
        if (isset($shipmentItemData[$orderItemId])) {
            if ($orderItem->getParentItemId() && isset($shipmentItemData[$orderItem->getParentItemId()])) {
                $qtyShipped = $shipmentItemData[$orderItem->getParentItemId()] > $shipmentItemData[$orderItemId] ? $shipmentItemData[$orderItem->getParentItemId()] : $shipmentItemData[$orderItemId];
            } else
                $qtyShipped = $shipmentItemData[$orderItemId];
        } else {
            if ($orderItem->getParentItemId() && isset($shipmentItemData[$orderItem->getParentItemId()])) {
                $qtyShipped = $shipmentItemData[$orderItem->getParentItemId()];
            } else {
                if ($orderItem->getParentItemId() && isset($shipmentItemData[$orderItem->getParentItemId()]))
                    $qtyShipped = $shipmentItemData[$orderItem->getParentItemId()];
                else
                    $qtyShipped = 0;
            }
        }
        return $qtyShipped;
    }

    /*
     * 	check product of order item should be ignored or not
     * 	@param	var orderItem
     * 	@return	bolean
     */

    public function isIgnoreProduct($orderItem) {
        $ignore = false;
        $product_id = $orderItem->getProductId();
        $product = $orderItem->getProduct();
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
        $manageStock = $stockItem->getManageStock();
        if ($stockItem->getUseConfigManageStock()) {
            $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
        }
        if (!$manageStock)
            $ignore = true;
        if ($product->isComposite())
            $ignore = true;
        return $ignore;
    }

    /*
     * 	get warehouse ID to ship this order item
     * 	@param	var orderItem,var data, var qty_shipped
     * 	@return	warehouse_id
     */

    public function _getWarehouseIdToShip($orderItem, $data, $qtyShipped) {
        $warehouseCol = Mage::getModel('inventoryplus/warehouse')->getCollection();
        $warehouse_id = $warehouseCol->getFirstItem()->getId();
        return $warehouse_id;
    }

    /*
     * 	get prepare and save warehouse_shipment model
     * 	@param	var warehouse_id,var orderItem, var product_id, var qtyShipped
     * 	@return	bolean
     */

    public function _saveModelWarehouseShipment($warehouse_id, $orderItem, $product_id, $qtyShipped) {
        $warehouseName = Mage::helper('inventoryplus/warehouse')->getWarehouseNameByWarehouseId($warehouse_id);
        $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
        $inventoryShipmentModel->setItemId($orderItem->getId())
                ->setProductId($product_id)
                ->setOrderId($this->_orderOb->getId())
                ->setWarehouseId($warehouse_id)
                ->setWarehouseName($warehouseName)
                ->setShipmentId($this->_shipmentOb->getId())
                ->setQtyShipped($qtyShipped)
                ->setSubtotalShipped($orderItem->getPrice() * $qtyShipped);
        try {
            $inventoryShipmentModel->save();
        } catch (Exception $e) {
            try {
                $resource = Mage::getModel('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $data = $inventoryShipmentModel->getData();
                if (!$data['warehouse_shipment_id']) {
                    $fieldsString = '(`' . implode('`, `', array_keys($data)) . '`)';
                    $valuesString = " VALUES ('" . implode("', '", $data) . "')";
                    $sql = "INSERT INTO {$resource->getTableName('erp_inventory_warehouse_shipment')} "
                            . $fieldsString
                            . $valuesString;
                    $writeConnection->query($sql);
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'inventory_management.log');
            }
        }
        return;
    }

    /*
     * 	prepare and save warehouse_product model
     * 	@param	var warehouse_id,var product_id, var qtyShipped
     * 	@return	object
     */

    public function _saveModelWarehouseProduct($warehouse_id, $product_id, $qtyShipped) {
        $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                ->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouse_id)
                ->addFieldToFilter('product_id', $product_id)
                ->getFirstItem();
        $newTotalQty = $warehouse_product->getTotalQty() - $qtyShipped;
        $warehouse_product->setTotalQty($newTotalQty);
        try {
            $warehouse_product->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
        return $warehouse_product;
    }

    /*
     * 	prepare and save warehouse_order model
     * 	@param	var warehouse_id,var product_id, var qtyShipped, model warehouseProduct
     * 	@return	object
     */

    public function _saveModelWarehouseOrder($warehouse_id, $product_id, $qtyShipped, $warehouseProduct) {
        $warehouseOr = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                ->addFieldToFilter('order_id', $this->_orderOb->getId())
                ->addFieldToFilter('product_id', $product_id)
                ->getFirstItem();
        $OnHoldQty = $warehouseOr->getQty() - $qtyShipped;
        if ($OnHoldQty >= 0) {
            $warehouseOr->setQty($OnHoldQty)
                    ->save();
        } else {
            $warehouseOr->setQty(0)
                    ->save();
        }
        return $warehouseOr;
    }

    /*
     * 	prepare and save warehouse_transaction_product model
     * 	@param	var transactionId,var product_id, var transactionProduct
     * 	@return	null
     */

    public function _saveModelTransactionProduct($transactionId, $productId, $transactionProduct) {
        try {
            Mage::getModel('inventoryplus/transaction_product')
                    ->setWarehouseTransactionId($transactionId)
                    ->setProductId($productId)
                    ->setProductSku($transactionProduct['product_sku'])
                    ->setProductName($transactionProduct['product_name'])
                    ->setQty(-$transactionProduct['qty_shipped'])
                    ->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
        return;
    }

    /*
     * 	prepare and save warehouse_transaction model
     * 	@param	var transactionSendData
     * 	@return	object
     */

    public function _saveModelTransaction($transactionSendData) {
        $transactionSendModel = Mage::getModel('inventoryplus/transaction');
        if(!$transactionSendModel){
            return;
        }
        $transactionSendModel->addData($transactionSendData);
        try {
            $transactionSendModel->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
        return $transactionSendModel;
    }

}
