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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Totalorderbywarehouse extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('totalorderbywarehousegrid');
        $this->setDefaultSort('total_order');
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
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        if(!$requestData['warehouse_select']){  //All warehouses
            $installer = Mage::getModel('core/resource');
            $collection = Mage::getModel('inventoryplus/warehouse_shipment')->getCollection();
            $collection->getSelect()
            ->joinLeft( array('order'=>$installer->getTableName('sales_flat_order')), 'main_table.order_id = order.entity_id', array('count(distinct main_table.order_id) as total_order', 'main_table.warehouse_name'))
            ->where('order.created_at BETWEEN "'.$gettime['date_from'].'" and "'.$gettime['date_to'].'" AND main_table.warehouse_id > 0 ')
            ->group('main_table.warehouse_id')
            ;

        }
        else{   //Warehouse selected
            $installer = Mage::getModel('core/resource');
            $collection = Mage::getModel('inventoryplus/warehouse_shipment')->getCollection();      
            $collection->getSelect()->distinct(true)
            ->joinLeft( array('order'=>$installer->getTableName('sales_flat_order')), 'main_table.order_id = order.entity_id', array('order.increment_id','order.created_at','order.base_grand_total','order.grand_total'))
            ->joinLeft(array('item' => $installer->getTableName('sales_flat_order_item')), 'order.entity_id = item.order_id', array('sum(item.qty_ordered) as qty_order', 'sum(item.qty_shipped) as qty_ship', 'sum(item.qty_refunded) as qty_refund'))
            ->where('main_table.warehouse_id = '.$requestData['warehouse_select'].' AND order.created_at BETWEEN "'.$gettime['date_from'].'" and "'.$gettime['date_to'].'"')
            ->group('main_table.order_id')
            ;
        }
        //Zend_Debug::Dump($collection->getSelect()->__toString());die();
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
        $warehouseId = $requestData['warehouse_select'];
        if(!$warehouseId){
            $this->addColumn('warehouse_name', array(
                'header' => Mage::helper('inventoryreports')->__('Warehouse Name'),
                'align' => 'left',
                'index' => 'warehouse_name',
            ));
            $this->addColumn('total_order', array(
                'header' => Mage::helper('inventoryreports')->__('No. of Orders '),
                'align' => 'right',
                'index' => 'total_order',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalOrderCallback'),
            ));
            
        }else{
            $this->addColumn('increment_id', array(
                'header' => Mage::helper('inventoryreports')->__('Order #'),
                'align' => 'right',
                'index' => 'increment_id',
                'type' => 'text',
                'width' => '50px',
            ));
            
            $this->addColumn('created_at', array(
                'header' => Mage::helper('inventoryreports')->__('Purchase On'),
                'align' => 'left',
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '350px',
            ));
            
            $this->addColumn('qty_order', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Ordered'),
                'align' => 'right',
                'index' => 'qty_order',
                'type' => 'number',
                'width' => '50px',
                'filter_condition_callback' => array($this, '_filterQtyOrderCallback'),
//                'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbywarehouse_renderer_qtyorder_ordered'
            ));
            
            $this->addColumn('qty_ship', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Shipped'),
                'align' => 'right',
                'index' => 'qty_ship',
                'type' => 'number',
                'width' => '50px',
                'filter_condition_callback' => array($this, '_filterQtyShipCallback'),
//                'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbywarehouse_renderer_qtyorder_shipped'
            ));
                        
            $this->addColumn('qty_refund', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Refunded'),
                'align' => 'right',
                'index' => 'qty_refund',
                'type' => 'number',
                'width' => '50px',
                'filter_condition_callback' => array($this, '_filterQtyRefundCallback'),
//                'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbywarehouse_renderer_qtyorder_refunded'
            ));
            
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('inventoryreports')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));
            
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('inventoryreports')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
            
        $this->addColumn('status', array(
            'header' => Mage::helper('inventoryreports')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
            
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('inventoryreports')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                    'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbywarehouse_renderer_order_url'
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
        return $this->getUrl('*/*/totalorderbywarehousegrid',array('top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalOrderCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('count(distinct main_table.order_id) >= ?', $filter['from']);
        }        
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('count(distinct main_table.order_id) <= ?', $filter['to']);
        }
        return $this;
    }
    
    public function _filterQtyOrderCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(item.qty_ordered) >= ?', $filter['from']);
        }        
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(item.qty_ordered) <= ?', $filter['to']);
        }
        return $this;
    }
    
    public function _filterQtyShipCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(item.qty_shipped) >= ?', $filter['from']);
        }        
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(item.qty_shipped) <= ?', $filter['to']);
        }
        return $this;
    }
    
    public function _filterQtyRefundCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(item.qty_refunded) >= ?', $filter['from']);
        }        
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(item.qty_refunded) <= ?', $filter['to']);
        }
        return $this;
    }    


}