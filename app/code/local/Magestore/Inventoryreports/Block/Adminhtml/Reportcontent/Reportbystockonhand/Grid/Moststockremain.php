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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbystockonhand_Grid_Moststockremain extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid {

    protected $_requestData = null;
    protected $_filter = null;

    public function __construct() {
        parent::__construct();
        $this->setId('moststockremainGrid');
        $this->setDefaultSort('total_remain');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        //show total row
        $this->setCountTotals(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Prepare data collection
     * 
     */
    protected function _prepareCollection() {
        $requestData = $this->getRequestData();
        $collection = Mage::helper('inventoryreports/stockonhand')->getStockRemainingCollection($requestData);
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

        $this->addColumn('sku', array(
            'header' => Mage::helper('inventoryreports')->__('SKU'),
            'align' => 'left',
            'index' => 'sku',
            'width' => '100px',
            'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbystockonhand_renderer_supersku'
        ));

        $this->addColumn('total_remain', array(
            'header' => Mage::helper('inventoryreports')->__('On-hand Qty'),
            'align' => 'right',
            'index' => 'total_remain',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));
        
        /*
        $this->addColumn('child_product_qty', array(
            'header' => Mage::helper('inventoryreports')->__('On-hand Qty<br/>per child products'),
            'align' => 'right',
            'index' => 'child_product_qty',
            'width' => '150px',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbystockonhand_renderer_childproductqty'
        ));
        */
        
        $this->addColumn('sales_ratio', array(
            'header' => Mage::helper('inventoryreports')->__('#Ratio'),
            'align' => 'right',
            'index' => 'sales_ratio',
            'filter' => false,
            'sortable' => false,
            'width' => '100px',
            'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbystockonhand_renderer_qtyremainingratio'
        ));

        $this->_addWarehouseCoulmn('inventoryreports/adminhtml_reportcontent_reportbystockonhand_renderer_warehouse');


        $this->addColumn('action', array(
            'header' => Mage::helper('inventoryreports')->__('Action'),
            'width' => '50px',
            'align' => 'center',
            'type' => 'action',
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            'renderer' => 'inventoryreports/adminhtml_reportcontent_reportbystockonhand_renderer_action'
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
            'total_remain' => 0,
        );
        foreach ($this->getCollection() as $item) {
            foreach ($totalsData as $field => $value) {
                $totalsData[$field] += $item->getData($field);
            }
        }

        $this->_addWHTotalQty($totalsData);

        //First column in the grid
        $totalsData['sku'] = $this->__('Totals');
        $totalsData['action'] = $this->__('Totals');
        //calculate ratio
        $renderer = $this->getLayout()->createBlock('inventoryreports/adminhtml_reportcontent_reportbystockonhand_renderer_qtyremainingratio');
        $totalValue = $renderer->getTotalData();
        $totalsData['sales_ratio'] = 0;
        if($totalValue)
            $totalsData['sales_ratio'] = round($totalsData['total_remain'] / $totalValue * 100, 2);
        $totals->setData($totalsData);
        return $totals;
    }

    /**
     * Calculate total product qty in each warehouse
     * 
     * @param array $totalsData
     * @return array
     */
    protected function _addWHTotalQty(&$totalsData) {
        $warehouses = $this->getData('warehouses');
        if (!count($warehouses))
            return $totalsData;
        foreach ($warehouses as $warehouseId => $warehouseName) {
            $productIds = array();
            foreach ($this->getCollection() as $row) {
                
                $productId = $row->getData('entity_id');
                $productIds[$productId] = $productId;
                $superProduct = $this->helper('inventoryreports/stockonhand')
                                ->getSuperProduct($productId);
                if ($superProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $childProductIds = $superProduct->getTypeInstance()->getUsedProductIds();
                    $productIds = array_merge($productIds, $childProductIds);                 
                }
            }
            $qty = $this->helper('inventoryplus/warehouse')->getTotalQty($productIds, $warehouseId);
            $totalsData['warehouse_qty_' . $warehouseId] = $qty;
        }
        return $totalsData;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return false;
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/stockonhandmoststockremaingrid', array('type_id' => 'stockonhand', 'top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    /**
     * Get real filed from alias in sql
     * 
     * @param string $alias
     * @return string
     */
    protected function _getRealFieldFromAlias($alias) {
        $field = null;
        switch ($alias) {
            case 'total_remain':
                $field = 'IFNULL(SUM(warehouseproduct.total_qty),0)';
                break;
            default :
                $field = $alias;
        }
        if($field != $alias){
            $field = new Zend_Db_Expr($field);
        }          
        return $field;
    }

}
