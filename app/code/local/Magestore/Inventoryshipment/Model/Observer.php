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
 * @package     Magestore_Inventoryshipment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryshipment Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryshipment
 * @author      Magestore Developer
 */
class Magestore_Inventoryshipment_Model_Observer {

    //save shipping progress
    public function saveOrderSaveAfter($observer) {
        /* Edit by Magnus - 08/04/2015 - Fix cancel by mass action ko change shipping proccess */
        $order = $observer->getOrder();
        $order_id = $order->getId();
        if (Mage::registry('INVENTORY_SHIPMENT_PLUGIN_ORDER_SAVE_' . $order_id)){
            return;
        }
        Mage::register('INVENTORY_SHIPMENT_PLUGIN_ORDER_SAVE_' . $order_id, true);
        /* Endl Magnus 08/04/2015 */
        $status = $order->getStatus();
        $shipment = $order->hasShipments();
        if ($status == 'complete') {
            $shipping_progress = 2; //shipped
        } elseif ($status == 'canceled') {
            $shipping_progress = 3; //canceled
        } elseif ($status == 'closed') {
            $shipping_progress = 4; //closed
        } else {
            $shipping_progress = 2;
            foreach ($order->getAllItems() as $item) {
                if ($item->getQtyToShip() > 0 && !$item->getIsVirtual() && !$item->getLockedDoShip()) {
                    if ($shipment) {
                        $shipping_progress = 1; //partial
                        break;
                    } else {
                        $shipping_progress = 0; //not ship
                        break;
                    }
                }
            }
        }
        try {
            $order->setData('shipping_progress', $shipping_progress)
                    ->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    /**
     * Update buttons in Sales Order
     * 
     * @param type $observer
     */
    public function changeButton($observer) {
        if(!Mage::helper('inventoryplus')->isInInventorySection()){
            return;
        }
        $block = $observer->getEvent()->getBlock();
        if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Shipment_View') {
            $shipmentId = $block->getRequest()->getParam('shipment_id');           
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            $orderId = $shipment->getOrderId();
            $block->removeButton('save');
            $block->removeButton('delete');
            $block->updateButton('back', 'onclick', 'setLocation(\'' . $block->getUrl('adminhtml/sales_order/view', array('order_id' => $orderId,'active_tab'=>'order_shipments','inventoryplus'=>'1')) . '\')');          
        }
        if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Shipment_Create' && $block->getRequest()->getParam('inventoryplus') == '1') {
            $orderId = $block->getRequest()->getParam('order_id');
            $block->updateButton('back', 'onclick', 'setLocation(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/ins_inventoryshipment/index') . '\')');            
        }
        if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_View') {
            $orderId = $block->getRequest()->getParam('order_id');
            $block->removeButton('print');
            $block->removeButton('save');
            $block->removeButton('delete');
            $block->removeButton('order_hold');
            $block->removeButton('order_unhold');
            $block->removeButton('void_payment');
            $block->removeButton('accept_payment');
            $block->removeButton('deny_payment');
            $block->removeButton('get_review_payment_update');
            $block->removeButton('send_notification');
            $block->removeButton('void_payment');
            $block->removeButton('order_reorder');
            $block->removeButton('order_edit');
            $block->removeButton('order_cancel');
            $block->removeButton('order_creditmemo');
            $block->removeButton('order_invoice');
            $block->updateButton('order_ship', 'onclick', 'setLocation(\'' . $block->getUrl('adminhtml/sales_order_shipment/new', array('order_id' => $orderId, 'inventoryplus'=>'1')) . '\')');
            $block->updateButton('back', 'onclick', 'setLocation(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/ins_inventoryshipment/index') . '\')');            
        }
    }
    
    /*
     * Add param inventoryplus=1 to redirect url in Inventory Section
     * 
     */
    public function controller_response_redirect($observer){
        if(Mage::app()->getRequest()->getPost('inventoryplus_section') != '1'){
            return;
        }
        $transport = $observer->getEvent()->getTransport();
        $redirectUrl = $transport->getUrl();
        $redirectUrl .= 'inventoryplus/1';
        $transport->setUrl($redirectUrl);
    }
}
