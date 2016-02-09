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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Salesbywarehouserevenue extends Mage_Adminhtml_Block_Widget_Grid {

    protected $is_warehouse;

    public function __construct() {
        parent::__construct();
        $this->setId('salesbywarehouserevenuegrid');
        $this->setDefaultSort('revenue');
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
        if (empty($requestData)) {
            $requestData = Mage::helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        if (!$requestData['warehouse_select']) {  //All Warehouses
            $this->is_warehouse = 0;
            $collection = Mage::getModel('inventoryplus/warehouse_shipment')->getCollection();
            $collection->getSelect()
                    ->join(array('order' => Mage::getSingleton("core/resource")->getTableName('sales_flat_order')), 'main_table.order_id = order.entity_id', array('main_table.warehouse_name', 'sum(main_table.subtotal_shipped) as revenue', 'order.created_at'))
                    ->where('main_table.qty_shipped > 0 AND order.created_at BETWEEN "' . $gettime['date_from'] . '" and "' . $gettime['date_to'] . '"')
                    ->group('main_table.warehouse_name')
            ;
        } else {   //  WAREHOUSE SELECTED
            $prodNameAttrId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','name');
            $collection = Mage::getModel('inventoryplus/warehouse_shipment')->getCollection();
            $collection->getSelect()
                    ->joinLeft(array('order' => Mage::getSingleton("core/resource")->getTableName('sales_flat_order')), 'main_table.order_id = order.entity_id', array('main_table.product_id', 'sum(main_table.qty_shipped) as total_ship', 'sum(main_table.subtotal_shipped) as revenue', 'order.created_at'))
                    ->joinLeft(array('flat' => Mage::getSingleton("core/resource")->getTableName('catalog_product_entity_varchar')), 'main_table.product_id = flat.entity_id AND flat.attribute_id=' . $prodNameAttrId, array('flat.value as name'))
                    ->where('main_table.warehouse_id = "' . $requestData['warehouse_select'] . '" AND main_table.qty_shipped > 0 AND order.created_at BETWEEN "' . $gettime['date_from'] . '" and "' . $gettime['date_to'] . '"')
                    ->group('main_table.product_id')
            ;
        }

        $this->setCollection($collection);
//        $this->_prepareTotals('total_ship,revenue');
        return parent::_prepareCollection();
    }

    protected function _prepareTotals($columns = null) {
        $columns = explode(',', $columns);
        if (!$columns) {
            return;
        }
        $this->_countTotals = true;
        $totals = new Varien_Object();
        $fields = array();
        foreach ($columns as $column) {
            $fields[$column] = 0;
        }
        foreach ($this->getCollection() as $item) {
            foreach ($fields as $field => $value) {
                $fields[$field]+=$item->getData($field);
            }
        }
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if ($this->is_warehouse == 0) {
            $fields['warehouse_name'] = 'Totals';
        } else {
            $fields['name'] = 'Totals';
        }

        $totals->setData($fields);
        $this->setTotals($totals);
        return;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        if ($requestData['warehouse_select']) {   //Warehouse Selected
            $this->addColumn('name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'name',
                'filter_condition_callback' => array($this, '_filterNameCallback')
            ));
            $this->addColumn('total_ship', array(
                'header' => Mage::helper('inventoryreports')->__('Qty Shipped'),
                'align' => 'right',
                'index' => 'total_ship',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalShipCallback'),
            ));

            $this->addColumn('revenue', array(
                'header' => Mage::helper('inventoryreports')->__('Total Revenue'),
                'align' => 'right',
                'index' => 'revenue',
                'type' => 'currency',
                'total' => 'sum',
                'width' => '100px',
                'currency_code' => $currencyCode,
                'filter_condition_callback' => array($this, '_filterTotalRevenueCallback'),
            ));
        } else {  // All Warehouses
            $this->addColumn('warehouse_name', array(
                'header' => Mage::helper('inventoryreports')->__('Warehouse Name'),
                'align' => 'left',
                'index' => 'warehouse_name'
            ));
            $this->addColumn('revenue', array(
                'header' => Mage::helper('inventoryreports')->__('Total Revenue'),
                'align' => 'right',
                'index' => 'revenue',
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterTotalRevenueCallback'),
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
        return $this->getUrl('adminhtml/inr_report/salesbywarehouserevenuegrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    public function _filterNameCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        foreach ($collection as $item) {
            $fieldValue = $item->getData($column->getId());
            $pass = TRUE;
            if (!is_array($filter)) {
                if (strpos(strtolower($fieldValue), strtolower($filter)) == false) {
                    $pass = FALSE;
                }
            }
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($fieldValue) < floatval($filter['from'])) {
                    $pass = FALSE;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($fieldValue) > floatval($filter['to'])) {
                        $pass = FALSE;
                    }
                }
            }
            if ($pass) {
                $item->setData($column->getId(), $fieldValue);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
        for ($i = 0; $i < count($arr); $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

    public function _filterTotalRevenueCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(main_table.subtotal_shipped) <= ?', $filter['to']);
        }

        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(main_table.subtotal_shipped) >= ?', $filter['from']);
        }
        $filterCollection = new Varien_Data_Collection();
        foreach ($collection as $c) {
            $filterCollection->addItem($c);
        }
        $this->setCollection($filterCollection);
    }

    public function _filterTotalShipCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having('sum(main_table.qty_shipped) <= ?', $filter['to']);
        }
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having('sum(main_table.qty_shipped) >= ?', $filter['from']);
        }
        $filterCollection = new Varien_Data_Collection();
        foreach ($collection as $c) {
            $filterCollection->addItem($c);
        }
        $this->setCollection($filterCollection);
    }

}
