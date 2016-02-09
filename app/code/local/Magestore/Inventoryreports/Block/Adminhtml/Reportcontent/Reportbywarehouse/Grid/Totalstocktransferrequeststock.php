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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Totalstocktransferrequeststock extends Mage_Adminhtml_Block_Widget_Grid {
//
    public function __construct() {
        parent::__construct();
        $this->setId('totalstocktransferrequeststockgrid');
        $this->setDefaultSort('total_request');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $resource = Mage::getModel('core/resource');
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if(empty($requestData)){
            $requestData = Mage::helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        
        if(!$requestData['warehouse_select']){  //NO WAREHOUSE SELECTED --> All Warehouses
            $collection = Mage::getModel('inventorywarehouse/requeststock')->getCollection()
                    ->addFieldToFilter('status',1);
            $collection->getSelect()
            ->columns('SUM(total_products) AS total_request')
            ->where('created_at BETWEEN "'.$gettime['date_from'].'" and "'.$gettime['date_to'].'"')
            ->group('warehouse_id_to')
            ;
        }
        else{   //  WAREHOUSE SELECTED
            $collection = Mage::getModel('inventorywarehouse/requeststock_product')->getCollection();
            $collection->getSelect()
            ->join(array('requeststock' => $resource->getTableName('erp_inventory_warehouse_requeststock')), 
                    'requeststock.warehouse_requeststock_id = main_table.warehouse_requeststock_id', 
                    array('main_table.product_name', 'sum(main_table.qty) as total_request', 'requeststock.created_at', 'main_table.product_name'))
            ->where('requeststock.status = 1 and requeststock.warehouse_id_to = '.$requestData['warehouse_select'].' and requeststock.created_at BETWEEN "'.$gettime['date_from'].'" and "'.$gettime['date_to'].'"')
            ->group('main_table.product_id')
            ;
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if($requestData['warehouse_select']){   //Warehouse Selected
            $this->addColumn('product_name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'product_name',
            ));
            $this->addColumn('total_request', array(
                'header' => Mage::helper('inventoryreports')->__('Qty Request'),
                'align' => 'right',
                'index' => 'total_request',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalRequestEachCallback'),
            ));
        }else{  // All Warehouses
            $this->addColumn('warehouse_name_to', array(
                'header' => Mage::helper('inventoryreports')->__('Warehouse Name'),
                'align' => 'left',
                'index' => 'warehouse_name_to'
            ));
            $this->addColumn('total_request', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Requested'),
                'align' => 'right',
                'index' => 'total_request',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalRequestAllCallback'),
            ));
        }
//        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryreports')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('inventoryreports')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/totalstocktransferrequeststockgrid',array('top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalRequestAllCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('SUM(total_products) <= ?', $filter['to']);
        }
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('SUM(total_products) >= ?', $filter['from']);
        }        
        return $this;
    }
    
    public function _filterTotalRequestEachCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(main_table.qty) <= ?', $filter['to']);
        }
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(main_table.qty) >= ?', $filter['from']);
        }        
        return $this;
    }
    

}