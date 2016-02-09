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
class Magestore_Inventoryreports_Block_Adminhtml_Supplier_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {                
        $collection = Mage::getResourceModel('inventoryreports/product_collection')
                            ->addAttributeToSelect('type_id')
                            ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                            ->addAttributeToSelect('sku')                            
                            ->addAttributeToSelect('name')
                            ->addAttributeToSelect('status')
                            ->addAttributeToSelect('price')
                            ->addAttributeToSelect('attribute_set_id');        
        $collection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'inner');
        $collection->getSelect()
                ->joinLeft(
                    array('supplier_product' => $collection->getTable('inventorypurchasing/supplier_product')), 'e.entity_id=supplier_product.product_id', array('supplier_id')
                )
                ->group(array('e.entity_id'));
        $store = $this->_getStore();
        $collection->setIsGroupCountSql(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'align' => 'right',
            'sortable' => true,
            'width' => '60',
            'type' => 'number',
            'index' => 'entity_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $store = $this->_getStore();
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sku'
        ));
        $this->addColumn('product_price', array(
            'header' => Mage::helper('catalog')->__('Price'),
            'align' => 'right',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('catalog')->__('Total Qty'),
            'align' => 'right',
            'index' => 'qty',
            'type' => 'number'
        ));

        $currency_code = Mage::app()->getStore()->getBaseCurrency()->getCode();

        $this->addColumn('supplier_id', array(
            'header' => Mage::helper('catalog')->__('Supplier'),
            'type' => 'options',
            'options' => Mage::helper('inventoryreports')->getAllSupplierName(),
            'renderer' => 'inventoryreports/adminhtml_supplier_product_renderer_supplier',
            'align' => 'left',
            'index' => 'supplier_id',     
            'width' => '100px',
            'filter_condition_callback' => array($this, 'filterCallbackSupplier'),
        ));

        $this->addColumn('warehouse_inventory', array(
            'header' => Mage::helper('inventoryreports')->__('Warehouse'),
            'align' => 'left',
            'index' => 'warehouse_inventory',
            'type' => 'options',
            'options' => Mage::helper('inventoryreports')->getAllWarehouseName(),
            'renderer' => 'inventoryreports/adminhtml_supplier_product_renderer_warehouse',
            'filter_condition_callback' => array($this, '_filterWarehouseInventoryCallback'),
            'width' => '100px',
        ));        
        $this->addColumn('time_inventory', array(
            'header' => Mage::helper('inventoryreports')->__('Time Inventory(days)'),
            'align' => 'right',
            'index' => 'time_inventory',
            'type' => 'number',
            'renderer' => 'inventoryreports/adminhtml_supplier_product_renderer_time',
            'filter_condition_callback' => array($this, '_filterTimeInventoryCallback'),
            'width' => '100px',
        ));
        
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
        return $this->getUrl('*/*/productgrid');
    }

    public function filterCallbackSupplier($collection, $column) {        
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->getSelect()->where('supplier_product.supplier_id like ?', '%' . $value . '%');
        }
        return $this;
    }
    
    protected function _filterTimeInventoryCallback($collection, $column) {
       $collectionClone = Mage::getResourceModel('inventoryreports/product_collection')
                            ->addAttributeToSelect('type_id')
                            ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                            ->addAttributeToSelect('sku')                            
                            ->addAttributeToSelect('name')
                            ->addAttributeToSelect('status')
                            ->addAttributeToSelect('price')
                            ->addAttributeToSelect('attribute_set_id');        
        $collectionClone->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'inner');
        $collectionClone->getSelect()
                ->joinLeft(
                    array('supplier_product' => $collection->getTable('inventorypurchasing/supplier_product')), 'e.entity_id=supplier_product.product_id', array('supplier_id')
                )
                ->group(array('e.entity_id'));
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        $i=1;
        foreach ($collectionClone as $item) {
            $time = Mage::helper('inventoryreports')->getTimeInventory($item, $filterData);            
            $pass = TRUE;
            if(!$time){
                $pass = FALSE;
                continue;
            }
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($time) < floatval($filter['from'])) {
                    $pass = FALSE;
                    continue;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($time) > floatval($filter['to'])) {
                        $pass = FALSE;
                        continue;
                    }
                }
            }
            if ($pass) {                
                $arr[] = $item->getEntityId();
            }
        }        
        $collection->addFieldToFilter('entity_id',array('in'=>$arr));
    }
    
    protected function _filterWarehouseInventoryCallback($collection, $column) {    
        $filter = $column->getFilter()->getValue();
        if($filter)
            $collection->getSelect()
                    ->join(
                        array('barcode_product' => $collection->getTable('inventorybarcode/barcode')), 'e.entity_id=barcode_product.product_entity_id and warehouse_warehouse_id = '.$filter.' and barcode_product.qty > '. 0, array()
                    )
                    ->group(array('e.entity_id'));        
    }
    
    
    public function _sortTimeInventoryCallBack($collection, $column) {           
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));   
    }

}