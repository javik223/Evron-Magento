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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Purchaseorder_Grid extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('reportcontent_purchaseorder');
        $this->setDefaultSort('total_value');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setCountTotals(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $requestData = $this->getRequestData();
        $collection = Mage::helper('inventoryreports/purchaseorder')->getCollection($requestData);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $requestData = $this->getRequestData();

        $this->_addFirstColumn();

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('inventoryreports')->__('Purchased Qty'),
            'align' => 'right',
            'index' => 'total_qty',
            'type' => 'number',
            'width' => '100px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));
        /*
        if($this->isPOSKUReport()) {
            $this->addColumn('child_product_qty', array(
                'header' => Mage::helper('inventoryreports')->__('Purchased Qty<br/>per child products'),
                'align' => 'right',
                'index' => 'child_product_qty',
                'width' => '120px',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'inventoryreports/adminhtml_reportcontent_purchaseorder_renderer_childproductqty'
            ));              
        }
        */
        $this->addColumn('total_order', array(
            'header' => Mage::helper('inventoryreports')->__('Total Order'),
            'align' => 'right',
            'index' => 'total_order',
            'type' => 'number',
            'width' => '100px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('total_value', array(
            'header' => Mage::helper('inventoryreports')->__('Total Value'),
            'align' => 'right',
            'index' => 'total_value',
            'type' => 'currency',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            'width' => '100px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('sales_ratio', array(
            'header' => Mage::helper('inventoryreports')->__('#Ratio'),
            'align' => 'right',
            'index' => 'sales_ratio',
            'filter' => false,
            'sortable' => false,
            'width' => '50px',
            'renderer' => 'inventoryreports/adminhtml_reportcontent_purchaseorder_renderer_salesratio'
        ));

        $this->_addWarehouseCoulmn('inventoryreports/adminhtml_reportcontent_purchaseorder_renderer_warehouse');

        $this->addColumn('action', array(
            'header' => Mage::helper('inventoryreports')->__('Action'),
            'width' => '120px',
            'align' => 'center',
            'type' => 'action',
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            'renderer' => 'inventoryreports/adminhtml_reportcontent_purchaseorder_renderer_action'
        ));

        $this->addCSVExport();

        return parent::_prepareColumns();
    }

    /**
     * Add first column to grid
     * 
     */
    protected function _addFirstColumn() {
        $report = $this->getReport();
        switch ($report) {
            case 'po_supplier':
                $this->addColumn('supplier_name', array(
                    'header' => Mage::helper('inventoryreports')->__('Supplier'),
                    'align' => 'left',
                    'index' => 'supplier_name',
                    'width' => '100px',
                ));
                break;
            case'po_warehouse':
                $this->addColumn('warehouse_name', array(
                    'header' => Mage::helper('inventoryreports')->__('Warehouse'),
                    'align' => 'left',
                    'index' => 'warehouse_name',
                    'width' => '100px',
                    'filter_condition_callback' => array($this, '_filterTextCallback'),
                ));
                break;
            case 'po_sku':
                $this->addColumn('sku', array(
                    'header' => Mage::helper('inventoryreports')->__('SKU'),
                    'align' => 'left',
                    'index' => 'sku',
                    'width' => '100px',
                ));
                break;
        }
    }

    /**
     * Get data totals
     * 
     * @return \Varien_Object
     */
    public function getTotals() {
        $totals = new Varien_Object();
        $totalsData = array(
            'total_qty' => 0,
            'total_order' => 0,
            'total_value' => 0,
        );
        foreach ($this->getCollection() as $item) {
            foreach ($totalsData as $field => $value) {
                $totalsData[$field] += (float) $item->getData($field);
            }
        }
        
        $this->_addWHTotalQty($totalsData);
        
        //First column in the grid
        $totalsData['supplier_name'] = $this->__('Totals');
        $totalsData['warehouse_name'] = $this->__('Totals');
        $totalsData['sku'] = $this->__('Totals');
        $totalsData['action'] = $this->__('Totals');
        //calculate ratio
        $renderer = $this->getLayout()->createBlock('inventoryreports/adminhtml_reportcontent_purchaseorder_renderer_salesratio');
        $totalsData['sales_ratio'] = 0;
        if($renderer->getTotalData()){
            $totalsData['sales_ratio'] = round($totalsData['total_value'] / $renderer->getTotalData() * 100, 2);
        }
        $totals->setData($totalsData);
        return $totals;
    }

    /**
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
            $POIDs = array();
            $purchaseQty = 0;
            foreach ($this->getCollection() as $row) {
                $productIds = array_merge($productIds, explode(',', $row->getData('all_child_product_id')));
                $POIDs = array_merge($POIDs, explode(',', $row->getAllPurchaseOrderId()));
            }
            if ($this->isPOSKUReport()) {
                $purchaseQty = $this->helper('inventoryreports/purchaseorder')
                        ->getWarehousePOQtyByProduct($POIDs, $productIds, $warehouseId);
            }
            if ($this->isPOSupplierReport()) {
                $purchaseQty = $this->helper('inventoryreports/purchaseorder')
                        ->getWarehousePOQty($POIDs, $warehouseId);
            }
            $totalsData['warehouse_qty_' . $warehouseId] = $purchaseQty;
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
        return $this->getUrl('*/*/purchaseordergrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    protected function _getRealFieldFromAlias($alias) {
        $field = null;
        $report = $this->getReport();
        switch ($alias) {
            case 'total_order':
                $field = 'COUNT(DISTINCT main_table.purchase_order_id)';
                break;
            case 'total_value':
                $field = 'IFNULL(SUM(main_table.total_amount),0)';
                $field = $this->isPOWarehouseReport() ? 'SUM( (main_table.qty_received - main_table.qty_returned)* IFNULL(POP.cost,0) '
                        . ' * (100 + IFNULL(POP.tax,0) - IFNULL(POP.discount,0)) )/100' : $field;
                $field = $this->isPOSKUReport() ? 'SUM( (main_table.qty_recieved - main_table.qty_returned)* IFNULL(main_table.cost,0) '
                        . '* (100 + IFNULL(main_table.tax,0) - IFNULL(main_table.discount,0)) / 100)' : $field;
                break;
            case 'total_qty':
                $field = 'IFNULL(SUM(main_table.total_products),0)';
                $field = $this->isPOWarehouseReport() ? 'IFNULL(SUM(main_table.qty_received),0)' : $field;
                $field = $this->isPOSKUReport() ? 'IFNULL(SUM(main_table.qty_recieved),0)' : $field;
                break;
            case 'warehouse_name':
                $field = 'warehouse.warehouse_name';
                break;
        }
        return $field;
    }

}
