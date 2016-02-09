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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Model_Observer {

    /**
     * process supplier_form_after event
     *
     * @return Magestore_Inventorydropship_Model_Observer
     */
    public function supplier_form_after($observer) {
        $form = $observer->getEvent()->getForm();
        if (Mage::getStoreConfig('inventoryplus/dropship/enable')) {
            $fieldset = $form->addFieldset('supplierpass_form', array(
                'legend' => Mage::helper('inventoryplus')->__('Password Management')
            ));

            $fieldset->addField('new_password', 'text', array(
                'label' => Mage::helper('inventoryplus')->__('New Password'),
                'required' => false,
                'name' => 'new_password',
            ));
            
            $fieldset->addField('auto_general_password', 'checkbox', array(
                'label' => Mage::helper('inventoryplus')->__('Auto-generated password'),
                'required' => false,
                'name' => 'auto_general_password',
            ));

            $fieldset->addField('send_mail', 'checkbox', array(
                'label' => Mage::helper('inventoryplus')->__('Send new password to supplier'),
                'required' => false,
                'name' => 'send_mail',
            ));
        }
    }

    public function addHtml($observer) {

        if (!Mage::getStoreConfig('inventoryplus/dropship/enable')) {
            return;
        }
        
        $block = $observer->getEvent()->getBlock();

        if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Items' && ($block->getRequest()->getControllerName() == 'sales_order_shipment' || $block->getRequest()->getControllerName() == 'adminhtml_shipment') && $block->getRequest()->getActionName() == 'new') {

            $orderId = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

            $dropshipBlock = Mage::app()->getLayout()
                    ->createBlock('adminhtml/template')
                    ->setTemplate('inventorydropship/sales/shipment/items.phtml')
                    ->assign('_items', $shipment->getAllItems());
            echo $dropshipBlock->toHtml();
        }
/*
        if ((get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items' && $block->getRequest()->getControllerName() == 'sales_order_creditmemo' && $block->getRequest()->getActionName() == 'new')) {

            $dropshipBlock = Mage::app()->getLayout()
                    ->createBlock('adminhtml/template')
                    ->setTemplate('inventorydropship/sales/refund/items.phtml')
                    ->assign('_items', $block->getCreditmemo()->getAllItems());
            echo $dropshipBlock->toHtml();
        }
 * 
 */
    }

    protected function _getItemQtys() {
        $data = Mage::app()->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }
    
    /**
     * Retrieve random password
     *
     * @param   int $length
     * @return  string
     */
    public function generatePassword($length = 8)
    {
        $chars = Mage_Core_Helper_Data::CHARS_PASSWORD_LOWERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_UPPERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_DIGITS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_SPECIALS;
        return Mage::helper('core')->getRandomString($length, $chars);
    }

    public function saveSupplierPassword($observer) {
        if (!Mage::getStoreConfig('inventoryplus/dropship/enable')) {
            return;
        }
        $data = $observer->getEvent()->getDatas();
        $model = $observer->getEvent()->getModel();
        
        if(isset($data['auto_general_password'])){
            $data['new_password'] = $this->generatePassword();
        }
        
        if ($data['new_password']) {
            $newPassword = $data['new_password'];
            $newPasswordHash = md5($newPassword);
            $model->setPasswordHash($newPasswordHash);
        }
        if(array_key_exists('send_mail',$data))
            Mage::helper('inventorydropship')->sendPasswordResetConfirmationEmail($model,$newPassword); 
        
    }

    public function creditmemoSaveAfter($observer) {
 
        if (!Mage::getStoreConfig('inventoryplus/dropship/enable')) {
            return;
        }
        
        if(Mage::registry('INVENTORYDROPSHIP_CORE_ORDER_REFUND'))
            return;
        Mage::register('INVENTORYDROPSHIP_CORE_ORDER_REFUND',true);

        $data = Mage::app()->getRequest()->getParams();
        $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();
        $inventoryCreditmemoData = array();

        $order_id = $order->getId();
        $creditmemo_id = $creditmemo->getId();
        $warehouseId = 0;
        
        if (!isset($data['supplier-shipment']['items']['select-warehouse-supplier'])) {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
            $warehouseId = $warehouse->getId();
        }

        foreach ($creditmemo->getAllItems() as $creditmemo_item) {
           
            if(isset($data['creditmemo']['select-warehouse-supplier'][$creditmemo_item->getOrderItemId()]) && $data['creditmemo']['select-warehouse-supplier'][$creditmemo_item->getOrderItemId()]==1){
                continue;
            }
            
            
            
            $item = Mage::getModel('sales/order_item')->load($creditmemo_item->getOrderItemId());
            if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped')))
                continue;
            //row_total_incl_tax  

            if ($item->getParentItemId()) {
                if (isset($data['creditmemo']['items'][$item->getParentItemId()])) {
                  
                    if(isset($data['creditmemo']['select-warehouse-supplier'][$item->getParentItemId()]) && $data['creditmemo']['select-warehouse-supplier'][$item->getParentItemId()]==1){
                        continue;
                    }
                    
                    $item_parrent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                    $options = $item->getProductOptions();
                    
                    if (isset($options['bundle_selection_attributes'])) {
                        $option = unserialize($options['bundle_selection_attributes']);

                        $parentQty = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];

                        $qtyRefund = (int) $option['qty'] * (int) $parentQty;


                        $qtyShipped = $item->getQtyShipped();
                        $qtyRefunded = $item->getQtyRefunded();
                        $qtyOrdered = $item->getQtyOrdered();

                        $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();

                        //////////
                        //if return to stock
                        /*
                         * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                         * available qty will be returned = qtyRefund
                         */
                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                        if (isset($data['creditmemo']['items'][$item->getParentItemId()]['back_to_stock'])) {
                            if (isset($data['supplier-select'][$item->getParentItemId()])) {
                                $inventoryCreditmemoData[$item->getItemId()]['supplier_id'] = $data['supplier-shipment']['items'][$item->getParentItemId()];
                                $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
                                $inventoryCreditmemoData[$item->getItemId()]['qty_supplier'] = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                            }                            

                            $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                            $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            if ($qtyChecked > 0)
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;
                        }

                        //////////
                    } else {

                        $qtyRefund = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                        $qtyShipped = $item->getQtyShipped();
                        $qtyRefunded = $item->getQtyRefunded();
                        $qtyOrdered = $item->getQtyOrdered();

                        //////////
                        //if return to stock
                        /*
                         * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                         * available qty will be returned = qtyRefund
                         */
                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;

                        if (isset($data['creditmemo']['items'][$item->getParentItemId()]['back_to_stock'])) {

                            $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                            $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;

                            if (isset($data['supplier-select'][$item->getParentItemId()])) {
                                $inventoryCreditmemoData[$item->getItemId()]['supplier_id'] = $data['supplier-select'][$item->getParentItemId()];
                                $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
                                $inventoryCreditmemoData[$item->getItemId()]['qty_supplier'] = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                            }
                            
                            if ($qtyChecked > 0)
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;
                        }


                        $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                    }
                } else {

                    $qtyRefund = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                    $qtyShipped = $item->getQtyShipped();
                    $qtyRefunded = $item->getQtyRefunded();
                    $qtyOrdered = $item->getQtyOrdered();

                    //////////
                    //if return to stock
                    /*
                     * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                     * available qty will be returned = qtyRefund
                     */

                    $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                    $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                    if (isset($data['creditmemo']['items'][$item->getItemId()]['back_to_stock'])) {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;

                        $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                        if ($qtyChecked > 0)
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;

                        if (isset($data['supplier-select'][$item->getItemId()])) {
                            $inventoryCreditmemoData[$item->getItemId()]['supplier_id'] = $data['supplier-select'][$item->getItemId()];
                            $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
                            $inventoryCreditmemoData[$item->getItemId()]['qty_supplier'] = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                        }
                       
                    } 
                    $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                }
            } else {
                $qtyRefund = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                $qtyShipped = $item->getQtyShipped();
                $qtyRefunded = $item->getQtyRefunded();
                $qtyOrdered = $item->getQtyOrdered();

                //////////
                //if return to stock
                /*
                 * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                 * available qty will be returned = qtyRefund
                 */
                $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                if (isset($data['creditmemo']['items'][$item->getItemId()]['back_to_stock'])) {
                    $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                    $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                    if ($qtyChecked > 0)
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;

                    if (isset($data['supplier-select'][$item->getItemId()])) {
                        $inventoryCreditmemoData[$item->getItemId()]['supplier_id'] = $data['supplier-select'][$item->getItemId()];
                        $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
                        $inventoryCreditmemoData[$item->getItemId()]['qty_supplier'] = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                    }
                   
                } 

                $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
            }
        }
        $supplierReturn = array();
        foreach ($inventoryCreditmemoData as $id => $value) {
            if (isset($value['supplier_id'])) {
                $supplierReturn[$value['supplier_id']][$value['item_id']] = $value['qty_supplier'];
            } 
        }

        //end create transaction
        if (count($supplierReturn)) {
            foreach ($supplierReturn as $supplierReId => $itemData) {
                Mage::helper('inventorydropship')->sendEmailRefundToSupplier($supplierReId, $itemData);
            }
        }
    }

    public function addSupplierTabs($observer) {
        if (!Mage::getStoreConfig('inventoryplus/dropship/enable'))
            return;
        $tabs = $observer->getEvent()->getTabs();
        $tabs->addTab('dropship_section', array(
            'label' => Mage::helper('inventoryplus')->__('Drop Shipments'),
            'title' => Mage::helper('inventoryplus')->__('Drop Shipments'),
            'url' => Mage::getUrl('adminhtml/indr_inventorydropship/supplierdropship', array(
                '_current' => true,
                'id' => Mage::app()->getRequest()->getParam('id'),
                'store' => Mage::app()->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));
    }
    
    public function orderCancelAfter($observer) {
        if (!Mage::getStoreConfig('inventoryplus/dropship/enable'))
            return;
        if(Mage::registry('INVENTORYDROPSHIP_CORE_ORDER_CANCEL'))
            return;
        Mage::register('INVENTORYDROPSHIP_CORE_ORDER_CANCEL',true);
        try{
            $order = $observer->getOrder();
            $orderId =  $order->getId();
            $dropships = Mage::getModel('inventorydropship/inventorydropship')->getCollection()
                                        ->addFieldToFilter('order_id',$orderId);
            foreach($dropships as $dropship){
                if(in_array($dropship->getStatus(), array(1,2,3,4))){
                    $dropship->setData('status',5)->save();   
                    Mage::helper('inventorydropship')->sendEmailCancelDropShipToSupplier($dropship->getId());
                }
            }
            
            
        }catch(Exception $e){
            Mage::log($e->getMessage(),null,'inventory_management.log');
        }
    }
	public function salesOrderShipmentSaveAfter($observer){
		$helperClass = Mage::helper('inventorydropship/dropshipment');
        if ($helperClass->isIgnoreObserver() == true) return;
		$data = Mage::app()->getRequest()->getParams();
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder();
		$shipmentId = $shipment->getId();
		if (Mage::registry('INVENTORY_DROPSHIP_SHIPMENT_' . $shipmentId))return;
            Mage::register('INVENTORY_DROPSHIP_SHIPMENT_' . $shipmentId, true);
		$helperClass->sendObject($observer,$data,$shipment,$order);
		$shipmentItemData = array();		// Get data from sales/order_shipment_item_collection to array.
		$shipmentItems = Mage::getResourceModel('sales/order_shipment_item_collection')->addFieldToFilter('parent_id', $shipmentId);
		foreach ($shipmentItems as $shipmentItem) {
			$shipmentItemData[$shipmentItem->getOrderItemId()] = $shipmentItem->getQty();
		}
		$message = 'This item(s) was shipped by Drop Shipping!';
		foreach ($order->getAllItems() as $item) {
			$qtyShipped	= $helperClass->_prepareQtyShipped($item,$shipmentItemData);
			if ($qtyShipped != 0) {
				if ($helperClass->isIgnoreProduct($item) == true) continue;
				$product_id = $item->getProductId();
				$helperClass->_updateOnHoldAndAvailableQty($item,$data,$qtyShipped);				
				$helperClass->_saveModelWarehouseShipment($message,$item,$product_id,$qtyShipped);
				$helperClass->_updateCatalogQty($product_id);
			}
		}
	}
}
