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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */

class Magestore_Inventorywarehouse_Adminhtml_Inw_ShipmentController extends Mage_Adminhtml_Controller_action {

    public function checkavailablebyeventAction() {
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $productId = $this->getRequest()->getParam('product_id');
        $qty = $this->getRequest()->getParam('qty');
        $orderItemId = $this->getRequest()->getParam('order_item_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $totalQtyOfProductRequest = $this->getRequest()->getParam('total_qty');
        $availableProduct = Mage::helper('inventorywarehouse/warehouse')
                ->checkWarehouseAvailableProduct($warehouseId, $productId, $totalQtyOfProductRequest);
        if ($availableProduct == true || $qty == 0) {
            $this->getResponse()->setBody("available");
        } else {            
            $this->getResponse()->setBody("notavailable");            
        }
    }

}
