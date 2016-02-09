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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyproduct_Chart extends Mage_Adminhtml_Block_Widget {
    
    protected function _prepareLayout() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if(empty($requestData)){
            $this->setTemplate('inventoryreports/content/chart/chart-content/product/bestseller.phtml');
        }
        if(!empty($requestData)){
            switch ($requestData['report_radio_select']) {
                case 'best_seller':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/product/bestseller.phtml');
                    break;
                case 'most_stock_remain':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/product/moststockremain.phtml');
                    break;
                case 'warehousing_time_longest':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/product/warehousingtimelongest.phtml');
                    break;
            }
        }
                return parent::_prepareLayout();
    }
    
}