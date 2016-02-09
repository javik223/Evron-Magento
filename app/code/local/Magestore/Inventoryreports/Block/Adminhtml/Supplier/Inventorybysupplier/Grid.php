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
class Magestore_Inventoryreports_Block_Adminhtml_Supplier_Inventorybysupplier_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('inventorysupplierGrid');
        $this->setDefaultSort('supplier_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        if($supplierId = $this->getRequest()->getParam('supplier_id')){
            $productAttribute = $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');
            $resource = Mage::getSingleton('core/resource');
            $collection = Mage::getModel('inventorypurchasing/supplier_product')
                                ->getCollection()
                                ->addFieldToFilter('supplier_id',$supplierId);
            $collection->getSelect()
                       ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.product_id=barcode.product_entity_id and barcode.supplier_supplier_id = '.$supplierId, 
                                    array('total_inventory' => 'sum(barcode.qty)')
                                )
                       ->joinLeft(array('product_attribute' => $resource->getTableName('catalog_product_entity_'.$productAttribute->getData('backend_type'))), 'main_table.product_id=product_attribute.entity_id and product_attribute.attribute_id = '.$productAttribute->getData('attribute_id'), 
                                    array('product_name' => 'product_attribute.value')
                                )
                       ->group(array('main_table.product_id')); 
            $collection->setIsGroupCountSql(true);
        }else{
            $collection = Mage::getModel('inventorypurchasing/supplier')
                            ->getCollection();
            $collection->getSelect()
                        ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.supplier_id=barcode.supplier_supplier_id', 
                                    array('total_inventory' => 'sum(barcode.qty)')
                                )
                        ->group('main_table.supplier_id');            
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
        if($supplierId = $this->getRequest()->getParam('supplier_id')){
            $this->addColumn('product_name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'product_name',
                'filter_condition_callback' => array($this, '_filterProductNameCallback'),
            ));
            $this->addColumn('total_inventory', array(
                'header' => Mage::helper('inventoryreports')->__('Inventory by Supplier'),
                'align' => 'right',
                'index' => 'total_inventory',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalInventoryCallback'),
                'renderer' => 'inventoryreports/adminhtml_supplier_inventorybysupplier_renderer_inventory'
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
                'renderer' => 'inventoryreports/adminhtml_supplier_inventorybysupplier_renderer_inventory'
            ));
        }
        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryreports')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventoryreports')->__('XML'));

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
        return $this->getUrl('*/*/inventorybysuppliergrid',array('supplier_id'=>$this->getRequest()->getParam('supplier_id')));
    }

    public function _filterTotalInventoryCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(barcode.qty) >= ?', $filter['from']);
        }        
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(barcode.qty) <= ?', $filter['to']);
        }        
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