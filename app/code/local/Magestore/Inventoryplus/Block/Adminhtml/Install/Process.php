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
class Magestore_Inventoryplus_Block_Adminhtml_Install_Process extends Magestore_Inventoryplus_Block_Adminhtml_Process_Popup
{
    public function getLoadDataTypeUrl(){
        return $this->getUrl('*/*/processDataList', array('_secure'=>true));
    }
    
    public function getTotalUrl(){
        return $this->getUrl('*/*/countData', array('_secure'=>true));
    }
    
    public function getRunProcessUrl(){
        return $this->getUrl('*/*/doProcess', array('_secure'=>true));
    }   
    
    public function getRedirectUrl(){
        return $this->getUrl('*/*/', array('_secure'=>true));
    }
    
    public function getHeaderText(){
        return $this->__('Import Magento Data');
    }

    public function getRedirectMessage(){
        return $this->__('Redirecting to the dashboard...');
    }

    public function getErrorMessage() {
        return $this->__('An error has occurred while processing! Please try again.');
    }

    public function getFinishMessage() {
        return $this->__('Finished all processes.');
    }
}