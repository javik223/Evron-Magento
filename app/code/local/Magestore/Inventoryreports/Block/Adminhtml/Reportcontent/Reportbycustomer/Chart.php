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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbycustomer_Chart extends Mage_Adminhtml_Block_Widget{
    
    public function getChartColumnData($collection, $requestData) {
        $collection->getSelect()->order('IFNULL(SUM(main_table.grand_total),0) DESC')->limit(20);
        $reportcode = $requestData['report_radio_select'];
        $series = array();
        $categories = '[';
        $series['inventory_customer']['name'] = $this->__('Customers Report');
        $series['inventory_customer']['data'] = '[';
        $i = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['inventory_customer']['data'] .= ',';
            }
            $categories .= '\'' . $col->getData('customer_firstname')." ".$col->getData('customer_lastname')
                        . '<br/>('.$col->getData('telephone') .')'.'\'';
            $series['inventory_customer']['data'] .= abs($col->getData('sum_grand_total'));
            $i++;
        }
        $categories .= ']';
        $series['inventory_customer']['data'] .= ']';
        $data['categories'] = $categories;
        $data['series'] = $series; 
        return $data;
    }

    public function getChartPieData($collection, $requestData) {
        $collection->getSelect()->order('IFNULL(SUM(main_table.grand_total),0) DESC')->limit(20);
        $reportcode = $requestData['report_radio_select'];
        $i = 0;
        $series = '';
        foreach ($collection as $col) {
            if ($i != 0){
                $series .= ',';
            }
            $series .= '{name:\'' . $col->getData('customer_firstname')." ".$col->getData('customer_lastname') 
                    . '<br/>('.$col->getData('telephone') .')'
                    .'\',y:' . abs($col->getData('sum_grand_total')) . '}';
            $i++;
        }
        $data['series'] = $series;
        return $data;
    }
}