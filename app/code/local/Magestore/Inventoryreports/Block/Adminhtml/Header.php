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
 * @package     Magestore_Inventoryplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Header 
    extends Magestore_Inventoryreports_Block_Adminhtml_Template {

    public function getSubmitUrl() {
        return Mage::getUrl('adminhtml/inr_report/index/', array('type_id' => $this->getRequest()->getParam('type_id')));
    }

    /**
     * Get header text of report
     * 
     * @return string
     */
    public function getHeaderText() {
        return $this->helper('inventoryreports')->getHeaderText();
    }
    
    /**
     * Check if display/ hide report criterias form
     * 
     * @return bool
     */    
    public function isDisplayReportCriteria() {
        if($this->getRequestData('is_show_report_criteria')) {
            return true;
        }
        return false;
    }
    
    /**
     * Check if Report type is stock on hand
     * 
     * @return boolean
     */
    public function isStockOnHandReport() {
        if (strcmp(Mage::app()->getRequest()->getActionName(), 'stockonhand') == 0) {
            return true;
        }
        return false;
    }
}
