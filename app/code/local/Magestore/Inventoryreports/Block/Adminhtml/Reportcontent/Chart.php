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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Chart 
    extends Magestore_Inventoryreports_Block_Adminhtml_Template {
    
    /**
     * Limit items shown in chart
     */
    CONST CHART_POINT_LIMIT = 10;
    
    /**
     * Get data collection
     * 
     * @return collection
     */
    public function getCollection() {
        $dataCollection = $this->getDataCollection();
        if (is_array($dataCollection)) {
            $collection = $dataCollection['collection'];
        } else {
            $collection = $dataCollection;
        }
        //set limit to SKU report
        if($this->isSalesSKUReport()){
            //$collection->getSelect()->limit(10);
        }
        return $collection;
    } 
    
    /**
     * Get header text of report page
     * 
     * @return string
     */
    public function getHeaderText(){
        return Mage::helper('inventoryreports')->getHeaderText();
    }

}
