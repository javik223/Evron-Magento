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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbysupplier_Grid_Supplier extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('inventorysupplierGrid');
        $this->setDefaultSort('total_inventory');
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
//        Zend_Debug::dump($requestData);
        $collection = Mage::helper('inventoryreports')->getSupplierReportCollection($requestData);
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
        if(empty($requestData)){$requestData = Mage::Helper('inventoryreports')->getDefaultOptionsSupplier();}
        if($supplierId = $requestData['supplier_select']){
            $this->addColumn('product_name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'product_name',
                'filter_condition_callback' => array($this, '_filterProductNameCallback'),
            ));
            $this->addColumn('total_inventory', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Purchased'),
                'align' => 'right',
                'index' => 'total_inventory',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalInventoryCallback'),
                'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbysupplier_renderer_inventory'
            ));
        }else{
            $this->addColumn('supplier_name', array(
                'header' => Mage::helper('inventoryreports')->__('Supplier name'),
                'align' => 'left',
                'index' => 'supplier_name'
            ));
            $this->addColumn('total_inventory', array(
                'header' => Mage::helper('inventoryreports')->__('Inventory by Supplier'),
                'align' => 'right',
                'index' => 'total_inventory',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalInventoryCallback'),
                'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbysupplier_renderer_inventory'
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
        return $this->getUrl('*/*/inventorybysuppliergrid',array('top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalInventoryCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(barcode.qty) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(barcode.qty) <= ?', $filter['to']);
        }
//        Zend_debug::dump($collection->getSelect()->__toString());
        return $this;
    }
    
    public function _filterProductNameCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter)) {
            $collection->getSelect()->where('product_attribute.value like?', '%'.$filter.'%');
        }                
        return $this;
    }


}