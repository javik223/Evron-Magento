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
class Magestore_Inventorywarehouse_Adminhtml_Inw_SalesrateController extends Magestore_Inventoryplus_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Sales Rate'));
        $this->loadLayout()->_setActiveMenu('inventoryplus/warehouse');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Sales Rate'));
        $this->loadLayout()->_setActiveMenu('inventoryplus');
        $this->renderLayout();
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/warehouse');
    } 
}