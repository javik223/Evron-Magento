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

class Magestore_Inventorywarehouse_Block_Adminhtml_Storepickup_Js extends Mage_Adminhtml_Block_Template {

    protected function _prepareLayout() {
        $this->setTemplate('inventorywarehouse/storepickup/js.phtml');
        return parent::_prepareLayout();
    }
    
    public function getStoreId() {
        return $this->getRequest()->getParam('id');
    }
    
    public function getChangeWarehouseUrl() {
        return $this->getUrl('adminhtml/inw_storepickup/changewarehouse', array('_secure' => true));
    }
    
    public function getChangeWarehouseMessage() {
        return $this->__('If you change the linked warehouse, the store information will be changed too. Do you want to change?');
    }
}