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

class Magestore_Inventorywarehouse_Adminhtml_Inw_StorepickupController extends Magestore_Inventoryplus_Controller_Action {
    
    public function changewarehouseAction() {
        $storeId = $this->getRequest()->getParam('store_id');
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
        $responseData = array('warehouse' => $warehouse->getData());
        $this->getResponse()->setBody(json_encode($responseData));
    }
}
