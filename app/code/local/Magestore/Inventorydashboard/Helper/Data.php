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
 * @package     Magestore_Inventorydashboard
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydashboard Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydashboard
 * @author      Magestore Developer
 */
class Magestore_Inventorydashboard_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Limit items shown in chart
     */
    CONST CHART_POINT_LIMIT = 10;

    public function getGroupType() {
        $result = Mage::helper('inventorydashboard/constant')->getReportTypes();
        foreach ($result as $key => $value) {
            if (strcmp($key, "unknown") == 0) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    public function getReportType($type) {
        $chartTypes = Mage::helper('inventorydashboard/constant')->getReportTypes();
        $result = $chartTypes[$type]['report_code'];
        return $result;
    }
    
    public function getReportTypeByCode($reportCode){
        $reports = Mage::helper('inventorydashboard/constant')->getReportTypes();
        foreach($reports as $type => $reportData){
            if(isset($reportData['report_code'][$reportCode])){
                return $type;
            }
        }
        return null;
    }

    public function getDefaultChartType($defaultChartCode = null, $defaultReportCode = null) {
        $response = '';
        if (is_null($defaultReportCode)) {
            $defaultReportCode = 'hours_of_day';
        }
        if (is_null($defaultChartCode)) {
            $defaultChartCode = 'chart_column';
        }
        $chartReports = Mage::helper('inventorydashboard/constant')->getChartReport();
        $chartCodes = $chartReports[$defaultReportCode];
        $i = 1;
        foreach ($chartCodes as $chartCode) {
            if ($defaultChartCode == $chartCode) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            $chartTypes = Mage::helper('inventorydashboard/constant')->getChartTypes();
            $chartTitle = $chartTypes[$chartCode];
            $image = Mage::getBaseUrl('media') . 'inventorydashboard/charttype/' . $chartCode . '.png';
            $response .= '<li style="float:left;margin-right:15px">
                <input type="radio" id="default_chart_type" class="radio validate-one-required-by-name validation-passed" value="' . $chartCode . '" name="chart_type" title="' . $chartTitle . '" ' . $checked . '/>
                <label for="chart_pie"><img src ="' . $image . '" title="' . $chartTitle . '" /></label>
                </li>';
            $i++;
        }
        return $response;
    }

    public function getItemColumn($tab_id) {
        $itemCol1 = Mage::getModel('inventorydashboard/items')->getCollection()
                ->addFieldToFilter('tab_id', $tab_id)
                ->addFieldToFilter('item_column', 1);
        $itemCol2 = Mage::getModel('inventorydashboard/items')->getCollection()
                ->addFieldToFilter('tab_id', $tab_id)
                ->addFieldToFilter('item_column', 2);
        $item_column = 1;
        if (count($itemCol1) != 0 && count($itemCol2) == 0) {
            $item_column = 2;
        }
        if (count($itemCol2) > 0 && count($itemCol1) > count($itemCol2)) {
            $item_column = 2;
        }
        return $item_column;
    }

    public function getChartData($data) {
        $results = array();
        switch ($data['group_type']) {
            case 'sales':
                $name = $data['chart_name'];
                if ($data['sales_report'] == 'order_attribute') {
                    $reportType = $data['attribute_sales_report'];
                } else {
                    $reportType = $data['sales_report'];
                }
                $chartType = $data['chart_type'];
                break;
            case 'purchaseorder':
                $name = $data['chart_name'];
                $reportType = $data['purchaseorder_report'];
                $chartType = $data['chart_type'];
                break;
            case 'stockonhand':
                $name = $data['chart_name'];
                $reportType = $data['stockonhand_report'];
                $chartType = $data['chart_type'];
                break;
            case 'stockmovement':
                $name = $data['chart_name'];
                $reportType = $data['stockmovement_report'];
                $chartType = $data['chart_type'];
                break;
            case 'customer':
                $name = $data['chart_name'];
                $reportType = $data['customer_report'];
                $chartType = $data['chart_type'];
                break;
        }
        $results['name'] = $name;
        $results['reportType'] = $reportType;
        $results['chartType'] = $chartType;
        return $results;
    }

    public function getChartDataEdit($data) {
        $results = array();
        switch ($data['group_type']) {
            case 'sales':
                $name = $data['chart_name'];
                if ($data['sales_report_edit'] == 'order_attribute') {
                    $reportType = $data['attribute_sales_report_edit'];
                } else {
                    $reportType = $data['sales_report_edit'];
                }
                $chartType = $data['chart_type'];
                break;
            case 'purchaseorder':
                $name = $data['chart_name'];
                $reportType = $data['purchaseorder_report_edit'];
                $chartType = $data['chart_type'];
                break;
            case 'stockonhand':
                $name = $data['chart_name'];
                $reportType = $data['stockonhand_report_edit'];
                $chartType = $data['chart_type'];
                break;
            case 'stockmovement':
                $name = $data['chart_name'];
                $reportType = $data['stockmovement_report_edit'];
                $chartType = $data['chart_type'];
                break;
            case 'customer':
                $name = $data['chart_name'];
                $reportType = $data['customer_report_edit'];
                $chartType = $data['chart_type'];
                break;
        }
        $results['name'] = $name;
        $results['reportType'] = $reportType;
        $results['chartType'] = $chartType;
        return $results;
    }

    /**
     * Get chart column data for sales report
     * 
     * @param type $collection
     * @param type $requestData
     * @return string
     */
    public function getChartColumnData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $attribute = '';
        if ($reportcode == 'order_attribute') {
            $attribute = $requestData['attribute_select'];
        }
        $series = array();
        $categories = '[';
        $series['inventory_order']['name'] = $this->__('Sales Report By Order');
        $series['inventory_order']['data'] = '[';
        $i = 0;
        $total_order = 0;
        $total_invoice = 0;
        $total_refund = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['inventory_order']['data'] .= ',';
            }
            if ($attribute) {
                if (strlen($col->getData('att_' . $attribute)) > 20) {
                    $categories .= '\'' . substr($col->getData('att_' . $attribute), 0, 20) . '\'';
                } else {
                    $categories .= '\'' . $col->getData('att_' . $attribute) . '\'';
                }
            } else {
                switch ($reportcode) {
                    case 'sales_days':
                        $categories .= '\'' . $col->getTimeRange() . '\'';
                        if ($col->getData('time_range')) {
                            $date = Mage::helper('core')->formatDate($col->getData('time_range'), 'medium');
                            $date = date('Y-m-d', strtotime($date));
                            $dateArray = explode('-', $date);
                            $dateArray[1] = (int) $dateArray[1] - 1;
                            $dateData = implode(',', $dateArray);
                        }
                        $series['inventory_order']['data'] .= "[Date.UTC($dateData)," . round($col->getData('sum_base_grand_total'), 2) . ']';
                        break;
                    case 'days_of_month':
                        $categories .= '\'' . 'Day ' . $col->getTimeRange() . '\'';
                        break;
                    case 'days_of_week':
                        $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                        $categories .= '\'' . $daysofweek[$col->getTimeRange()] . '\'';
                        break;
                    case 'hours_of_day':
                        $categories .= '\'' . $col->getTimeRange() . ':00' . ' - ' . $col->getTimeRange() . ':59' . '\'';
                        break;
                    case 'sales_sku':
                        $categories .= '\'' . $col->getProductSku() . '\'';
                        break;
                    case 'sales_warehouse':
                        $categories .= '\'' . $col->getWarehouseName() . '\'';
                        break;
                    case 'sales_supplier':
                        $categories .= '\'' . $col->getSupplierName() . '\'';
                        break;
                    case 'payment_method':
                    case 'shipping_method':
                    case 'status':
                        $categories .= '\'' . ucwords($col->getData('att_' . $reportcode)) . '\'';
                        break;
                    default:
                        break;
                }
            }
            if ($reportcode == 'invoice') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_invoice += round($col->getData('sum_base_grand_total_invoiced'), 2);
            } else if ($reportcode == 'refund') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_refund += round($col->getData('sum_base_grand_total_refunded'), 2);
            } else if (strcmp($reportcode, 'sales_days') != 0) {
                $series['inventory_order']['data'] .= round($col->getData('sum_base_grand_total'), 2);
            }
            $i++;
        }
        if ($total_order) {
            $categories = '[' . '\'' . 'Total Ordered' . '\'';
            $series['inventory_order']['data'] = '[' . $total_order;
            if ($reportcode == 'invoice') {
                $categories .= ',' . '\'' . 'Total Invoiced' . '\'';
                $series['inventory_order']['data'] .= ',' . $total_invoice;
            } else {
                $categories .= ',' . '\'' . 'Total Refunded' . '\'';
                $series['inventory_order']['data'] .= ',' . $total_refund;
            }
        }
        $categories .= ']';
        $series['inventory_order']['data'] .= ']';
        $data['categories'] = $categories;
        $data['series'] = $series;
        return $data;
    }

    /**
     * Get chart pie data for sales report
     * 
     * @param type $collection
     * @param type $requestData
     * @return boolean
     */
    public function getChartPieData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $attribute = '';
        if ($reportcode == 'order_attribute') {
            $attribute = $requestData['attribute_select'];
        }
        $series = '';
        $i = 0;
        $j = 0;
        $total_order = 0;
        $total_invoice = 0;
        $total_refund = 0;
        foreach ($collection as $col) {
            $totalInventories = 0;
            if ($col->getData('sum_base_grand_total')) {
                $totalInventories = round($col->getData('sum_base_grand_total'), 2);
            }
            if ($i != 0) {
                $series .= ',';
            }
            if ($attribute) {
                if ($attribute == 'shipping_method' && !$col->getData('att_' . $attribute)) {
                    $alias = $this->__('No shipping');
                } else {
                    $alias = ucwords($col->getData('att_' . $attribute));
                }
            } else {
                switch ($reportcode) {
                    case 'sales_days':
                        break;
                    case 'days_of_month':
                        $alias = 'Day ' . $col->getTimeRange();
                        break;
                    case 'days_of_week':
                        $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                        $alias = $daysofweek[$col->getTimeRange()];
                        break;
                    case 'hours_of_day':
                        $alias = $col->getTimeRange() . ':00' . ' - ' . $col->getTimeRange() . ':59';
                        break;
                    case 'sales_sku':
                        $alias = $col->getProductSku();
                        break;
                    case 'sales_warehouse':
                        $alias = $col->getWarehouseName();
                        break;
                    case 'sales_supplier':
                        $alias = $col->getSupplierName();
                        break;
                    case 'payment_method':
                        $alias = str_replace('-', '<br/>', ucwords($col->getData('att_' . $reportcode))) . '<br/>';
                        break;
                    case 'shipping_method':
                        $alias = str_replace('-', '<br/>', ucwords($col->getData('att_' . $reportcode))) . '<br/>';
                        break;
                    case 'status':
                        $alias = ucwords($col->getData('att_' . $reportcode)) . '<br/>';
                        break;
                    default:
                        break;
                }
            }
            if ($reportcode == 'invoice') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_invoice += round($col->getData('sum_base_grand_total_invoiced'), 2);
            } else if ($reportcode == 'refund') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_refund += round($col->getData('sum_base_grand_total_refunded'), 2);
            } else {
                if ($totalInventories > 0) {
                    $j++;
                }
                $series .= '{name:\'' . $alias . '( ' . Mage::helper('core')->currency($totalInventories) . ' )\',y:' . $totalInventories . '}';
            }
            $i++;
        }
        if ($reportcode != 'invoice' && $reportcode != 'refund' && $j == 0) {
            return false;
        }
        if ($total_order && $total_order > 0) {
            $series = '';
            if ($reportcode == 'invoice') {
                $series .= '{name:\'' . 'Total Invoiced' . '(' . round(($total_invoice / $total_order) * 100, 2) . '%)\',y:' . $total_invoice . '}';
                if (($total_order - $total_invoice) > 0) {
                    $series .= ',';
                    $series .= '{name:\'' . 'Total Not Invoiced' . '( ' . (100 - round(($total_invoice / $total_order) * 100, 2)) . '%)\',y:' . ($total_order - $total_invoice) . '}';
                }
            } else {
                $series .= '{name:\'' . 'Total Refunded' . '( ' . round(($total_refund / $total_order) * 100, 2) . '%)\',y:' . $total_refund . '}';
                if (($total_order - $total_refund) > 0) {
                    $series .= ',';
                    $series .= '{name:\'' . 'Total Not Refunded' . '( ' . (100 - round(($total_refund / $total_order) * 100, 2)) . '%)\',y:' . ($total_order - $total_refund) . '}';
                }
            }
        }
        $series = Mage::helper('inventoryreports')->checkNullDataChart($series);
        $data['series'] = $series;
        return $data;
    }

    //getDateRangeByDay
    public function getDateRangeByDay($days) {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);
        $dateStart->subDay($days - 1);
        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');
        return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
    }

    /**
     * Get chart column data for purchase order report
     * 
     * @param type $collection
     * @param type $requestData
     * @return type
     */
    public function getPurchaseorderChartColumnData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
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
            switch ($reportcode) {
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
                default:
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

        return $data;
    }

    /**
     * Get chart pie data for purchase order report
     * 
     * @param type $collection
     * @param type $requestData
     * @return type
     */
    public function getPurchaseorderChartPieData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $series = '';
        $i = 0;
        foreach ($collection as $col) {
            $totalValue = round($col->getData('total_value'), 2);
            if ($i != 0) {
                $series .= ',';
            }
            switch ($reportcode) {
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
        
        return $data;
    }
    
    /**
     * Get chart column data for stock movement report
     * 
     * @param type $collection
     * @param type $requestData
     * @return string
     */
    public function getStockmovementChartColumnData($collection, $requestData) {
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
    
    /**
     * Get chart pie data for stock movement report
     * 
     * @param type $collection
     * @param type $requestData
     * @return string
     */
    public function getStockmovementChartPieData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $i = 0;
        $series = '';
        $alias = '';
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
    
    /**
     * Get chart column data for customer report
     * 
     * @param type $collection
     * @param type $requestData
     * @return string
     */
    public function getCustomerChartColumnData($collection, $requestData) {
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
    
    /**
     * Get chart pie data for customer report
     * 
     * @param type $collection
     * @param type $requestData
     * @return string
     */
    public function getCustomerChartPieData($collection, $requestData) {
        $collection->getSelect()->order('IFNULL(SUM(main_table.grand_total),0) DESC')->limit(20);
        $reportcode = $requestData['report_radio_select'];
        $i = 0;
        $series = '';
        foreach ($collection as $col) {
            if ($i != 0){
                $series .= ',';
            }
            $series .= '{name:\'' . $col->getData('customer_firstname')." ".$col->getData('customer_lastname')
                . ' ('.$col->getData('telephone') .')'
                .'\',y:' . abs($col->getData('sum_grand_total')) . '}';
            $i++;
        }
        $data['series'] = $series;
        return $data;
    }
    
    /**
     * Get data for stock on-hand report
     * 
     * @param type $collection
     * @param type $requestData
     * @return string
     */
    public function getStockRemainReportData($collection, $requestData) {
        $columnSeries = array();
        $product_name = array();
        $total_remain = array();
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
        
        return $data;
    }
    
    /**
     * Get chart column data for stock on-hand report
     * 
     * @param type $collection
     * @param type $requestData
     * @return type
     */
    public function getStockonhandChartColumnData($collection, $requestData) {
        $data = array();
        $results = $this->getStockRemainReportData($collection, $requestData);
        $categories = $results['column_categories'];
        $series = $results['column_series'];
        $data['categories'] = $categories;
        $data['series'] = $series;
        return $data;
    }
    
    /**
     * Get chart pie data for stock on-hand report
     * 
     * @param type $collection
     * @param type $requestData
     * @return type
     */
    public function getStockonhandChartPieData($collection, $requestData) {
        $data = array();
        $results = $this->getStockRemainReportData($collection, $requestData);
        $series = $results['pie_series'];
        $data['series'] = $series;
        return $data;
    }

}
