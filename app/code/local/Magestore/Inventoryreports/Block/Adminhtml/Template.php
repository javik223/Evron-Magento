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
 * @package     Magestore_Inventorysupplyneeds
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
class Magestore_Inventoryreports_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template {

    /**
     * Get report type
     * 
     * @return string
     */
    public function getTypeId() {
        return $this->getRequest()->getParam('type_id');
    }
    
    /**
     * Get data of submited report filter
     * 
     * @return array
     */
    public function getRequestData($field = null) {
        if(!$this->hasData('request_data')){
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
            if (strcmp($this->getTypeId(), 'stockonhand') == 0) {
                $requestData['select_time'] = 'last_30_days';
                $requestData['report_radio_select'] = 'most_stock_remain';
            }
            $this->setData('request_data', $requestData);
        }
        $data = $this->getData('request_data');
        return $field ? (isset($data[$field]) ? $data[$field] : null) : $data;
    }
    
    /**
     * Get report code
     * 
     * @return string
     */
    public function getReport() {
        $requestData = $this->getRequestData();
        if(isset($requestData['report_radio_select'])){
            return $requestData['report_radio_select'];
        }
        return null;
    }    
    
    /**
     * Check if show report selector
     * 
     * @return bool
     */
    public function isShowReportSelector() {
        $filterData = new Varien_Object();
        $requestData = $this->getRequestData();
        $report_radio_select = $requestData['report_radio_select'];
        return $this->helper('inventoryreports')->checkDisplay($report_radio_select);
    }
    
    
    /**
     * Check if it's showing sales warehouse report
     * 
     * @return bool
     */
    public function isSalesWarehouseReport(){
        if($this->getReport() == 'sales_warehouse'){
            return true;
        }
        return false;
    }

    /**
     * Check if it's showing sales supplier report
     * 
     * @return bool
     */
    public function isSalesSupplierReport(){
        if($this->getReport() == 'sales_supplier'){
            return true;
        }
        return false;
    }      
    
    /**
     * Check if it's showing sales sku report
     * 
     * @return bool
     */
    public function isSalesSKUReport(){
        if($this->getReport() == 'sales_sku'){
            return true;
        }
        return false;
    } 
}
