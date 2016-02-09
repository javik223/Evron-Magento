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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Grid 
    extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid {

    protected $_requestData = null;
    protected $_filter = null;

    public function __construct() {
        parent::__construct();
        $this->setId('reportorderGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        //show total row
        $this->setCountTotals(true);
    }

    /**
     * 
     * @return collection
     */
    protected function _prepareCollection() {
        $data = Mage::helper('inventoryreports/order')->getOrderReportCollection($this->getRequestData());
        if (is_array($data)) {
            $collection = $data['collection'];
            $this->_filter = $data['filter'];
        } else {
            $collection = $data;
        }
        // Sort grid data
        $this->_sortCollection($collection);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     * 
     */
    protected function _prepareColumns() {

        $this->_addFirstColumn();
        
        $this->addColumn('sum_qty_ordered', array(
            'header' => Mage::helper('inventoryreports')->__('Ordered Qty'),
            'align' => 'right',
            'index' => 'sum_qty_ordered',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));
        /*
        if($this->isSalesSKUReport()){
            $this->addColumn('child_product_qty', array(
                'header' => Mage::helper('inventoryreports')->__('Sold Qty<br/>per child products'),
                'align' => 'right',
                'index' => 'child_product_qty',
                'width' => '50px',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbyorder_renderer_childproductqty'
            ));   
        }     
        */
        $this->addColumn('count_entity_id', array(
            'header' => Mage::helper('inventoryreports')->__('Total Order'),
            'align' => 'right',
            'index' => 'count_entity_id',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));        

        $this->addColumn('sum_base_grand_total', array(
            'header' => Mage::helper('inventoryreports')->__('Grand Total'),
            'align' => 'right',
            'index' => 'sum_base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        if ($this->isSalesbyHourReport()) {
            $this->_addColumnsPerHour();
        }

        if($this->isSalesbyDayReport()) {
            $this->_addColumnsPerDay();
        }

        $this->addColumn('sales_ratio', array(
            'header' => Mage::helper('inventoryreports')->__('#Ratio'),
            'align' => 'right',
            'index' => 'sales_ratio',
            'filter' => false,
            'sortable' => false,
            'width' => '50px',
            'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbyorder_renderer_salesratio'
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('sales')->__('Action'),
            'width' => '50px',
            'align' => 'center',
            'type' => 'action',
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbyorder_renderer_action'
        ));
        
        $this->addCSVExport();

        return parent::_prepareColumns();
    }

    /**
     * Get data totals
     * 
     * @return \Varien_Object
     */
    public function getTotals() {
        $totals = new Varien_Object();
        $totalsData = array(
            'count_entity_id' => 0,
            'sum_qty_ordered' => 0,
            'sum_base_grand_total' => 0,
            'base_grand_total_per_hour' => 0,
            'qty_ordered_per_hour' => 0,
            'total_order_per_hour' => 0,
            'base_grand_total_per_day' => 0,
            'qty_ordered_per_day' => 0,
            'total_order_per_day' => 0,            
        );
        foreach ($this->getCollection() as $item) {
            foreach ($totalsData as $field => $value) {
                $totalsData[$field] += $item->getData($field);
            }
        }
        //First column in the grid
        $totalsData['time_range'] = $this->__('Totals');
        $totalsData['warehouse_name'] = $this->__('Totals');
        $totalsData['supplier_name'] = $this->__('Totals');
        $totalsData['sku'] = $this->__('Totals');
        $totalsData['product_sku'] = $this->__('Totals');
        $totalsData['att_' . $this->getReport()] = $this->__('Totals');
        $totalsData['action'] = $this->__('Totals');
        //calculate ratio
        $renderer = $this->getLayout()->createBlock('inventoryreports/adminhtml_reportcontent_reportbyorder_renderer_salesratio');
        $totalValue = $renderer->getTotalData();
        $totalsData['sales_ratio']  = 0;
        if($totalValue){
            $totalsData['sales_ratio'] = round($totalsData['sum_base_grand_total'] / $totalValue * 100, 2);
        }
        //format currency
        $totalsData['sum_base_grand_total'] = $this->helper('core')->currency($totalsData['sum_base_grand_total']);
        $totalsData['base_grand_total_per_hour'] = $this->helper('core')->currency($totalsData['base_grand_total_per_hour']);
        $totalsData['base_grand_total_per_day'] = $this->helper('core')->currency($totalsData['base_grand_total_per_day']);
        $totals->setData($totalsData);
        return $totals;
    }

    /**
     * Add value per hour columns
     */
    protected function _addColumnsPerHour() {
        $this->addColumn('total_order_per_hour', array(
            'header' => Mage::helper('inventoryreports')->__('Avg. Order/Hour'),
            'align' => 'right',
            'index' => 'total_order_per_hour',
            'type' => 'number',
            'width' => '30px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('qty_ordered_per_hour', array(
            'header' => Mage::helper('inventoryreports')->__('Avg. Qty/Hour'),
            'align' => 'right',
            'index' => 'qty_ordered_per_hour',
            'type' => 'number',
            'width' => '30px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('base_grand_total_per_hour', array(
            'header' => Mage::helper('inventoryreports')->__('Avg. Grand Total/Hour'),
            'align' => 'right',
            'index' => 'base_grand_total_per_hour',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'width' => '30px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));
    }
    
    /**
     * Add value per day columns
     */
    protected function _addColumnsPerDay() {
        $this->addColumn('total_order_per_day', array(
            'header' => Mage::helper('inventoryreports')->__('Avg. Order/Day'),
            'align' => 'right',
            'index' => 'total_order_per_day',
            'type' => 'number',
            'width' => '30px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('qty_ordered_per_day', array(
            'header' => Mage::helper('inventoryreports')->__('Avg. Qty/Day'),
            'align' => 'right',
            'index' => 'qty_ordered_per_day',
            'type' => 'number',
            'width' => '30px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('base_grand_total_per_day', array(
            'header' => Mage::helper('inventoryreports')->__('Avg. Grant Total/Day'),
            'align' => 'right',
            'index' => 'base_grand_total_per_day',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'width' => '30px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));
    }    

    /**
     * Add first column to grid report
     * 
     * @return \Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Grid
     */
    protected function _addFirstColumn() {
        $reportCode = $this->getReport();
        switch ($reportCode) {
            case 'sales_days':
            case 'days_of_month':
            case 'days_of_week':
            case 'hours_of_day':
                $this->addColumn('time_range', array(
                    'header' => Mage::helper('inventoryreports')->__('Time'),
                    'align' => 'left',
                    'index' => 'time_range',
                    'width' => '100px',
                    'filter' => false,
                    'renderer' => 'inventoryreports/adminhtml_reportcontent_renderer_ordertimerange'
                ));
                break;
            case 'sales_warehouse':
                $this->addColumn('warehouse_name', array(
                    'header' => Mage::helper('inventoryreports')->__('Warehouse'),
                    'align' => 'left',
                    'index' => 'warehouse_name',
                    'width' => '100px',
                ));
                break;
            case 'sales_supplier':
                $this->addColumn('supplier_name', array(
                    'header' => Mage::helper('inventoryreports')->__('Supplier'),
                    'align' => 'left',
                    'index' => 'supplier_name',
                    'width' => '100px',
                ));
                break;
            case 'sales_sku':
                $this->addColumn('product_sku', array(
                    'header' => Mage::helper('inventoryreports')->__('SKU'),
                    'align' => 'left',
                    'index' => 'product_sku',
                    'width' => '100px',
                    'filter_condition_callback' => array($this, '_filterTextCallback'),
                ));
                break;
            default:
                $this->addColumn('att_' . $reportCode, array(
                    'header' => Mage::helper('inventoryreports')->__(ucwords(str_replace('_', ' ', $reportCode))),
                    'align' => 'left',
                    'index' => 'att_' . $reportCode,
                    'width' => '100px',
                    'filter' => false,
                ));
        }
        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/reportordergrid', array('type_id' => 'sales', 'top_filter' => $this->getRequest()->getParam('top_filter'))
        );
    }

    /**
     * Get real filed from alias in sql
     * 
     * @param string $alias
     * @return string
     */
    protected function _getRealFieldFromAlias($alias) {
        $field = null;
        $report = $this->getReport();
        $totalFrame = 'TIMESTAMPDIFF(MONTH, \''.$this->getDateRange('from').'\', \''.$this->getDateRange('to').'\' )';
        $totalFrame = ($report == 'days_of_month') ? $totalFrame : 'TIMESTAMPDIFF(WEEK, \''.$this->getDateRange('from').'\', \''.$this->getDateRange('to').'\' )';
        switch ($alias) {
            case 'count_entity_id':
                $field = 'COUNT(DISTINCT main_table.entity_id)';
                $field = $this->isSalesSupplierReport() ? 'COUNT(DISTINCT order.entity_id)' : $field;
                $field = $this->isSalesSKUReport() ? 'COUNT(DISTINCT order.entity_id)' : $field;
                $field = $this->isSalesWarehouseReport() ? 'COUNT(DISTINCT order.entity_id)' : $field;
                break;
            case 'product_sku':
                $field = 'product.sku';
                break;
            case 'warehouse_name':
                $field = 'IFNULL(warehouseShip.warehouse_name,\''.$this->__('Unassigned Warehouse').'\')';
                break;
            case 'sum_subtotal':
                $field = 'IFNULL(SUM(main_table.subtotal),0)';
                break;
            case 'sum_base_subtotal':
                $field = 'IFNULL(SUM(main_table.base_subtotal),0)';
                break;
            case 'sum_grand_total':
                $field = 'IFNULL(SUM(main_table.grand_total),0)';
                $field = $this->isSalesWarehouseReport() ? 
                                'IFNULL( SUM( orderItem.row_total_incl_tax * (main_table.qty_shipped - main_table.qty_refunded) / orderItem.qty_ordered ),'
                                . ' SUM(orderItem.row_total_incl_tax))' : $field;
                $field = $this->isSalesSupplierReport() ? 'IFNULL(SUM(orderItem.row_total),0)' : $field;
                $field = $this->isSalesSKUReport() ? 'IFNULL(SUM(main_table.row_total),0)' : $field;
                break;
            case 'sum_base_grand_total':
                $field = 'IFNULL(SUM(main_table.base_grand_total),0)';
                $field = $this->_isSalesOrderQuery() ? $field : 'IFNULL(SUM(order.base_grand_total),0)';
                $field = $this->isSalesWarehouseReport() ? 
                                'IFNULL( SUM( orderItem.base_row_total_incl_tax * (main_table.qty_shipped - main_table.qty_refunded) / orderItem.qty_ordered ),'
                                . ' SUM(orderItem.base_row_total_incl_tax))' : $field;
                $field = $this->isSalesSupplierReport() ? 'IFNULL(SUM(orderItem.base_row_total),0)' : $field;
                $field = $this->isSalesSKUReport() ? 'IFNULL(SUM(main_table.base_row_total),0)' : $field;
                break;
            case 'sum_tax_amount':
                $field = 'IFNULL(SUM(main_table.tax_amount),0)';
                break;
            case 'sum_base_tax_amount':
                $field = 'IFNULL(SUM(main_table.base_tax_amount),0)';
                break;
            case 'sum_qty_ordered':
                $field = 'IFNULL(SUM(main_table.total_qty_ordered),0)';
                $field = $this->_isSalesOrderQuery() ? $field : 'IFNULL(SUM(order.total_qty_ordered),0)';
                $field = $this->isSalesWarehouseReport() ? 'SUM(IFNULL(main_table.qty_shipped, orderItem.qty_ordered))' : $field;
                $field = $this->isSalesSupplierReport() ? 'IFNULL(SUM(orderItem.qty_ordered),0)' : $field;
                $field = $this->isSalesSKUReport() ? 'IFNULL(SUM(main_table.qty_ordered),0)' : $field;
                break;
            case 'qty_ordered_per_hour':
                $field = 'ROUND(IFNULL(SUM(main_table.total_qty_ordered),0)'
                        . ' / DATEDIFF(\'' . $this->getDateRange('to') . '\', \'' . $this->getDateRange('from') . '\' ), 2)';
                break;
            case 'base_grand_total_per_hour':
                $field = 'IFNULL(SUM(main_table.base_grand_total),0)'
                        . ' / DATEDIFF(\'' . $this->getDateRange('to') . '\', \'' . $this->getDateRange('from') . '\' )';
                break;
            case 'total_order_per_hour':
                $field = 'ROUND(COUNT(DISTINCT main_table.entity_id)'
                        . ' / DATEDIFF(\'' . $this->getDateRange('to') . '\', \'' . $this->getDateRange('from') . '\' ), 2)';
                break;
            case 'qty_ordered_per_day':
                $field = 'ROUND(IFNULL(SUM(main_table.total_qty_ordered),0)'
                                        ." / IF($totalFrame > 0, $totalFrame, 1), 2)";
                break;
            case 'total_order_per_day':
                $field = 'ROUND(COUNT(DISTINCT main_table.entity_id)'
                                        ." / IF($totalFrame > 0, $totalFrame, 1), 2)";
                break;
            case 'base_grand_total_per_day':
                $field = 'IFNULL(SUM(main_table.base_grand_total),0)'
                                        ." / IF($totalFrame > 0, $totalFrame, 1)";
                break;
            default :
                $field = $alias;
        }
        if($field != $alias){
            $field = new Zend_Db_Expr($field);
        }
        return $field;
    }
    
    /**
     * Rewrite function to add more row to grid
     * 
     * @return \Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Grid
     */
    protected function _afterLoadCollection()
    {
        if($this->isSalesSupplierReport()){
            $this->_addUnassignedSupplierRow();
        }
        return $this;
    }  
    
    /**
     * Add unassigned supplier row to grid
     * 
     * @return \Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Grid
     */
    protected function _addUnassignedSupplierRow() {
        $collection = $this->getCollection();
        $item = $this->helper('inventoryreports/order')->getUnassignedSupplierSales($this->getRequestData());
        $item->setData('supplier_name', $this->__('Unassigned Supplier'));
        $item->setData('id','no_supplier');
        $collection->addItem($item);
        return $this;
    }
    
    /**
     * Get row class in grid
     * 
     * @param Varien_Object $item
     * @return string
     */
    public function getRowClass($item){
        return $item->getData('id') == 'no_supplier' ? 'unassigned' : null;
    }
    
    public function getRowUrl($item){
        return null;
    }    
    
    /**
     * Check if query from sales order table
     * 
     * @return boolean
     */
    protected function _isSalesOrderQuery() {
        $selectQuery = $this->getCollection()->getSelect()->__toString();
        if (strpos($selectQuery, 'sales_flat_order` AS `order`') === false)
            return true;
        return false;
    }    
}
