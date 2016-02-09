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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Chart extends Mage_Adminhtml_Block_Widget {
    
    protected $_title = '';

    public function setTitle($title){
        $this->_title = $title;
    }

    public function getTitle(){
        return $this->_title;
    }

    protected function _prepareLayout() {
        $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/chart.phtml');
        return parent::_prepareLayout();
    }
    
    public function getChartColumnData(){
        $data = array();
        $results = $this->getWarehouseReportData();
        $categories = $results['column_categories'];
        $series = $results['column_series'];
        $data['categories'] = $categories;
        $data['series'] = $series;
        return $data;
    }

    public function getChartPieData(){
        $data = array();
        $results = $this->getWarehouseReportData();
        $series = $results['pie_series'];
        $data['series'] = $series;
        return $data;
    }

    public function getWarehouseReportData(){
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        switch ($requestData['report_radio_select']) {
            default:
                $this->setTitle('Quantity');
                return $this->getTotalQtyAdjuststockReportData();
                break;
            case 'total_qty_adjuststock':
                $this->setTitle('Total Qty Adjusted');
                return $this->getTotalQtyAdjuststockReportData();
                break;
            case 'number_of_product_adjuststock':
                $this->setTitle('Number of Item(s)');
                return $this->getNumberProductAdjuststockReportData();
                break;
            case 'total_order_by_warehouse':
                $this->setTitle('Number of Order(s)');
                return $this->getTotalOrderData();
                break;
            case 'sales_by_warehouse_revenue':
                $this->setTitle('Grand Total ('.Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().')');
                return $this->getWarehouseRevenueData();
                break;
            case 'sales_by_warehouse_item_shipped':
                $this->setTitle('Number of Item(s)');
                return $this->getWarehouseItemShippedData();
                break;
            case 'total_stock_transfer_send_stock':
                $this->setTitle('Total Qty Sended');
                $this->getTransferSendStockData();
                break;
            case 'total_stock_transfer_request_stock':
                $this->setTitle('Total Qty Requested');
                $this->getTransferRequestStockData();
                break;
            case 'supply_needs_by_warehouse_products':
                $this->setTitle('Total Qty Needs');
                $this->getSupplyNeedsWarehouseProducts();
                break;
            case 'total_stock_different_when_physical_stocktaking_by_warehouse':
                $this->setTitle('Total Qty Needs');
                $this->getStockDifferentWhenPhysicalTaking();
                break;
        }
    }

    public function getSupplyNeedsWarehouseProducts(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $warehouse = $requestData['warehouse_select'];
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $datefrom = $gettime['date_from'];
        $dateto = $gettime['date_to'];
        if ($warehouse == 0) {
            $total_supplyneeds = array();
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->getCollection();
            foreach ($warehouse_collection as $value) {
                $warehouse_id = $value->getWarehouseId();
                $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                        ->getCollection()
                        ->addFieldToFilter('warehouse_id', $warehouse_id);
                $supplyneeds = array();
                foreach ($warehouse_product as $value) {
                    $product_id = $value->getProductId();
                    $method = Mage::getStoreConfig('inventory/supplyneed/supplyneeds_method');
                    if ($datefrom && $dateto && $method == 2 && (strtotime($datefrom) <= strtotime($dateto))) {
                        $max_needs = Mage::helper('inventorysupplyneeds')->calMaxAverage($product_id, $datefrom, $dateto, $warehouse_id);
                    } elseif ($datefrom && $dateto && $method == 1 && strtotime($datefrom) <= strtotime($dateto)) {
                        $max_needs = Mage::helper('inventorysupplyneeds')->calMaxExponential($product_id, $datefrom, $dateto, $warehouse_id);
                    } else {
                        $max_needs = 0;
                    }
                    if ($max_needs > 0) {
                        $supplyneeds[$product_id] = $max_needs;
                    }
                }
                $total_supplyneeds[$warehouse_id] = $supplyneeds;
                unset($supplyneeds);
            }
            $warehouse_name = array();
            $total_request = array();
            $chart_data = array();
            $count = 0;
            foreach ($total_supplyneeds as $key => $values) {
        //            if(!empty($values)){
                $total_value = 0;
                foreach ($values as $value) {
                    $total_value += $value;
                }
                if ($total_value > 0) {
                    $warehouse_name[] = Mage::getModel('inventoryplus/warehouse')->load($key)->getWarehouseName();
                    $total_request[] = $total_value;
                    $chart_data[$count]['warehouse_name'] = Mage::getModel('inventoryplus/warehouse')->load($key)->getWarehouseName();
                    $chart_data[$count]['total_request'] = $total_value;
                }
                $count++;
            }
        } else {
            $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse);
            $supplyneeds = array();
            foreach ($warehouse_product as $value) {
                $product_id = $value->getProductId();
                $method = Mage::getStoreConfig('inventory/supplyneed/supplyneeds_method');
                if ($datefrom && $dateto && $method == 2 && (strtotime($datefrom) <= strtotime($dateto))) {
                    $max_needs = Mage::helper('inventorysupplyneeds')->calMaxAverage($product_id, $datefrom, $dateto, $warehouse);
                } elseif ($datefrom && $dateto && $method == 1 && strtotime($datefrom) <= strtotime($dateto)) {
                    $max_needs = Mage::helper('inventorysupplyneeds')->calMaxExponential($product_id, $datefrom, $dateto, $warehouse);
                } else {
                    $max_needs = 0;
                }
                if ($max_needs > 0) {
                    $supplyneeds[$product_id] = $max_needs;
                }
            }
            $warehouse_name = array();
            $total_request = array();
            $count = 0;
            foreach ($supplyneeds as $key => $data) {
                if ($data > 0) {
                    $warehouse_name[] = Mage::getModel('catalog/product')->load($key)->getName();
                    $total_request[] = $data;
                    $chart_data[$count]['product_name'] = Mage::getModel('catalog/product')->load($key)->getName();
                    $chart_data[$count]['total_request'] = $data;
                    $count++;
                }
            }
        }
        if(count($total_request) > 0){
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($total_request as $number_value) {
                if ($j != 0) {
                    $columnSeries['inventory_warehouse']['data'] .= ',';
                }
                $columnSeries['inventory_warehouse']['data'] .= $number_value;
                $j++;
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($chart_data as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                if ($warehouse > 0) {
                    $pieSeries .= '[\'' . $result['product_name'] . '(' . $result['total_request'] . ' items supplyneeds)\',' . $result['total_request'] . ']';
                } else {
                    $pieSeries .= '[\'' . $result['warehouse_name'] . '(' . $result['total_request'] . ' items supplyneeds)\',' . $result['total_request'] . ']';
                }
                $i++;
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data;

    }

    public function getStockDifferentWhenPhysicalTaking(){
        $columnSeries = array();
        $total_data = array();
        $product_name = array();
        $productIds = array();
        $difference = array();
        $phyids = '';
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $warehouse_name = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select'])->getWarehouseName();
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');

        if ($requestData['warehouse_select']) {
            $warehouse_id = $requestData['warehouse_select'];
            $query = 'SELECT DISTINCT p.product_id, t.physicalstocktaking_id
                        FROM '.$installer->getTableName("erp_inventory_physicalstocktaking t").'
                        LEFT JOIN '.$installer->getTableName("erp_inventory_physicalstocktaking_product p").'
                        ON t.physicalstocktaking_id = p.physicalstocktaking_id
                        WHERE t.status > 0 AND t.warehouse_id = "' . $warehouse_id . '" AND t.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"'
            ;

            $results = $readConnection->fetchAll($query);
            if ($results) {
                foreach ($results as $value) {
                    $productIds[] = $value['product_id'];
                    if ($phyids == '') {
                        $phyids = "('" . $value['physicalstocktaking_id'] . "'";
                    } else {
                        $phyids .= ',';
                        $phyids .= "'" . $value['physicalstocktaking_id'] . "'";
                    }
                }
                $phyids .= ")";
            } else {
                $phyids = "('0')";
            }
            $productIds = Mage::helper('inventoryreports')->checkProductInWarehouse($productIds, $warehouse_id);
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $productIds));
            $collection->joinField('old_qty', 'inventoryphysicalstocktaking/physicalstocktaking_product', 'old_qty', 'product_id=entity_id', '{{table}}.old_qty IS NOT NULL AND {{table}}.old_qty > 0 AND {{table}}.physicalstocktaking_id IN ' . $phyids, 'left');
            $collection->joinField('adjust_qty', 'inventoryphysicalstocktaking/physicalstocktaking_product', 'adjust_qty', 'product_id=entity_id', '{{table}}.adjust_qty IS NOT NULL AND {{table}}.adjust_qty > 0 AND {{table}}.physicalstocktaking_id IN ' . $phyids, 'left');
            $collection->getSelect()->columns(array('difference' => new Zend_Db_Expr("SUM(at_adjust_qty.adjust_qty) - SUM(at_old_qty.old_qty)")));
            $collection->getSelect()->group('e.entity_id');
            $total_difference = 0;
            if (count($collection) > 0) {
                foreach ($collection as $col) {
                    if(abs($col->getDifference()) != 0){
                    $total_data[$col->getEntityId()]['product_name'] = Mage::getModel('catalog/product')->load($col->getEntityId())->getName();
                    $total_data[$col->getEntityId()]['difference'] = $col->getDifference();
                    }
                }
            }
        }
        if(count($total_data) > 0){
            foreach ($total_data as $result) {
                $product_name[] = $result['product_name'];
                $difference[] = $result['difference'];
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($product_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $name_value) . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($difference as $number_value) {
                if (abs($number_value) != 0) {
                    if ($j != 0) {
                        $columnSeries['inventory_warehouse']['data'] .= ',';
                    }
                    $columnSeries['inventory_warehouse']['data'] .= abs($number_value);
                    $j++;
                }   
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($total_data as $result) {
                if ($i != 0 && $result['difference'] != 0){
                    $pieSeries .= ',';
                    $pieSeries .= '{name:\'' . $result['product_name'] . '(' . abs($result['difference']) . ' items)\',y:' . abs($result['difference']) . '}';
                    $i++;
                } else if($i == 0 && $result['difference'] != 0) {
                    $pieSeries .= '{name:\'' . $result['product_name'] . '(' . abs($result['difference']) . ' items)\',y:' . abs($result['difference']) . '}';
                    $i++;
                }
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data;
    }

    public function getTransferRequestStockData(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if(empty($requestData)){$requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();}
        $name = 'All Warehouses';
        if($requestData['warehouse_select'] > 0){
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $warehouse_name = array();
        $total_request = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        $is_warehouse = 0;
        if(isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0){
            $is_warehouse = 1;
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select']);
            $sql = 'SELECT s.warehouse_id_from, s.warehouse_name_from, s.warehouse_name_to, p.product_id, p.product_name, SUM(p.qty) AS total_request, s.created_at
                    FROM '.$installer->getTableName("erp_inventory_warehouse_requeststock s").'
                    INNER JOIN '.$installer->getTableName("erp_inventory_warehouse_requeststock_product p").'
                    ON s.warehouse_requeststock_id = p.warehouse_requeststock_id
                    WHERE s.status = 1 AND s.created_at BETWEEN "'.$gettime['date_from'].'" AND "'.$gettime['date_to'].'" AND s.warehouse_name_from = "'.$warehouse_collection->getWarehouseName().'"
                    GROUP BY p.product_id
                    ORDER BY SUM(p.qty) DESC';
        }
        else{
            $is_warehouse = 0;
            $sql = 'SELECT warehouse_name_to, SUM(total_products) AS total_request 
                    FROM '.$installer->getTableName("erp_inventory_warehouse_requeststock").'
                    WHERE status = 1 AND created_at BETWEEN "'.$gettime['date_from'].'" AND "'.$gettime['date_to'].'"
                    GROUP BY warehouse_id_to
                    ORDER BY SUM(total_products) DESC';
        }
        $results = $readConnection->fetchAll($sql);
        if(isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0){
            $checkResult = 0;
            foreach($results as $result){
                if($checkResult > 10){
                    $limit = 10;
                    break;
                }
                if($result['total_request'] == NULL || $result['total_request'] <= 0){
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            } 
            if(isset($limit)){
                $sql .= 'LIMIT '.$limit.'';
                $results = $readConnection->fetchAll($sql);
            }
        }
        if(count($results) > 0){
            if($is_warehouse == '1'){
                foreach ($results as $result) {
                    $warehouse_name[]   =   $result['product_name'];
                    $total_request[]     =   $result['total_request'];
                }
            }
            else{
                foreach ($results as $result) {
                    $warehouse_name[]   =   $result['warehouse_name_to'];
                    $total_request[]     =   $result['total_request'];
                }
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                    if ($i != 0) {
                        $columnCategories .= ',';
                    }
                    $columnCategories .= '"' . $name_value . '"';
                    $i++;
                }
            $columnCategories .= ']';    
            $j=0;
            $columnSeries['inventory_warehouse']['data'] = '[';    
            foreach($total_request as $number_value){
                    if ($j != 0) {
                        $columnSeries['inventory_warehouse']['data'] .= ',';
                    }
                    $columnSeries['inventory_warehouse']['data'] .= $number_value;
                    $j++;
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                if($is_warehouse == '1'){
                    $pieSeries .= '[\'' . $result['product_name'] . '(' . $result['total_request'] . ' items requested)\',' . $result['total_request'] . ']';
                }else{
                    $pieSeries .= '[\'' . $result['warehouse_name_to'] . '(' . $result['total_request'] . ' items requested)\',' . $result['total_request'] . ']';
                }
                $i++;
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data; 
    }

    public function getTransferSendStockData(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if(empty($requestData)){$requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();}
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $name = 'All Warehouses';
        if($requestData['warehouse_select'] > 0){
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $warehouse = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        $warehouse_name = array();
        $total_send = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        $is_warehouse = 0;
        if(isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0){
            $is_warehouse = 1;
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select']);
            $sql = 'SELECT s.warehouse_id_from, s.warehouse_name_from, s.warehouse_name_to, p.product_id, p.product_name, SUM(p.qty) AS total_send, s.created_at
                    FROM '.$installer->getTableName("erp_inventory_warehouse_sendstock s").'
                    INNER JOIN '.$installer->getTableName("erp_inventory_warehouse_sendstock_product p").'
                    ON s.warehouse_sendstock_id = p.warehouse_sendstock_id
                    WHERE s.status = 1 AND s.created_at BETWEEN "'.$gettime['date_from'].'" AND "'.$gettime['date_to'].'" AND s.warehouse_name_from = "'.$warehouse_collection->getWarehouseName().'" 
                    GROUP BY p.product_id
                    ORDER BY SUM(p.qty)';
        }
        else{
            $is_warehouse = 0;
            $sql = 'SELECT warehouse_name_from, SUM(total_products) AS total_send 
                    FROM '.$installer->getTableName("erp_inventory_warehouse_sendstock").'
                    WHERE status = 1 AND created_at BETWEEN "'.$gettime['date_from'].'" AND "'.$gettime['date_to'].'"
                    GROUP BY warehouse_id_from
                    ORDER BY SUM(total_products)';
        }
        $results = $readConnection->fetchAll($sql);
        if(isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0){
            $checkResult = 0;
            foreach($results as $result){
                if($checkResult > 10){
                    $limit = 10;
                    break;
                }
                if($result['total_send'] == NULL){
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            } 
            if(isset($limit)){
                $sql .= 'LIMIT '.$limit.'';
                $results = $readConnection->fetchAll($sql);
            }
        }
        if(count($results) > 0){
            if($is_warehouse == '1'){
                foreach ($results as $result) {
                    $warehouse_name[]   =   $result['product_name'];
                    $total_send[]     =   ((int)$result['total_send']*(-1));
                }
            }
            else{
                foreach ($results as $result) {
                    $warehouse_name[]   =   $result['warehouse_name_from'];
                    $total_send[]     =   ((int)$result['total_send']*(-1));
                }
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                    if ($i != 0) {
                        $columnCategories .= ',';
                    }
                    $columnCategories .= '"' . $name_value . '"';
                    $i++;
                }
            $columnCategories .= ']';    
            $j=0;
            $columnSeries['inventory_warehouse']['data'] = '[';    
            foreach($total_send as $number_value){
                    if ($j != 0) {
                        $columnSeries['inventory_warehouse']['data'] .= ',';
                    }
                    $columnSeries['inventory_warehouse']['data'] .= $number_value;
                    $j++;
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                if($is_warehouse == '1'){
                    $pieSeries .= '[\'' . $result['product_name'] . '(' . ((int)$result['total_send']*(-1)) . ' items sent)\',' . ((int)$result['total_send']*(-1)) . ']';
                }
                else{
                    $pieSeries .= '[\'' . $result['warehouse_name_from'] . '(' . ((int)$result['total_send']*(-1)) . ' items sent)\',' . ((int)$result['total_send']*(-1)) . ']';
                }
                $i++;
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data; 
    }

    public function getWarehouseItemShippedData(){
        $columnSeries = array();
        $is_warehouse = 0;
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $warehouse_name = array();
        $item_shipped = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $is_warehouse = 1;
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select']);
            $sql = 'SELECT w.warehouse_name, w.product_id, sum(w.qty_shipped) AS item_shipped, f.created_at
                    FROM ' . $installer->getTableName("erp_inventory_warehouse_shipment w") . '
                    INNER JOIN ' . $installer->getTableName("sales_flat_shipment f") . '
                    ON w.order_id = f.order_id
                    WHERE w.warehouse_id > 0 AND w.warehouse_name = "' . $warehouse_collection->getWarehouseName() . '" AND f.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                    GROUP BY w.product_id
                    ORDER BY item_shipped DESC 
                    ';
        } else {
            $is_warehouse = 0;
            $sql = 'SELECT w.warehouse_name, SUM(w.qty_shipped) AS item_shipped, f.created_at
                    FROM ' . $installer->getTableName("erp_inventory_warehouse_shipment w") . '
                    INNER JOIN ' . $installer->getTableName("sales_flat_shipment f") . '
                    ON w.order_id = f.order_id
                    WHERE w.warehouse_id > 0 AND f.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                    GROUP BY w.warehouse_name
                    ORDER BY item_shipped DESC ';
        }
        $results = $readConnection->fetchAll($sql);
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $checkResult = 0;
            foreach ($results as $result) {
                if ($checkResult > 10) {
                    $limit = 10;
                    break;
                }
                if ($result['item_shipped'] == NULL || $result['item_shipped'] <= 0) {
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            }
            if (isset($limit)) {
                $sql .= 'LIMIT ' . $limit . '';
                $results = $readConnection->fetchAll($sql);
            }
        }
        if(count($results) > 0){
            if ($is_warehouse == 0) {
                foreach ($results as $result) {
                    $warehouse_name[] = $result['warehouse_name'];
                    $item_shipped[] = $result['item_shipped'];
                }
            } else {
                foreach ($results as $result) {
                    $warehouse_name[] = Mage::getModel('catalog/product')->load($result['product_id'])->getName();
                    $item_shipped[] = $result['item_shipped'];
                }
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['name'] = $this->__('Item Shipped by Warehouse');
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($item_shipped as $number_value) {
                if ($j != 0) {
                    $columnSeries['inventory_warehouse']['data'] .= ',';
                }
                $columnSeries['inventory_warehouse']['data'] .= $number_value;
                $j++;
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            //pie data
            $pieSeries = '';
            $i = 0;
            if ($is_warehouse == 0) {
                foreach ($results as $result) {
                    if ($i != 0)
                        $pieSeries .= ',';
                    $pieSeries .= '[\'' .$result['warehouse_name'] . '(' . $result['item_shipped'] . ' items shipped)\',' . $result['item_shipped'] . ']';
                    $i++;
                }
            }
            else {
                foreach ($results as $result) {
                    if ($i != 0)
                        $pieSeries .= ',';
                    $pieSeries .= '[\'' . Mage::getModel('catalog/product')->load($result['product_id'])->getName() . '(' . $result['item_shipped'] . ' items shipped)\',' . $result['item_shipped'] . ']';
                    $i++;
                }
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data;
    }

    public function getWarehouseRevenueData(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $warehouse_name = array();
        $revenue = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        $prodNameAttrId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','name');
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {    //Warehouse selected
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select']);
            
            $sql = 'SELECT f.value as warehouse_name, sum(s.subtotal_shipped) as revenue, o.created_at
                    FROM ' . $installer->getTableName("erp_inventory_warehouse_shipment s") . '
                    INNER JOIN ' . $installer->getTableName("sales_flat_order o") . '
                    ON s.order_id = o.entity_id
                    INNER JOIN ' . $installer->getTableName("catalog_product_entity_varchar f") . '
                    ON s.product_id = f.entity_id AND f.attribute_id=' . $prodNameAttrId . '
                    WHERE s.warehouse_id = "' . $requestData['warehouse_select'] . '" AND s.qty_shipped > 0 AND o.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                    GROUP BY s.product_id
                    ORDER BY sum(s.subtotal_shipped) DESC
                    ';
        } else {   //All warehouses     
            $sql = 'SELECT s.warehouse_name, sum(s.subtotal_shipped) as revenue, o.created_at
                    FROM ' . $installer->getTableName("erp_inventory_warehouse_shipment s") . '
                    INNER JOIN ' . $installer->getTableName("sales_flat_order o") . '
                    ON s.order_id = o.entity_id
                    WHERE s.qty_shipped > 0 AND o.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                    GROUP BY s.warehouse_name
                    ORDER BY sum(s.subtotal_shipped) DESC
                    ';
        }
        $results = $readConnection->fetchAll($sql);
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $checkResult = 0;
            foreach ($results as $result) {
                if ($checkResult > 10) {
                    $limit = 10;
                    break;
                }
                if ($result['revenue'] == NULL || $result['revenue'] <= 0) {
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            }
            if (isset($limit)) {
                $sql .= 'LIMIT ' . $limit . '';
                $results = $readConnection->fetchAll($sql);
            }
        }
        $data = array();
        if(count($results) > 0){
            foreach ($results as $result) {
                $warehouse_name[] = $result['warehouse_name'];
                $revenue[] = round($result['revenue']);
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['name'] = $this->__('Sales By Warehouse Revenue');
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($revenue as $number_value) {
                if ($j != 0) {
                    $columnSeries['inventory_warehouse']['data'] .= ',';
                }
                $columnSeries['inventory_warehouse']['data'] .= $number_value;
                $j++;
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                $pieSeries .= '[\'' . $result['warehouse_name'] . '(' . round($result['revenue']) . ')\',' . round($result['revenue']) . ']';
                $i++;
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data;
    }



    public function getTotalOrderData(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
            $display = 0;
        } else {
            $display = 1;
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $warehouse_name = array();
        $total_order = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $sql = 'SELECT COUNT(DISTINCT s.order_id) AS total_order, s.warehouse_name
                    FROM ' . $installer->getTableName("erp_inventory_warehouse_shipment s") . '
                    LEFT JOIN ' . $installer->getTableName("sales_flat_order o") . '
                    ON s.order_id = o.entity_id AND s.warehouse_id = "' . $requestData['warehouse_select'] . '"
                    WHERE s.warehouse_id > 0 AND o.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] .'"'
                    ;
        } else {
            $sql = 'SELECT COUNT(DISTINCT s.order_id) AS total_order, s.warehouse_name
                        FROM ' . $installer->getTableName("erp_inventory_warehouse_shipment s") . '
                        LEFT JOIN ' . $installer->getTableName("sales_flat_order o") . '
                        ON s.order_id = o.entity_id 
                        WHERE s.warehouse_id > 0 AND o.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                        GROUP BY s.warehouse_id
                        ORDER BY COUNT(DISTINCT s.order_id)'
            ;
        }
        $results = $readConnection->fetchAll($sql);
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $checkResult = 0;
            foreach ($results as $result) {
                if ($checkResult > 10) {
                    $limit = 10;
                    break;
                }
                if ($result['total_order'] == NULL || $result['total_order'] <= 0) {
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            }
            if (isset($limit)) {
                $sql .= 'LIMIT ' . $limit . '';
                $results = $readConnection->fetchAll($sql);
            }
        }
        $data = array();
        if(count($results) > 0){
            foreach ($results as $result) {
                $warehouse_name[] = $result['warehouse_name'];
                $total_order[] = $result['total_order'];
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['name'] = $this->__('Total Sales Orders by Warehouse');
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($total_order as $number_value) {
                if ($j != 0) {
                    $columnSeries['inventory_warehouse']['data'] .= ',';
                }
                $columnSeries['inventory_warehouse']['data'] .= $number_value;
                $j++;
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';

            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                $pieSeries .= '[\'' . $result['warehouse_name'] . '(' . $result['total_order'] . ' orders)\',' . $result['total_order'] . ']';
                $i++;
            }
            $data['pie_series'] = $pieSeries;
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
        }
        return $data;
    }

    public function getTotalQtyAdjuststockReportData(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $warehouse_name = array();
        $total_adjust = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $is_warehouse = 1;
            $prodNameAttrId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','name');
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select']);
            $sql = 'SELECT f.value as warehouse_name, SUM(p.adjust_qty) AS total_adjust
                    FROM ' . $installer->getTableName("erp_inventory_adjuststock a ") . '
                    LEFT JOIN ' . $installer->getTableName("erp_inventory_adjuststock_product p ") . '
                    ON a.adjuststock_id = p.adjuststock_id
                    LEFT JOIN ' . $installer->getTableName("catalog_product_entity_varchar f") . '
                    ON p.product_id = f.entity_id AND f.attribute_id=' . $prodNameAttrId . '
                    WHERE a.warehouse_id = "' . $requestData['warehouse_select'] . '" AND a.status = 1 AND a.confirmed_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '" AND a.warehouse_id = ' . $requestData['warehouse_select'] . '
                    GROUP BY p.product_id
                    ORDER BY SUM(p.adjust_qty) DESC
                    ';
        } else {
            $is_warehouse = 0;
            $sql = 'SELECT a.warehouse_name, SUM(p.adjust_qty) AS total_adjust
                    FROM ' . $installer->getTableName("erp_inventory_adjuststock a ") . '
                    INNER JOIN ' . $installer->getTableName("erp_inventory_adjuststock_product p ") . '
                    ON a.adjuststock_id = p.adjuststock_id
                    WHERE a.status = 1 AND a.confirmed_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                    GROUP BY a.warehouse_name
                    ORDER BY SUM(p.adjust_qty) DESC
                    ';
        }
        $results = $readConnection->fetchAll($sql);
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $checkResult = 0;
            foreach ($results as $result) {
                if ($checkResult > 10) {
                    $limit = 10;
                    break;
                }
                if ($result['total_adjust'] == NULL || $result['total_adjust'] <= 0) {
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            }
            if (isset($limit)) {
                $sql .= 'LIMIT ' . $limit . '';
                $results = $readConnection->fetchAll($sql);
            }
        }
        $data = array();
        if(count($results) > 0){
            foreach ($results as $result) {
                $warehouse_name[] = $result['warehouse_name'];
                $total_adjust[] = $result['total_adjust'];
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['name'] = $this->__('Total Adjusted Qty');
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($total_adjust as $adjust_value) {
                if ($adjust_value > 0) {
                    if ($j != 0) {
                        $columnSeries['inventory_warehouse']['data'] .= ',';
                    }
                    $columnSeries['inventory_warehouse']['data'] .= $adjust_value;
                    $j++;
                }
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($result['total_adjust'] > 0) {
                    if ($i != 0)
                        $pieSeries .= ',';
                    $pieSeries .= '[\'' . $result['warehouse_name'] . '(' . $result['total_adjust'] . ' items)\',' . $result['total_adjust'] . ']';
                    $i++;
                }
            }
            $data['pie_series'] = $pieSeries;
        }
        return $data;
    }

    public function getNumberProductAdjuststockReportData(){
        $columnSeries = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $name = 'All Warehouses';
        if ($requestData['warehouse_select'] > 0) {
            $name = Mage::helper('inventoryreports')->getWarehouseName($requestData['warehouse_select']);
            $display = 0;
        } else {
            $display = 1;
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $warehouse_name = array();
        $product_number = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->load($requestData['warehouse_select']);
            $sql = 'SELECT COUNT( DISTINCT p.product_id ) AS product_number, a.warehouse_name
                    FROM ' . $installer->getTableName("erp_inventory_adjuststock a") . '
                    LEFT JOIN ' . $installer->getTableName("erp_inventory_adjuststock_product p") . '
                    ON a.adjuststock_id = p.adjuststock_id
                    WHERE a.status = 1 AND a.confirmed_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '" AND a.warehouse_id = "' . $requestData['warehouse_select'] . '"
                    GROUP BY a.warehouse_id
                    ORDER BY product_number';
        } else {
            $sql = 'SELECT COUNT( DISTINCT p.product_id ) as product_number, a.warehouse_name
                    FROM ' . $installer->getTableName("erp_inventory_adjuststock a") . '
                    INNER JOIN ' . $installer->getTableName("erp_inventory_adjuststock_product p") . '
                    ON a.adjuststock_id = p.adjuststock_id
                    WHERE a.status = 1 AND a.confirmed_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                    GROUP BY a.warehouse_id
                    ORDER BY product_number';
        }
        $results = $readConnection->fetchAll($sql);
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select'] > 0) {
            $checkResult = 0;
            foreach ($results as $result) {
                if ($checkResult > 10) {
                    $limit = 10;
                    break;
                }
                if ($result['product_number'] == NULL || $result['product_number'] <= 0) {
                    $limit = $checkResult;
                    continue;
                }
                $checkResult++;
            }
            if (isset($limit)) {
                $sql .= 'LIMIT ' . $limit . '';
                $results = $readConnection->fetchAll($sql);
            }
        }
        $data = array();
        if(count($results) > 0){
            foreach ($results as $result) {
                $warehouse_name[] = $result['warehouse_name'];
                $product_number[] = $result['product_number'];
            }
            $i = 0;
            $columnCategories = '[';
            foreach ($warehouse_name as $name_value) {
                if ($i != 0) {
                    $columnCategories .= ',';
                }
                $columnCategories .= '"' . $name_value . '"';
                $i++;
            }
            $columnCategories .= ']';
            $j = 0;
            $columnSeries['inventory_warehouse']['name'] = $this->__('Number of Products Being Adjusted Qty by Warehouse');
            $columnSeries['inventory_warehouse']['data'] = '[';
            foreach ($product_number as $number_value) {
                if ($number_value > 0) {
                    if ($j != 0) {
                        $columnSeries['inventory_warehouse']['data'] .= ',';
                    }
                    $columnSeries['inventory_warehouse']['data'] .= $number_value;
                    $j++;
                }
            }
            $columnSeries['inventory_warehouse']['data'] .= ']';
            $data['column_series'] = $columnSeries;
            $data['column_categories'] = $columnCategories;
            //pie data
            $pieSeries = '';
            $i = 0;
            foreach ($results as $result) {
                if ($i != 0)
                    $pieSeries .= ',';
                $pieSeries .= '[\'' . $result['warehouse_name'] . '(' . $result['product_number'] . ' items)\',' . $result['product_number'] . ']';
                $i++;
            }
            $data['pie_series'] = $pieSeries;
        }
        return $data;
    }

    protected function _getAttributeTableAlias($attributeCode)
    {
        return 'at_' . $attributeCode;
    }

}