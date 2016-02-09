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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbymovement_Chart extends Mage_Adminhtml_Block_Widget{
	 public function getChartColumnData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $series = array();
        $categories = '[';
        $series['inventory_movement']['name'] = $this->__('Total Transaction Qtys');
        $series['inventory_movement']['data'] = '[';
        $i = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['inventory_movement']['data'] .= ',';
            }
            $categories .= '\'' . Mage::helper('inventoryreports')->getMovementTypeText($col->getData('type')) . '\'';
            $series['inventory_movement']['data'] .= abs($col->getData('transactionproductqty'));
            $i++;
        }
        $categories .= ']';
        $series['inventory_movement']['data'] .= ']';
        $data['categories'] = $categories;
        $data['series'] = $series; 
        return $data;
    }

    public function getChartPieData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $i = 0;
        $series = '';
        foreach ($collection as $col) {
            if ($i != 0){
                $series .= ',';
            }
            $series .= '{name:\'' . Mage::helper('inventoryreports')->getMovementTypeText($col->getData('type')) .': '. abs($col->getData('transactionproductqty')).'\',y:' . abs($col->getData('transactionproductqty')) . '}';
            $i++;
        }
        $data['series'] = $series;
        return $data;
    }
}