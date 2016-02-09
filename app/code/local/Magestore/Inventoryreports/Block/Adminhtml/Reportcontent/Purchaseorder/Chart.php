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
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Purchaseorder_Chart extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Chart {

    /**
     * Get chart title
     * 
     * @return string
     */
    public function getTitle() {
        return 'Total Value (' . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() . ')';
    }

    /**
     * Get total data of a field
     * 
     * @return int|float
     */
    public function getTotalData() {
        if(Mage::registry('total_data') === null) {
            $data = $this->getChartColumnData();
            Mage::register('total_data', $data['total']);
        }
        return Mage::registry('total_data');
    }

    /**
     * Get data collection
     * 
     * @return collection
     */
    public function getCollection() {
        $collection = Mage::helper('inventoryreports/purchaseorder')->getCollection($this->getRequestData());
        $collection->getSelect()->order('total_value DESC');
        return $collection;
    }

    /**
     * Get bar chart data
     * 
     * @return array
     */
    public function getChartColumnData() {
        if ($this->hasData('bar_chart_data')) {
            return $this->getData('bar_chart_data');
        }
        $collection = $this->getCollection();
        $report = $this->getReport();
        $series = array();
        $categories = '[';
        $series['data'] = '[';
        $i = 0;
        $totalValue = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['data'] .= ',';
            }
            $seriesPoint = round($col->getData('total_value'), 2);
            $categoryPoint = '';
            switch ($report) {
                case 'po_supplier':
                    $categoryPoint = '\'' . ucwords($col->getData('supplier_name')) . '\'';
                    break;
                case 'po_warehouse':
                    $categoryPoint = '\'' . ucwords($col->getData('warehouse_name')) . '\'';
                    break;
                case 'po_sku':
                    $categoryPoint = '\'' . ucwords($col->getData('sku')) . '\'';
                    if ($i >= self::CHART_POINT_LIMIT) {
                        $categoryPoint = '';
                        $seriesPoint = '';
                    }
                    break;
            }
            $categories .= $categoryPoint;
            $series['data'] .= $seriesPoint;
            $totalValue += $col->getData('total_value');
            $i++;
        }
        $totalValue = round($totalValue, 2);
        $categories .= ']';
        $series['data'] .= ']';
        $data['categories'] = $categories;
        $data['series']['total_value'] = $series;
        $data['name'] = $this->__('Total Value');
        $data['total'] = $totalValue;
        $this->setData('bar_chart_data', $data);

        return $this->getData('bar_chart_data');
    }

    /**
     * Get data of sales pie chart
     * 
     * @return array
     */
    public function getChartPieData() {
        if ($this->hasData('pie_chart_data')) {
            return $this->getData('pie_chart_data');
        }
        $collection = $this->getCollection();
        $report = $this->getReport();
        $series = '';
        $i = 0;
        foreach ($collection as $col) {
            $totalValue = round($col->getData('total_value'), 2);
            if ($i != 0)
                $series .= ',';
            switch ($report) {
                case 'po_supplier':
                    $alias = ucwords($col->getData('supplier_name'));
                    break;
                case 'po_warehouse':
                    $alias = ucwords($col->getData('warehouse_name'));
                    break;
                case 'po_sku':
                    $alias = ucwords($col->getData('sku'));
                    if ($i >= self::CHART_POINT_LIMIT) {
                        $alias = '';
                    }
                    break;
            }

            if ($alias) {
                $series .= '{name:\'' . $alias . ' (' . Mage::helper('core')->currency($totalValue) . ')\',y:' . $totalValue . '}';
            }
            $i++;
        }
        $series = Mage::helper('inventoryreports')->checkNullDataChart($series);
        $data['series'] = $series;
        $this->setData('pie_chart_data', $data);

        return $this->getData('pie_chart_data');
    }

}
