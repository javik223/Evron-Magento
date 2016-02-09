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
abstract class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid extends Magestore_Inventoryplus_Block_Adminhtml_Widget_Grid {

    /**
     * Add export csv button to Grid
     * 
     */
    public function addCSVExport() {
        $this->addExportType('adminhtml/inr_report/exportCsv/report/' . $this->getReport() . '/type_id/' . $this->getTypeId()
                , Mage::helper('inventoryreports')->__('CSV'));
    }
    
    /**
     * Get report type
     * 
     * @return string
     */
    public function getTypeId() {
        return $this->getRequest()->getParam('type_id');
    }

    /**
     * Get data of submited report filter
     * 
     * @return array
     */
    public function getRequestData($field = null) {
        if (!$this->hasData('request_data')) {
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
            $requestData['type_id'] = $this->getTypeId();
            $this->setData('request_data', $requestData);
        }
        $data = $this->getData('request_data');
        return $field ? (isset($data[$field]) ? $data[$field] : null) : $data;
    }

    /**
     * Get report code
     * 
     * @return string
     */
    public function getReport() {
        $requestData = $this->getRequestData();
        if (isset($requestData['report_radio_select'])) {
            return $requestData['report_radio_select'];
        }
        return null;
    }
    
    /**
     * Get date range of filter
     *
     * @param string $type
     * @return array|string
     */
    public function getDateRange($type = null){
        if(!$this->hasData('date_range')){
            $dateRange = $this->helper('inventoryreports')->getTimeRange($this->getRequestData());
            $this->setData('date_range', $dateRange);
        }
        $dateRange = $this->getData('date_range');
        return $type ? $dateRange[$type] : $dateRange;
    }

    /**
     * Check if show report selector
     * 
     * @return bool
     */
    public function isShowReportSelector() {
        $filterData = new Varien_Object();
        $requestData = $this->getRequestData();
        $report_radio_select = $requestData['report_radio_select'];
        return $this->helper('inventoryreports')->checkDisplay($report_radio_select);
    }

    /**
     * Check if it's showing sales warehouse report
     * 
     * @return bool
     */
    public function isSalesWarehouseReport() {
        if ($this->getReport() == 'sales_warehouse') {
            return true;
        }
        return false;
    }
    

    /**
     * Check if it's showing sales supplier report
     * 
     * @return bool
     */
    public function isSalesSupplierReport() {
        if ($this->getReport() == 'sales_supplier') {
            return true;
        }
        return false;
    }

    /**
     * Check if it's showing sales sku report
     * 
     * @return bool
     */
    public function isSalesSKUReport() {
        if ($this->getReport() == 'sales_sku') {
            return true;
        }
        return false;
    }
    
    
    /**
     * Check if it's showing purchase order supplier report
     * 
     * @return boolean
     */
    public function isPOSupplierReport() {
        if($this->getReport() == 'po_supplier') {
            return true;
        }
        return false;
    }    
    
    /**
     * Check if it's showing purchase order warehouse report
     * 
     * @return boolean
     */
    public function isPOWarehouseReport() {
        if($this->getReport() == 'po_warehouse') {
            return true;
        }
        return false;
    }
    
    /**
     * Check if it's showing purchase order warehouse report
     * 
     * @return boolean
     */
    public function isPOSKUReport() {
        if($this->getReport() == 'po_sku') {
            return true;
        }
        return false;
    }   
    
    /**
     * Check if it's showing stock in report
     * 
     * @return boolean
     */
    public function isStockInReport() {
        if($this->getReport() == 'stock_in') {
            return true;
        }
        return false;
    }    
    
    /**
     * Check if it's showing stock out report
     * 
     * @return boolean
     */
    public function isStockOutReport() {
        if($this->getReport() == 'stock_out') {
            return true;
        }
        return false;
    }   
    
    /**
     * Check if it's showing stock out report
     * 
     * @return boolean
     */
    public function isSalesbyHourReport() {
        if($this->getReport() == 'hours_of_day') {
            return true;
        }
        return false;
    }    
    
    /**
     * Check if it's showing stock out report
     * 
     * @return boolean
     */
    public function isSalesbyDayReport() {
        if($this->getReport() == 'days_of_month' || $this->getReport() == 'days_of_week') {
            return true;
        }
        return false;
    }   
    
    /**
     * Add warehouse columns to grid
     * 
     * @param string $render
     */
    protected function _addWarehouseCoulmn($render) {
        if($this->isPOWarehouseReport()){
            return $this;
        }
        $warehouses = $this->helper('inventoryreports')->getAllWarehouseName();
        $this->setData('warehouses', $warehouses);
        if (!count($warehouses)) {
            return;
        }
        foreach ($warehouses as $warehouseId => $warehouseName) {
            if($this->getRequestData('warehouse_select') == $warehouseId){
                continue;
            }
            $this->addColumn('warehouse_qty_' . $warehouseId, array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty in<br/> %s', $warehouseName),
                'align' => 'right',
                'index' => 'warehouse_qty_' . $warehouseId,
                'filter' => false,
                'sortable' => false,
                'width' => '50px',
                'warehouse_id' => $warehouseId,
                'renderer' => $render,
            ));
        }
        return $this;
    }
    
    /**
     * Sort collection
     * 
     * @return collection
     */
    protected function _prepareCollection() {
        if ($this->getCollection()) {
            $this->_sortCollection($this->getCollection());
        }
        return parent::_prepareCollection();
    }

    /**
     * Filter text field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterTextCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();    
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        $collection->getSelect()->where($field . ' like \'%' . $filter . '%\'');
        return $collection;
    }

    /**
     * Filter number field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterNumberCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        if (isset($filter['from'])) {
            $collection->getSelect()->having($field . ' >= \'' . $filter['from'] . '\'');
        }
        if (isset($filter['to'])) {
            $collection->getSelect()->having($field . ' <= \'' . $filter['to'] . '\'');
        }
        return $collection;
    }

    /**
     * Sort collection
     * 
     * @param collection $collection
     * @return collection
     */
    protected function _sortCollection($collection) {
        $sort = $this->getRequest()->getParam('sort', $this->_defaultSort);
        $dir = $this->getRequest()->getParam('dir', $this->_defaultDir);
        $this->setCollection($collection);
        $field = $sort;
        if ($field) {
            $collection->getSelect()->order("$field $dir");
        }
        return $collection;
    }

    /**
     * Get real filed from alias in sql
     * 
     * @param string $alias
     * @return string
     */
    abstract protected function _getRealFieldFromAlias($alias);
}
