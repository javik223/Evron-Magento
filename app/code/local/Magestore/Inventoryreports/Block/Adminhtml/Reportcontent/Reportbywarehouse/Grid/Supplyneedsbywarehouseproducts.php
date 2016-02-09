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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Supplyneedsbywarehouseproducts extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('supplyneedsbywarehouseproductsgrid');
        $this->setDefaultSort('warehouse_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $warehouse = $requestData['warehouse_select'];
        $gettime = Mage::helper('inventoryreports')->getTimeSelected($requestData);
        $datefrom = $gettime['date_from'];
        $dateto = $gettime['date_to'];
        if(empty($requestData)){
            $requestData = Mage::helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        if($warehouse == 0){
            $collection = Mage::getModel('inventoryplus/warehouse')->getCollection();
        }
        if($warehouse > 0){
            $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse);
            $supplyneeds = array();
            foreach($warehouse_product as $value){
                $product_id = $value->getProductId();
                $method = Mage::getStoreConfig('inventory/supplyneed/supplyneeds_method');             
                if ($datefrom && $dateto && $method == 2 && (strtotime($datefrom) <= strtotime($dateto))) {            
                    $max_needs = Mage::helper('inventorysupplyneeds')->calMaxAverage($product_id, $datefrom, $dateto, $warehouse);
                } elseif ($datefrom && $dateto && $method == 1 && strtotime($datefrom) <= strtotime($dateto)) {
                    $max_needs = Mage::helper('inventorysupplyneeds')->calMaxExponential($product_id, $datefrom, $dateto, $warehouse);
                } else {
                    $max_needs = 0;
                }
                if($max_needs > 0){
                    $supplyneeds[$product_id] = $max_needs;
                }
            }
            $ids = array();
            foreach($supplyneeds as $key => $value){
                $ids[] = $key;
            }
            $collection = Mage::getResourceModel('catalog/product_collection')
                ->addFieldToFilter('entity_id', array('in' => $ids))
                ->addAttributeToSelect('name');
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
        if(empty($requestData)){
            $requestData = Mage::helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        if($requestData['warehouse_select'] == 0){
            $this->addColumn('warehouse_name', array(
                'header'    => Mage::helper('inventoryreports')->__('Warehouse Name'),
                'align'     => 'left',
                'index'     => 'warehouse_name',
            ));
            $this->addColumn('supplyneeds', array(
                'header'    => Mage::helper('inventoryreports')->__('Total Qty. Needed Purchasing'),
                'align'     => 'right',
                'index'     => 'supplyneeds',
                'type'      => 'number',
                'width'     => '100px',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'inventoryreports/adminhtml_reportcontent_reportbywarehouse_renderer_supplyneeds_allwarehouses'
            ));
        }else{
            $this->addColumn('name', array(
                'header'    => Mage::helper('inventoryreports')->__('Product Name'),
                'align'     => 'left',
                'index'     => 'name'
            ));
            $this->addColumn('supplyneeds', array(
                'header'    => Mage::helper('inventoryreports')->__('Total Qty. Needed Purchasing'),
                'align'     => 'right',
                'index'     => 'supplyneeds',
                'type'      => 'number',
                'width'     => '100px',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'inventoryreports/adminhtml_reportcontent_reportbywarehouse_renderer_supplyneeds_warehouseselected'
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
        return $this->getUrl('*/*/supplyneedsbywarehouseproductsgrid',array('top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

}