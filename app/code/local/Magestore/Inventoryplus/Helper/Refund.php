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
 * @package     Magestore_Inventoryplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryplus
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Helper_Refund extends Mage_Core_Helper_Abstract {

    /**
     * Process refund item
     * 
     * @param Varien_Object $dataObject
     * @param \Mage_Sales_Model_Item $orderItem
     * @return \Magestore_Inventoryplus_Helper_Refund
     */
    public function processRefund($dataObject, $orderItem) {
        /* check if this item has been processed */
        if ($this->isProcessedRefund($dataObject->getCreditmemoObject(), $orderItem->getId()))
            return $this;
        $data = $this->prepareCreditMemoItemData($dataObject, $orderItem);
        
        if($data['return_type'] != '2') {
            /* it is not refund process to Supplier. Then do update stock in warehouse*/
            /* update on-hold qty in warehouse */
            foreach($data['warehouse_order'] as $warehouseId => $WHOrder){
                $warehouse = $this->_getWarehouse($warehouseId);
                if ($adjustQty = $WHOrder['adjust_qty']) {
                    $warehouse->updateOrderItem($WHOrder['item_id'], $adjustQty);
                }
            }
            /* update physical qty & available qty in warehouse */
            foreach($data['warehouse_product'] as $warehouseId => $warehouseProduct){
                $warehouse = $this->_getWarehouse($warehouseId);
                $warehouse->updateStock($warehouseProduct['product_id'], $warehouseProduct['adjust_physical_qty'], $warehouseProduct['adjust_available_qty']);
                $stockMovementData = $dataObject->getStockMovementData();
                $stockMovementData[$warehouse->getId()][$warehouseProduct['product_id']] = $warehouseProduct['adjust_physical_qty'];
                $dataObject->setStockMovementData($stockMovementData);
            }
        }
        /* add refund to warehouse */
        Mage::getModel('inventoryplus/warehouse_refund')
                ->setData($data['warehouse_refund'])
                ->save();
        return $this;
    }

    /**
     * Log refund transaction to Warehouse Transaction
     * 
     * @param Varien_Object $dataObject
     * @return \Magestore_Inventoryplus_Helper_Refund
     */
    public function logTransaction($dataObject) {
        if (!Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse')) {
            return $this;
        }
        $creditmemo = $dataObject->getCreditmemoObject();
        $order = $dataObject->getOrder();
        $stockMovementData = $dataObject->getStockMovementData();
        if (!count($stockMovementData)) {
            return $this;
        }
        $adminUser = Mage::helper('inventoryplus')->getCurrentUser();
        foreach ($stockMovementData as $warehouseId => $moveProducts) {
            $totalProducts = 0;
            $transData = array();
            foreach ($moveProducts as $productId => $qty) {
                $totalProducts += abs($qty);
            }
            if ($totalProducts == 0)
                continue;
            $transData['total_products'] = $totalProducts;
            $transData['warehouse_id_from'] = $warehouseId;
            $transData['warehouse_name_from'] = $this->_getWarehouse($warehouseId)->getWarehouseName();
            $transData['warehouse_id_to'] = $warehouseId;
            $transData['warehouse_id_to'] = $order->getCustomerId();
            $transData['warehouse_name_to'] = $order->getCustomerName();
            $transData['created_at'] = now();
            $transData['created_by'] = $adminUser;
            $transData['reason'] = Mage::helper('inventoryplus')->__('Return stock when customer refunds - Creditmemo #' . $creditmemo->getIncrementId());
            $transData['type'] = Magestore_Inventorywarehouse_Model_Transaction::TYPE_REFUND_STOCK;
            /* create new transaction */
            $transaction = Mage::getModel('inventorywarehouse/transaction')
                    ->setData($transData)
                    ->save();
            $products = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect('name')
                    ->addFieldToFilter('entity_id', array('in' => array_keys($moveProducts)));
            /* log products in transaction */
            foreach ($products as $product) {
                if ($moveProducts[$product->getId()] == 0)
                    continue;
                Mage::getModel('inventorywarehouse/transaction_product')
                        ->setData('product_id', $product->getId())
                        ->setData('product_name', $product->getName())
                        ->setData('product_sku', $product->getSku())
                        ->setData('qty', $moveProducts[$product->getId()])
                        ->setData('warehouse_transaction_id', $transaction->getId())
                        ->save();
            }
        }
    }

    /**
     * Prepare item data to process refund
     * 
     * @param Varien_Object $dataObject
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return array
     */
    public function prepareCreditMemoItemData($dataObject, $orderItem) {
        $data = array();
        $itemId = $orderItem->getId();
        if (!$dataObject->getData("creditmemo/items/$itemId/qty")) {
            $itemId = ($orderItem->getParentItemId()) ? $orderItem->getParentItemId() : $orderItem->getId();
        }
        $data['back_to_stock'] = $dataObject->getData("creditmemo/items/$itemId/back_to_stock");
        $data['refund_qty'] = $dataObject->getData("creditmemo/items/$itemId/qty");
        $data['return_type'] = $dataObject->getData("creditmemo/select-warehouse-supplier/$itemId");
        $data['warehouse_id'] = $dataObject->getData("creditmemo/warehouse-select/$itemId");
        $data['warehouse_id'] = $data['warehouse_id'] ? $data['warehouse_id'] : $dataObject->getData('warehouse_id');
        if (!$data['warehouse_id']) {
            /* load warehouse_id from order_id */
            $data['warehouse_id'] = Mage::getModel('inventoryplus/warehouse')->getIdFromOrderId($orderItem->getOrderId());
            $dataObject->setData('warehouse_id', $data['warehouse_id']);
        }
        $data['warehouse'] = $this->_getWarehouse($data['warehouse_id']);
        $data['order_item'] = $orderItem;
        $data['order'] = $dataObject->getOrder();
        $data['creditmemo'] = $dataObject->getCreditmemoObject();
        $refundQtys = $this->_calculateRefundedQty($dataObject, $orderItem);
        $data['refund_qty'] = $refundQtys;
        /* get original warehouse of order */
        $orgWarehouse = $this->_getOrgWarehouse($orderItem->getOrderId());
        $data['org_warehouse'] = $orgWarehouse;
        /* prepare data */
        $this->_prepareWarehouseRefundData($data);
        $this->_prepareWarehouseOrderData($data);
        $this->_prepareWarehouseProductData($data);
        return $data;
    }

    /**
     * Calculate refund qty
     * 
     * @param Varien_Object $dataObject
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return array [not_ship, shipped, total, had_refunded]
     */
    protected function _calculateRefundedQty($dataObject, $orderItem) {
        $creditMemo = $dataObject->getCreditmemoObject();
        $order = $dataObject->getOrder();
        $parentItemId = $orderItem->getParentItemId();
        $refundItem = $creditMemo->getItemByOrderId($orderItem->getId());
        $parentItem = $parentItemId ? $creditMemo->getItemByOrderId($parentItemId) : false;
        if(is_bool($refundItem)){$refundQty = $parentItem ? $parentItem->getQty() : 0;}
        else{ $refundQty = $parentItem ? ($parentItem->getQty() * $refundItem->getQty()) : $refundItem->getQty();}
        $oldCreditmemos = $order->getCreditmemosCollection();
        /* total refuned qty before created this creditmemo */
        $hadRefundedQty = 0;
        if (count($oldCreditmemos)) {
            foreach ($oldCreditmemos as $oldCreditmemo) {
                if ($oldCreditmemo->getId() == $creditMemo->getId()) {
                    continue;
                }
                $item = $oldCreditmemo->getItemByOrderId($orderItem->getId());
                if ($item) {
                    $parentItem = $orderItem->getParentItemId() ? $oldCreditmemo->getItemByOrderId($orderItem->getParentItemId()) : false;
                    $hadRefundedQty += $parentItem ? ($parentItem->getQty() * $item->getQty()) : $item->getQty();
                }
            }
        }
        /* calculate shipped qty */
        $shippedQty = $orderItem->getQtyShipped();
        if ($orderItem->getParentItemId()) {
            $parentOrderItem = $order->getItemById($orderItem->getParentItemId());
            if ($parentOrderItem && $parentOrderItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $shippedQty = $parentOrderItem->getQtyShipped();
            }
        }
        /* calculate canceled qty */
        $canceledQty = $orderItem->getQtyCanceled();
        if ($orderItem->getParentItemId()) {
            $parentOrderItem = $order->getItemById($orderItem->getParentItemId());
            if ($parentOrderItem && $parentOrderItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $canceledQty = $parentOrderItem->getQtyCanceled();
            }
        }          
        /* total qty which we need to ship before created this credit memo */
        $needToShipQty = ($orderItem->getQtyOrdered() - $shippedQty - $hadRefundedQty - $canceledQty);
        $needToShipQty = ($needToShipQty >= 0) ? $needToShipQty : 0;
        /* separate refund qty to qty of not_ship items & qty of shipped items */
        $refundQtys = array(
            'not_ship' => min($refundQty, $needToShipQty),
            'shipped' => $refundQty - min($refundQty, $needToShipQty),
            'total' => $refundQty,
            'had_refunded' => $hadRefundedQty
        );
        return $refundQtys;
    }

    /**
     * Prepare data to update to inventory_warehouse_refund table
     * 
     * @param array $data
     * @return array
     */
    protected function _prepareWarehouseRefundData(&$data) {
        $WHRefund = array('warehouse_id' => $data['warehouse_id'],
            'warehouse_name' => $data['warehouse']->getWarehouseName(),
            'creditmemo_id' => $data['creditmemo']->getId(),
            'order_id' => $data['order']->getId(),
            'item_id' => $data['order_item']->getId(),
            'product_id' => $data['order_item']->getProductId(),
            'qty_refunded' => $data['refund_qty']['total'],
        );
        $data['warehouse_refund'] = $WHRefund;
        return $data;
    }

    /**
     * Prepare data to update to inventory_warehouse_order table
     * 
     * @param array $data
     * @return array
     */
    protected function _prepareWarehouseOrderData(&$data) {
        $WHOrder = array(
            'item_id' => $data['order_item']->getId(),
        );
        /* calculate on-hold qty in current warehouse */
        $adjustOnholdQty = -$data['refund_qty']['not_ship'];
        $WHOrder['adjust_qty'] = $adjustOnholdQty;
        /* calculate on-hold qty in original warehouse */
        if($data['org_warehouse']->getId() != $data['warehouse']->getId()){
            $orgWHOrder = array(
                'item_id' => $data['order_item']->getId(),
                'adjust_qty' => -$data['refund_qty']['not_ship'],
            );
            $data['warehouse_order'][$data['org_warehouse']->getId()] = $orgWHOrder;
            /* update on-hold qty in return warehouse */
            $WHOrder['adjust_qty'] = 0;
        }
        $data['warehouse_order'][$data['warehouse']->getId()] = $WHOrder;
        return $data;
    }

    /**
     * Prepare data to update to inventory_warehouse_product table
     * 
     * @param array $data
     * @return array
     */
    protected function _prepareWarehouseProductData(&$data) {
        $WHProduct = array(
            'product_id' => $data['order_item']->getProductId(),
        );
        if ($data['back_to_stock']) {
            /* add refund shipped items to physical qty in warehouse */
            $adjustPhysicalQty = $data['refund_qty']['shipped'];
            /* add refund qty to available qty in warehouse */
            $adjustAvailableQty = $data['refund_qty']['total'];
            if($data['org_warehouse']->getId() != $data['warehouse']->getId()){
               $orgWHProduct = array(
                   'product_id' => $data['order_item']->getProductId(),
                   'adjust_available_qty' => $data['refund_qty']['not_ship'],
                   'adjust_physical_qty' => 0,
               );
               $data['warehouse_product'][$data['org_warehouse']->getId()] = $orgWHProduct;
               $adjustAvailableQty = $data['refund_qty']['shipped'];
           }
        } else {
            /* remove (refund not_ship items) from physical qty in warehouse */
            $adjustPhysicalQty = -$data['refund_qty']['not_ship'];
            /* do not add refund qty to available qty in warehouse */
            $adjustAvailableQty = 0;
        }
        $WHProduct['adjust_available_qty'] = $adjustAvailableQty;
        $WHProduct['adjust_physical_qty'] = $adjustPhysicalQty;
        $data['warehouse_product'][$data['warehouse']->getId()] = $WHProduct;
        return $data;
    }

    /**
     * Check is processed refund
     * 
     * @param \Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param string $itemId
     * @return boolean
     */
    public function isProcessedRefund($creditmemo, $itemId) {
        $whRefund = Mage::getModel('inventoryplus/warehouse_refund')
                ->loadByCreditmemoId($creditmemo->getId())
                ->addFieldToFilter('item_id', $itemId)
                ->getFirstItem();
        if ($whRefund->getId())
            return true;
        return false;
    }

    /**
     * Get Warehouse
     * 
     * @param type $id
     * @return \Magestore_Inventoryplus_Model_Warehouse
     */
    protected function _getWarehouse($id) {
        if (!Mage::registry('warehouse_' . $id)) {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($id);
            Mage::register('warehouse_' . $id, $warehouse);
        }
        return Mage::registry('warehouse_' . $id);
    }
    
    /**
     * Get original warehouse from order_id
     * 
     * @param int|string $orderId
     * @return \Magestore_Inventoryplus_Model_Warehouse
     */
    protected function _getOrgWarehouse($orderId){
        if (!Mage::registry('org_warehouse_' . $orderId)) {
            $orgWarehouseId = Mage::getModel('inventoryplus/warehouse_order')
                                    ->getOriginalWarehouseId($orderId);
            $warehouse = $this->_getWarehouse($orgWarehouseId);
            Mage::register('org_warehouse_' . $orderId, $warehouse);
        }
        return Mage::registry('org_warehouse_' . $orderId);
    }
    
    /**
     * Prepare post creditmemo data
     * 
     * @param Varien_Object $dataObject
     */
    public function prepareCreditMemoData($dataObject){
        Mage::dispatchEvent('inp_prepare_post_creditmemo_data', array('creditmemo_data' => $dataObject));
    }

}
