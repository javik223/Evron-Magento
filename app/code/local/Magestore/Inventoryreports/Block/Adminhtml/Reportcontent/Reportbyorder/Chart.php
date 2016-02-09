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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Chart extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Chart {

    /**
     * Get chart title
     * 
     * @return string
     */
    public function getTitle() {
        return 'Grand Total (' . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() . ')';
    }

    /**
     * Get report data
     * 
     * @return array
     */
    public function getDataCollection() {
        if (!$this->hasData('data_collection')) {
            $requestData = $this->getRequestData();
            $collection = Mage::helper('inventoryreports/order')->getOrderReportCollection($requestData);
            $this->setData('data_collection', $collection);
        }
        return $this->getData('data_collection');
    }

    /**
     * Get total data of a field
     * 
     * @return int|float
     */
    public function getTotalData() {
        if (Mage::registry('total_data') === null) {
            $data = $this->getChartColumnData();
            $totalValue = $data['series']['inventory_order']['total'];
            if($this->isSalesSupplierReport()){
                $item = $this->helper('inventoryreports/order')->getUnassignedSupplierSales($this->getRequestData());
                $totalValue += $item->getData('sum_base_grand_total');
            }            
            Mage::register('total_data', $totalValue);
        }
        return Mage::registry('total_data');
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
        $reportcode = $this->getReport();
        $series = array();
        $categories = '[';
        $series['inventory_order']['name'] = $this->__('Grand Total');
        $series['inventory_order']['data'] = '[';
        $i = 0;
        $qtyOrdered = 0;
        $total_value = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['inventory_order']['data'] .= ',';
            }
            $seriesPoint = round($col->getData('sum_base_grand_total'), 2);
            switch ($reportcode) {
                case 'shipping_method':
                    if (!$col->getData('att_' . $reportcode))
                        $categoryPoint = '\'' . $this->__('No shipping') . '\'';
                    else
                        $categoryPoint = '\'' . ucwords($col->getData('att_' . $reportcode)) . '\'';
                    break;
                case 'payment_method':
                case 'status':
                    $categoryPoint = '\'' . ucwords($col->getData('att_' . $reportcode)) . '\'';
                    break;
                case 'days_of_week':
                    $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                    $categoryPoint = '\'' . $daysofweek[$col->getTimeRange()] . '\'';
                     $seriesPoint = round($col->getData('base_grand_total_per_day'), 2);
                    break;
                case 'days_of_month':
                    $categoryPoint = '\'' . 'Day ' . $col->getTimeRange() . '\'';
                    $seriesPoint = round($col->getData('base_grand_total_per_day'), 2);
                    break;
                case 'hours_of_day':
                    $categoryPoint = '\'' . $col->getTimeRange() . 'h\'';
                    $seriesPoint = round($col->getData('base_grand_total_per_hour'), 2);
                    break;
                case 'sales_days':
                    $categoryPoint = '\'' . $col->getTimeRange() . '\'';
                    if ($col->getData('time_range')) {
                        $date = Mage::helper('core')->formatDate($col->getData('time_range'), 'medium');
                        $date = date('Y-m-d', strtotime($date));
                        $dateArray = explode('-', $date);
                        $dateArray[1] = (int) $dateArray[1] - 1;
                        $dateData = implode(',', $dateArray);
                    }
                    $seriesPoint = "[Date.UTC($dateData)," . round($col->getData('sum_base_grand_total'), 2) . ']';
                    break;
                case 'sales_warehouse':
                    $categoryPoint = '\'' . ($col->getData('warehouse_name')) . '\'';
                    break;
                case 'sales_supplier':
                    $categoryPoint = '\'' . ($col->getData('supplier_name')) . '\'';
                    break;
                case 'sales_sku':
                    $categoryPoint = '\'' . ($col->getData('product_sku')) . '\'';
                    if ($i >= self::CHART_POINT_LIMIT) {
                        $categoryPoint = '';
                        $seriesPoint = '';
                    }
                    break;
            }
            //join point date to chart date
            $categories .= $categoryPoint;
            $series['inventory_order']['data'] .= $seriesPoint;

            $i++;
            $total_value += $col->getData('sum_base_grand_total');
            $qtyOrdered += $col->getData('sum_qty_ordered');
        }
        $total_value = round($total_value, 2);
        $categories .= ']';
        $series['inventory_order']['data'] .= ']';
        $data['categories'] = $categories;
        $data['series'] = $series;
        $data['series']['inventory_order']['total'] = $total_value;
        $data['series']['inventory_order']['qty'] = $qtyOrdered;
        $this->setData('bar_chart_data', $data);

        return $this->getData('bar_chart_data');
    }

    /**
     * Get sales volumn bar chart data
     * 
     * @return array
     */
    public function getChartColumnVolumeData() {
        if ($this->hasData('bar_chart_volume_data')) {
            return $this->getData('bar_chart_volume_data');
        }
        $collection = $this->getCollection();
        $reportcode = $this->getReport();
        $series = array();
        $categories = '[';
        $series['inventory_order']['name'] = $this->__('Base Grandtotal');
        $series['inventory_order']['data'] = '[';
        $i = 0;
        $total_value = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['inventory_order']['data'] .= ',';
            }
            $seriesPoint = $col->getData('count_entity_id');
            switch ($reportcode) {
                case 'shipping_method':
                    if (!$col->getData('att_' . $reportcode))
                        $categoryPoint = '\'' . $this->__('No shipping') . '\'';
                    else
                        $categoryPoint = '\'' . ucwords($col->getData('att_' . $reportcode)) . '\'';
                    break;
                case 'payment_method':
                case 'status':
                    $categoryPoint = '\'' . ucwords($col->getData('att_' . $reportcode)) . '\'';
                    break;
                case 'days_of_week':
                    $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                    $categoryPoint = '\'' . $daysofweek[$col->getTimeRange()] . '\'';
                     $seriesPoint = $col->getData('total_order_per_day');
                    break;
                case 'days_of_month':
                    $categoryPoint = '\'' . 'Day ' . $col->getTimeRange() . '\'';
                    $seriesPoint = $col->getData('total_order_per_day');
                    break;
                case 'hours_of_day':
                    $categoryPoint = '\'' . $col->getTimeRange() . 'h\'';
                    $seriesPoint = $col->getData('total_order_per_hour');
                    break;
                case 'sales_days':
                    $categoryPoint = '\'' . $col->getTimeRange() . '\'';
                    if ($col->getData('time_range')) {
                        $date = Mage::helper('core')->formatDate($col->getData('time_range'), 'medium');
                        $date = date('Y-m-d', strtotime($date));
                        $dateArray = explode('-', $date);
                        $dateArray[1] = (int) $dateArray[1] - 1;
                        $dateData = implode(',', $dateArray);
                    }
                    $seriesPoint = "[Date.UTC($dateData)," . $col->getData('count_entity_id') . ']';
                    break;
                case 'sales_warehouse':
                    $categoryPoint = '\'' . ($col->getData('warehouse_name')) . '\'';
                    break;
                case 'sales_supplier':
                    $categoryPoint = '\'' . ($col->getData('supplier_name')) . '\'';
                    break;
                case 'sales_sku':
                    $categoryPoint = '\'' . ($col->getData('product_sku')) . '\'';
                    if ($i >= self::CHART_POINT_LIMIT) {
                        $categoryPoint = '';
                        $seriesPoint = '';
                    }
                    break;
            }
            //join point date to chart date
            $categories .= $categoryPoint;
            $series['inventory_order']['data'] .= $seriesPoint;

            $i++;
            $total_value += $col->getData('count_entity_id');
        }
        $categories .= ']';
        $series['inventory_order']['data'] .= ']';
        $series['inventory_order']['total'] = $total_value;
        $data['categories'] = $categories;
        $data['series'] = $series;
        $this->setData('bar_chart_volume_data', $data);

        return $this->getData('bar_chart_volume_data');
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
        $reportcode = $this->getReport();
        $series = '';
        $i = 0;
        foreach ($collection as $col) {
            $totalInventories = 0;
            if ($col->getData('sum_base_grand_total'))
                $totalInventories = round($col->getData('sum_base_grand_total'), 2);
            if ($i != 0)
                $series .= ',';
            $alias = '';
            switch ($reportcode) {
                case 'shipping_method':
                    if ($col->getData('att_' . $reportcode))
                        $alias = str_replace('-', '<br/>', ucwords($col->getData('att_' . $reportcode))) . '<br/>';
                    else
                        $alias = $this->__('No shipping');
                    break;
                case 'payment_method':
                case 'status':
                    $alias = ucwords($col->getData('att_' . $reportcode)) . '<br/>';
                    break;
                case 'days_of_week':
                    $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                    $alias = $daysofweek[$col->getTimeRange()];
                    $totalInventories = round($col->getData('base_grand_total_per_day'), 2);
                    break;
                case 'hours_of_day':
                    $alias = $col->getTimeRange() . 'h';
                    $totalInventories = round($col->getData('base_grand_total_per_hour'), 2);
                    break;
                case 'days_of_month':
                    $alias = 'Day ' . $col->getTimeRange();
                    $totalInventories = round($col->getData('base_grand_total_per_day'), 2);
                    break;
                case 'sales_warehouse':
                    $alias = $col->getData('warehouse_name');
                    break;
                case 'sales_supplier':
                    $alias = $col->getData('supplier_name');
                    break;
                case 'sales_sku':
                    $alias = $col->getData('product_sku');
                    if ($i >= self::CHART_POINT_LIMIT) {
                        $alias = '';
                    }
            }
            if ($alias) {
                $series .= '{name:\'' . $alias . ' (' . Mage::helper('core')->currency($totalInventories) . ')\',y:' . $totalInventories . '}';
            }
            $i++;
        }
        $series = Mage::helper('inventoryreports')->checkNullDataChart($series);
        $data['series'] = $series;
        $this->setData('pie_chart_data', $data);

        return $this->getData('pie_chart_data');
    }

    /**
     * Get data of sales value pie chart
     * 
     * @return array
     */
    public function getChartPieVolumeData() {
        if ($this->hasData('pie_chart_volume_data')) {
            return $this->getData('pie_chart_volume_data');
        }
        $collection = $this->getCollection();
        $reportcode = $this->getReport();
        $series = '';
        $i = 0;
        foreach ($collection as $col) {
            $totalInventories = 0;
            if ($col->getData('count_entity_id'))
                $totalInventories = $col->getData('count_entity_id');
            if ($i != 0)
                $series .= ',';
            $alias = '';
            switch ($reportcode) {
                case 'shipping_method':
                    if ($col->getData('att_' . $reportcode))
                        $alias = str_replace('-', '<br/>', ucwords($col->getData('att_' . $reportcode)));
                    else
                        $alias = $this->__('No shipping');
                    break;
                case 'payment_method':
                case 'status':
                    $alias = ucwords($col->getData('att_' . $reportcode)) . '<br/>';
                    break;
                case 'days_of_week':
                    $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                    $alias = $daysofweek[$col->getTimeRange()];
                    $totalInventories = $col->getData('total_order_per_day');
                    break;
                case 'hours_of_day':
                    $alias = $col->getTimeRange() . 'h';
                    $totalInventories = $col->getData('total_order_per_hour');
                    break;
                case 'days_of_month':
                    $alias = 'Day ' . $col->getTimeRange();
                    $totalInventories = $col->getData('total_order_per_day');
                    break;
                case 'sales_warehouse':
                    $alias = $col->getData('warehouse_name');
                    break;
                case 'sales_supplier':
                    $alias = $col->getData('supplier_name');
                    break;
                case 'sales_sku':
                    $alias = $col->getData('product_sku');
                    if ($i >= self::CHART_POINT_LIMIT) {
                        $alias = '';
                    }
                    break;
            }
            if ($alias) {
                $series .= '{name:\'' . $alias . ' (' . $totalInventories . ')\',y:' . $totalInventories . '}';
            }
            $i++;
        }
        $series = Mage::helper('inventoryreports')->checkNullDataChart($series);
        $data['series'] = $series;
        $this->setData('pie_chart_volume_data', $data);

        return $this->getData('pie_chart_volume_data');
    }

}
