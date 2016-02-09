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
 * Inventorysupplyneeds Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Salesrate_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('inventorysalesrateGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');        
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        if (!$this->getFilterData())
            $this->setFilterData(new Varien_Object());
    }

    protected function _prepareLayout() {
        $this->setChild('export_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Export'),
                            'onclick' => 'exportCsv()',
                            'class' => 'task'
                        ))
        );

        $this->setChild('reset_filter_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Reset Filter'),
                            'onclick' => $this->getJsObjectName() . '.resetFilter()',
                        ))
        );
        $this->setChild('search_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Search'),
                            'onclick' => $this->getJsObjectName() . '.doFilter()',
                            'class' => 'task'
                        ))
        );
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid
     */
    protected function _prepareCollection() {
        //process data
        $requestData = Mage::helper('adminhtml')->prepareFilterString(Mage::app()->getRequest()->getParam('top_filter'));
        $warehouse = $datefrom = $dateto = '';

        if ($requestData && isset($requestData['warehouse_select']))
            $warehouse = $requestData['warehouse_select'];
        if ($requestData && isset($requestData['date_from'])){
            $datefrom = date("Y-m-d", strtotime($requestData['date_from']));
        }
        if (!$datefrom) {
            $now = now();            
            $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if ($requestData && isset($requestData['date_to'])){
            $dateto = date("Y-m-d", strtotime($requestData['date_to']));
        }   
        if (!$dateto) {
            $now = now();            
            $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if($datefrom)
            $datefrom = $datefrom . ' 00:00:00';
        if($dateto)
            $dateto = $dateto . ' 23:59:59';
        //prepare collection
        $collection = Mage::getResourceModel('inventorywarehouse/product_collection')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id');
        if($warehouse){
            $collection->joinField('qty_warehouse', 'inventoryplus/warehouse_product', 'available_qty', 'product_id=entity_id', '{{table}}.warehouse_id='.$warehouse, 'inner');
        } else {
            $collection->joinField('qty_warehouse', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        }
        //get order id
        $orderIds = Mage::helper('inventorywarehouse/salesrate')->getOrderInPeriod($datefrom, $dateto);
        if(count($orderIds)>0){
            $stringOrderIds = '(';
            $i = 0;
            foreach($orderIds as $orderId){
                if($i == 0){
                    $stringOrderIds .= "'".$orderId."'";
                } else{
                    $stringOrderIds .= ','."'".$orderId."'";
                }
                $i++;
            }
            $stringOrderIds .= ')';
        } else {
            $stringOrderIds = '(0)';
        }
        $collection->joinField('qty_ordered', 'sales/order_item', 'SUM(at_qty_ordered.qty_ordered)', 'product_id=entity_id', '{{table}}.order_id IN '.$stringOrderIds, 'left');
        $collection->getSelect()->group('e.entity_id');
        $collection->setIsGroupCountSql(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function addExportType($url, $label) {
        $this->_exportTypes[] = new Varien_Object(
                        array(
                            'url' => $this->getUrl($url, array(
                                '_current' => true,
                                'filter' => $this->getParam($this->getVarNameFilter(), null)
                                    )
                            ),
                            'label' => $label
                        )
        );
        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '30px',
            'index' => 'entity_id',
            'type' => 'number',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'name',
            'width' => '280px',
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'index' => 'product_image',
            'filter' => false
        ));
        
        $this->addColumn('qty_warehouse', array(
            'header' => Mage::helper('catalog')->__('Total Avail. Qty in warehouse'),
            'width' => '80px',
            'index' => 'qty_warehouse',
            'type' => 'number',
            'align' => 'right',
            'filter_index' => 'qty_warehouse',
        ));

        $this->addColumn('qty_ordered', array(
            'header' => Mage::helper('catalog')->__('Total Ordered Qty in period'),
            'width' => '80px',
            'index' => 'qty_ordered',
            'type' => 'number',
            'default' => '0',
            'align' => 'right',
            'filter_condition_callback' => array($this, 'filterQtyOrdered'),
        ));

        $this->addColumn('sales_rate', array(
            'header' => Mage::helper('catalog')->__('Sales Rate'),
            'width' => '80px',
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
            'align' => 'right',
            'renderer' => 'inventorywarehouse/adminhtml_salesrate_renderer_salesrate'
        ));

        
        $this->addExportType('/*/*/', Mage::helper('inventorywarehouse')->__('CSV'));
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        
    }

    protected function _setCollectionOrder($column) {
        $filterData = $this->getFilterData();
        $collection = $this->getCollection();
        if ($collection) {
            switch ($column->getId()) {
                default:
                    $filter = $column->getIndex();
                    if ($column->getFilterIndex())
                        $filter = $column->getFilterIndex();
                    if ($column->getDir() == 'asc') {
                        $collection->setOrder($filter, 'ASC');
                    } else {
                        $collection->setOrder($filter, 'DESC');
                    }
                    break;
            }
        }
    }

    protected function filter_custom_column_callback($collection, $column) {
        return $this;
    }

    protected function filter_product_name_callback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->getSelect()->where('main_table.product_name like ?', '%' . $value . '%');
        }
        return $this;
    }
    protected function filterQtyOrdered($collection, $column) {
        $from = $column->getFilter()->getValue('from');
        $to = $column->getFilter()->getValue('to');
        if($from){
            if($warehouse){
                $collection->getSelect()->where("SUM(at_qty_ordered.qty_ordered) >= $from");
            }
        }
        if($to){
            if($warehouse){
                $collection->getSelect()->where("SUM(at_qty_ordered.qty_ordered) <= $to");
            }
        }
        return $this;
    }
    protected function filter_custom_qty_callback($collection, $column) {
        if($column->getIndex() == 'qty_warehouse'){
//            echo $collection->getSelect();die();
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
            $warehouse = '';
            if ($requestData && isset($requestData['warehouse_select']))
                $warehouse = $requestData['warehouse_select'];
            $from = $column->getFilter()->getValue('from');
            $to = $column->getFilter()->getValue('to');
            if($from){
                if($warehouse){
                    $collection->getSelect()->where("`warehouse_product`.`available_qty` >= $from");
                }else{
                    $collection->getSelect()->where("catalog_inventory.qty >= $from");                    
                }
            }
            if($to){
                if($warehouse){
                    $collection->getSelect()->where("`warehouse_product`.`available_qty` <= $to");
                }else{
                    $collection->getSelect()->where("catalog_inventory.qty <= $to");
                }
            }
        }
        return $this;
    }
}