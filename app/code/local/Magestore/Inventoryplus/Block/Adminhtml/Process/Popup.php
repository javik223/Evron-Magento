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
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Process_Popup extends Mage_Adminhtml_Block_Template{
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryplus/process/run.phtml');
        return $this;
    }
    
    public function getExceptions(){
        return array();
    }
    
    public function getShowFinished(){
        return false;
    }
    
    public function getLoadDataTypeUrl(){
        return $this->getUrl('*/*/processDataList');
    }
    
    public function getTotalUrl(){
        return $this->getUrl('*/*/countData');
    }
    
    public function getRunProcessUrl(){
        return $this->getUrl('*/*/doProcess');
    }    
    
    public function getHeaderText(){
        return $this->__('Processing');
    }
    
    public function getRedirectMessage(){
        return $this->__('Redirecting to review page...');
    }
    
    public function getErrorMessage() {
        return $this->__('There was error while processing! Try again.');
    }
    
    public function getFinishMessage() {
        return $this->__('Finished all processes.');
    }
}    
