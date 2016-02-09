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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Adminhtml_Inb_ShipmentController extends Mage_Adminhtml_Controller_Action {

    public function checkavailablebyeventAction() {
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $productId = $this->getRequest()->getParam('product_id');
        $qty = $this->getRequest()->getParam('qty');
        $orderItemId = $this->getRequest()->getParam('order_item_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $totalQtyOfProductRequest = $this->getRequest()->getParam('total_qty');
        $availableProduct = Mage::helper('inventorybarcode/warehouse')
                ->checkWarehouseAvailableProduct($warehouseId, $productId, $totalQtyOfProductRequest);
        $result = array();
        
        $result['barcode'] = Mage::helper('inventorybarcode')->selectboxBarcodeByPid($productId, $orderItemId, $orderId, $warehouseId);
    
        if ($availableProduct == true || $qty == 0) {
            $result['avaiable'] = true;            
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {   
            $result['avaiable'] = false;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    public function checkbarcodeAction(){
        $barcodeId = $this->getRequest()->getParam('barcode_id');
        $barcode = Mage::getModel('inventorybarcode/barcode')->load($barcodeId);
        
        $result = array();
        if ($barcode->getQty()>0) {
            $result['avaiable'] = true;                        
        } else {   
            $result['avaiable'] = false;            
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    

}
