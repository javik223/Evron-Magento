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
 * Supplier Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Adjuststock_Process extends Magestore_Inventoryplus_Block_Adminhtml_Process_Popup
{
    public function getLoadDataTypeUrl(){
        return $this->getUrl('*/*/processDataList', array('_secure'=>true, 'id'=>$this->getAdjuststockId()));
    }
    
    public function getTotalUrl(){
        return $this->getUrl('*/*/countData', array('_secure'=>true, 'id'=>$this->getAdjuststockId()));
    }
    
    public function getRunProcessUrl(){
        return $this->getUrl('*/*/doProcess', array('_secure'=>true, 'id'=>$this->getAdjuststockId()));
    }   
    
    public function getAdjuststockId(){
        return $this->getRequest()->getParam('id');
    }
    
    public function getRedirectUrl(){
        return $this->getUrl('*/*/edit', array('_secure'=>true, 'id'=>$this->getAdjuststockId()));
    }
    
    public function getHeaderText(){
        return $this->__('Processing Stock Adjustment');
    }
    
    public function getRedirectMessage(){
        return $this->__('Redirecting to review stock adjustment page...');
    }
    
    public function getErrorMessage() {
        return $this->__('There was error while processing! Try again.');
    }
    
    public function getFinishMessage() {
        return $this->__('Finished all processes.');
    }
}