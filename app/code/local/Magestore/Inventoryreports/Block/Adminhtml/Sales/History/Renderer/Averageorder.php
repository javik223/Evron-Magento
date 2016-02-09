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
class Magestore_Inventoryreports_Block_Adminhtml_Sales_History_Renderer_Averageorder extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) 
    {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        foreach ($requestData as $key => $value)
            if (!empty($value))
                $filterData->setData($key, $value);
        $dateFrom = $filterData->getData('date_from',null);
        $dateTo = $filterData->getData('date_to',null);  
        $period = $filterData->getData('period_type', null);
        $totalOrder = $row->getData('total_order');
        $days = (strtotime($dateTo) - strtotime($dateFrom))/(60*60*24);
        if($period == 1){
            $result = round(($totalOrder/$days),2);
        }elseif($period == 2){
            $result = round(($totalOrder/$days)*30,2);
        }elseif($period == 3){
            $result = round(($totalOrder/$days)*365,2);
        }        
        return $result;        
    }
}