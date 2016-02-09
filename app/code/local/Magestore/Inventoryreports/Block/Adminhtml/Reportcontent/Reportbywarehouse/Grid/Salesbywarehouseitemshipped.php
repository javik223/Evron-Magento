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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Salesbywarehouseitemshipped extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('salesbywarehouseitemshippedgrid');
        $this->setDefaultSort('total_shipped');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    
    protected function _prepareCollection() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if(empty($requestData)){
            $requestData = Mage::helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $prodNameAttrId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','name');
        if(!$requestData['warehouse_select']){  //All Warehouses
            $collection = Mage::getModel('inventoryplus/warehouse_shipment')->getCollection();
            $collection->getSelect()
            ->join( array('sales_shipment'=> Mage::getSingleton("core/resource")->getTableName('sales_flat_shipment')), 'main_table.order_id = sales_shipment.order_id', array('main_table.warehouse_name', 'sum(main_table.qty_shipped) as total_shipped', 'sales_shipment.created_at'))
            ->where('sales_shipment.created_at BETWEEN "'.$gettime['date_from'].'" and "'.$gettime['date_to'].'" AND main_table.warehouse_id > 0')
            ->group('main_table.warehouse_name')
            ;
        }
        else{   //  WAREHOUSE SELECTED
            $collection = Mage::getModel('inventoryplus/warehouse_shipment')->getCollection();
            $collection->getSelect()
            ->join( array('sales_shipment'=> Mage::getSingleton("core/resource")->getTableName('sales_flat_shipment')), 'main_table.order_id = sales_shipment.order_id', array('main_table.warehouse_name', 'main_table.product_id', 'SUM(main_table.qty_shipped) as total_shipped', 'sales_shipment.created_at'))
            ->join(array('flat' => Mage::getSingleton("core/resource")->getTableName('catalog_product_entity_varchar')), 
                'main_table.product_id = flat.entity_id AND flat.attribute_id=' . $prodNameAttrId, 
                array('flat.value as name'))
            ->where('main_table.warehouse_id = '.$requestData['warehouse_select'].' and sales_shipment.created_at BETWEEN "'.$gettime['date_from'].'" and "'.$gettime['date_to'].'"')
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
            $this->addColumn('name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'name',
                'filter_index'  =>  'flat.value',
//                'filter_condition_callback' => array($this, '_filterProductNameCallback'),
            ));
            $this->addColumn('total_shipped', array(
                'header' => Mage::helper('inventoryreports')->__('Qty Shipped'),
                'align' => 'right',
                'index' => 'total_shipped',
                'type' => 'number',
                'width' => '100px',
                'filter_index'  =>  'main_table.qty_shipped',
            ));
        }else{  // All Warehouses
            $this->addColumn('warehouse_name', array(
                'header' => Mage::helper('inventoryreports')->__('Warehouse Name'),
                'align' => 'left',
                'index' => 'warehouse_name'
            ));
            $this->addColumn('total_shipped', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Shipped'),
                'align' => 'right',
                'index' => 'total_shipped',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalShippedCallback'),
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
        return $this->getUrl('*/*/salesbywarehouseitemshippedgrid',array('top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalShippedCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(main_table.qty_shipped) <= ?', $filter['to']);
        }
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(main_table.qty_shipped) >= ?', $filter['from']);
        }        
        return $this;
    }
    
    public function _filterProductNameCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter)) {
            $collection->getSelect()->where('`flat`.`name` like?', '%'.$filter.'%');
        }                
        return $this;
    }


}