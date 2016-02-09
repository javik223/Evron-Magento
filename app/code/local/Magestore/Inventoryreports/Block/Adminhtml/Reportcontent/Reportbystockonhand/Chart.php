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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbystockonhand_Chart 
    extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Chart {
    
    protected function _prepareLayout() {
        $this->setTemplate('inventoryreports/content/chart/chart-content/stockonhand/chart.phtml');
        return parent::_prepareLayout();
    }
    
    public function getChartColumnData() {
        $data = array();
        $results = $this->getProductReportData();
        $categories = $results['column_categories'];
        $series = $results['column_series'];
        $data['categories'] = $categories;
        $data['series'] = $series;
        return $data;
    }

    public function getChartPieData() {
        $data = array();
        $results = $this->getProductReportData();
        $series = $results['pie_series'];
        $data['series'] = $series;
        return $data;
    }
    
    /**
     * Get total data of a field
     * 
     * @return int|float
     */
    public function getTotalData(){
        $requestData = $this->getRequestData();
        if(Mage::registry('total_data') === null) {
            $totalProduct = 0;
            $collection = Mage::helper('inventoryreports/stockonhand')->getStockRemainingCollection($requestData);
            foreach ($collection as $item) {
                $data = $item->getData();
                $totalProduct += (int) $data['total_remain'];
            }
            Mage::register('total_data', $totalProduct);
        }
        return Mage::registry('total_data');
    }
    
    public function getProductReportData() {
        return $this->getStockRemainReportData();
    }
    
    public function getStockRemainReportData() {
        if($this->hasData('stock_remaining_data')){
            return $this->getData('stock_remaining_data');
        }
        $columnSeries = array();
        $product_name = array();
        $total_remain = array();
        $requestData = $this->getRequestData();

        $collection = Mage::helper('inventoryreports/stockonhand')->getStockRemainingCollection($requestData);
        $collection->getSelect()->order('total_remain DESC');
        $collection->getSelect()->limit(10);

        $results = array();
        if (count($collection)) {
            foreach ($collection as $item) {
                $results[] = $item->getData();
            }
        }

        $data = array();
        if (count($results) > 0) {
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                $pieSeries .= '[\'' . $result['sku'] . ' (' . (int) $result['total_remain'] . ' items)\',' . (int) $result['total_remain'] . ']';
                $i++;
            }
            $data['pie_series'] = $pieSeries;
            //column data
            foreach ($results as $result) {
                $product_name[] = $result['sku'];
                $total_remain[] = ((int) $result['total_remain']);
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($product_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_product']['data'] = '[';
            foreach ($total_remain as $number_value) {
                if ($j != 0) {
                    $columnSeries['inventory_product']['data'] .= ',';
                }
                $columnSeries['inventory_product']['data'] .= $number_value;
                $j++;
            }
            $columnSeries['inventory_product']['data'] .= ']';
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        } else {
            $data['pie_series'] = '';
            $data['column_series'] = '';
            $data['column_categories'] = '';
        }
        
        $this->setData('stock_remaining_data', $data);
        
        return $data;
    }
}