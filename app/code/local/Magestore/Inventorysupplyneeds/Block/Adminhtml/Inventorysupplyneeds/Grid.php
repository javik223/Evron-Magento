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
 * Inventorysupplyneeds Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_helperClass = null;
    protected $_collectionGrid = null;

    public function __construct() {
        parent::__construct();
        $this->setId('inventorysupplyneedsGrid');
        $this->setDefaultSort('out_of_stock_date');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $helperClass = $this->getHelperClass();
        if (!$this->_collectionGrid)
            $this->_prepareCollectionInContruct($helperClass);
    }

    public function getCollectionGrid() {
        return $this->_collectionGrid;
    }

    public function sendHelperClass($send) {
        $this->_helperClass = $send; //Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds
    }

    protected function getHelperClass() {
        $helperClass = $this->_helperClass;
        if (!$helperClass) {
            $filter = $this->getRequest()->getParam('top_filter');
            $helperClass = Mage::helper('inventorysupplyneeds');
            $helperClass->setTopFilter($filter);
        }
        return $helperClass;
    }

    public function _prepareCollectionInContruct($helperClass) {
        try {
            $dateto = $helperClass->getForecastTo();
            $salesFromTo = $helperClass->getSalesFromTo();
            $listItemIds = $this->getListOrderItemIds($helperClass);
            $historySelected = $helperClass->getHistorySelected();
            $getNumberDaysForecast = $helperClass->getNumberDaysForecast();
            $purchase_more_rate = $helperClass->getRatePurchaseMore();
            $rate = $purchase_more_rate / 100;
            if (!$listItemIds) {
                $collection = new Varien_Data_Collection();
            } else {
                $w_productCol = $this->getWarehouseProductCollection($helperClass);
                $s_productCol = $this->getSupplierProductCollection($helperClass);
                $orderItemCol = $this->getOrderItemCollection($listItemIds, $helperClass);
                $poProductCol = $this->getPOProductCollection($helperClass);
                $coreResource = Mage::getSingleton('core/resource');
                $tempTableArr = array('supplier_temp_table', 'order_item_temp_table', 'purchase_order_product_temp');
                $this->removeTempTables($tempTableArr);
                $this->createTempTable('supplier_temp_table', $s_productCol);
                $this->createTempTable('order_item_temp_table', $orderItemCol);
                $this->createTempTable('purchase_order_product_temp', $poProductCol);
                $collection = $w_productCol;
                $collection->getSelect()
                        ->join(
                                array('supplier_product' => $coreResource->getTableName('supplier_temp_table')), "main_table.product_id=supplier_product.product_id", array('supplier_product.*'));
                $collection->getSelect()
                        ->join(
                                array('order_item' => $coreResource->getTableName('order_item_temp_table')), "main_table.product_id=order_item.product_id", array('order_item.*'));
                $collection->getSelect()
                        ->joinLeft(
                                array('tmp_po_product' => $coreResource->getTableName('purchase_order_product_temp')), "main_table.product_id=tmp_po_product.product_id", array('in_purchasing'));
                $collection->getSelect()->columns(array(
                    'out_of_stock_date' => new Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(SUM(main_table.available_qty)/order_item.avg_qty_ordered) DAY)"),
                    'supplyneeds' => new Zend_Db_Expr("GREATEST((order_item.avg_qty_ordered * {$getNumberDaysForecast} - SUM(main_table.available_qty)),0)"),
                    'purchase_more' => new Zend_Db_Expr("CEIL(GREATEST(((order_item.avg_qty_ordered * {$getNumberDaysForecast} - SUM(main_table.available_qty))* {$rate} - IFNULL(tmp_po_product.in_purchasing,0)),0))"),
                ));
            }
            $this->_collectionGrid = $collection;
        } catch (Exception $e) {
        }
    }

    protected function _prepareCollection() {
        $collection = $this->_collectionGrid;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->_getSelectedProducts(),
            'align' => 'center',
            'index' => 'product_id',
            'use_index' => true,
            'disabled_values' => array()
        ));
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('inventoryplus')->__('SKU'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'product_sku',
            'filter_condition_callback' => array($this, '_filterTextCallback')
        ));
        $this->addColumn('avg_qty_ordered', array(
            'header' => Mage::helper('inventoryplus')->__('Qty. Ordered/day'),
            'align' => 'right',
            'index' => 'avg_qty_ordered',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterNumberCallback')
        ));
        $this->addColumn('total_qty_ordered', array(
            'header' => Mage::helper('inventoryplus')->__('Total Sold'),
            'align' => 'right',
            'index' => 'total_qty_ordered',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterNumberCallback')
        ));
        $this->addColumn('total_available_qty', array(
            'header' => Mage::helper('inventoryplus')->__('Avail. Qty'), // Dang bi SAI
            'align' => 'right',
            'index' => 'total_available_qty',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterNumberCallback')
        ));
        $this->addColumn('in_purchasing', array(
            'header' => Mage::helper('inventoryplus')->__('Qty On Order'),
            'align' => 'right',
            'index' => 'in_purchasing',
            'type' => 'number',
        ));
        $this->addColumn('out_of_stock_date', array(
            'header' => Mage::helper('inventoryplus')->__('Out-of-stock Date'),
            'align' => 'left',
            'index' => 'out_of_stock_date',
            'type' => 'date',
            'filter_condition_callback' => array($this, '_filterDateCallback')
        ));
        $this->addColumn('supplyneeds', array(
            'header' => Mage::helper('inventoryplus')->__('Supply Needs'),
            'align' => 'right',
            'index' => 'supplyneeds',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterNumberCallback')
        ));
        if (!$this->_isExport) {
            $this->addColumn('purchase_more', array(
                'header' => Mage::helper('inventoryplus')->__('Purchase Qty'),
                'align' => 'right',
                'width' => '80px',
                'index' => 'purchase_more',
                'type' => 'input',
                'editable' => true,
                'sortable' => false,
                'filter' => false
            ));
        }
        if (!$this->_isExport && Mage::helper('core')->isModuleEnabled('Magestore_Inventoryreports')) {
            $this->addColumn('action', array(
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'align' => 'center',
                'type' => 'action',
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
                'renderer' => 'inventorysupplyneeds/adminhtml_inventorysupplyneeds_renderer_action'
            ));
        }
        $this->addExportType('*/*/exportCsv', Mage::helper('inventorysupplyneeds')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventorysupplyneeds')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        if ($filter = $this->getRequest()->getParam('top_filter'))
            return $this->getUrl('*/*/grid', array('_current' => true, 'top_filter' => $filter));
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return false;
    }

    protected function getListOrderItemIds($helperClass) {
        $coreResource = Mage::getSingleton('core/resource');
        $coreResource->getConnection('core_write')->query('SET SESSION group_concat_max_len = 1000000;');
        $salesFromTo = $helperClass->getSalesFromTo();
        $warehouseSelected = $helperClass->getWarehouseSelected();
        $warehousesEnable = Mage::helper('inventoryplus/warehouse')->getAllWarehouseNameEnable();
        if(count($warehouseSelected)==count($warehousesEnable)){
            $orderItems = Mage::getModel('sales/order_item')->getCollection();
            $conditionOne = "created_at > '{$salesFromTo['from']}' AND created_at < '{$salesFromTo['to']}' ";
            $orderItems->getSelect()->where($conditionOne);
            $orderItems->getSelect()->columns(array(
                    'all_item_id' => 'GROUP_CONCAT(DISTINCT item_id SEPARATOR ",")'));
            $itemIds = $orderItems->getFirstItem()->getAllItemId();	
        }else{	
            $warehouseSelectedStr = implode(',', $warehouseSelected);
            $conditionOne = "order_item.created_at > '{$salesFromTo['from']}' AND order_item.created_at < '{$salesFromTo['to']}' AND main_table.warehouse_id IN ({$warehouseSelectedStr})";
            $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection();
            $warehouseOrder->getSelect()
                            ->joinLeft(
                                            array('order_item' => $warehouseOrder->getTable('sales/order_item')), "main_table.item_id=order_item.item_id", array('item_id'));
            $warehouseOrder->getSelect()->where($conditionOne);
            $warehouseOrder->getSelect()->columns(array(
                    'all_item_id' => 'GROUP_CONCAT(DISTINCT main_table.item_id SEPARATOR ",")'));
            $itemIds = $warehouseOrder->getFirstItem()->getAllItemId();	
        }
        return $itemIds;
    }

    protected function getWarehouseProductCollection($helperClass) {
        $coreResource = Mage::getSingleton('core/resource');
        $warehouseSelected = $helperClass->getWarehouseSelected();
        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToSelect(array('product_id'));
        if (count($warehouseSelected) > 1)
            $collection->addFieldToFilter('warehouse_id', array('in' => $warehouseSelected));
        else
            $collection->addFieldToFilter('warehouse_id', $warehouseSelected[0]);
        $collection->getSelect()->columns(array(
            'total_available_qty' => 'SUM(available_qty)'
        ));
        $collection->getSelect()
                ->joinLeft(
                        array('catalog_product_entity'=>$coreResource->getTableName('catalog_product_entity')), "main_table.product_id = " . 'catalog_product_entity' . ".entity_id", array('product_sku' => 'sku'));
        $collection->getSelect()->group('main_table.product_id');
        return $collection;
    }

    protected function getSupplierProductCollection($helperClass) {
        $supplierSelected = $helperClass->getsupplierSelected();
        $collection = Mage::getModel('inventorypurchasing/supplier_product')->getCollection();
        $collection->addFieldToSelect('product_id');
        if (count($supplierSelected) > 1)
            $collection->addFieldToFilter('supplier_id', array('in' => $supplierSelected));
        else
            $collection->addFieldToFilter('supplier_id', $supplierSelected[0]);
        $collection->getSelect()->columns(array(
            'all_supplier_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT supplier_id SEPARATOR ",")')));
        $collection->getSelect()->group('product_id');
        return $collection;
    }

    protected function getNumberHoursFromTwoDate($from, $to) {
        $hours = round((strtotime($to) - strtotime($from)) / (60 * 60));
        return $hours;
    }

    protected function getNumberDaysFromTwoDate($from, $to) {
        $date1 = new DateTime($from);
        $date2 = new DateTime($to);
        $diff = $date2->diff($date1)->format("%a");
        return $diff;
    }

    protected function getNumberDaysInstock($helperClass) {
        $coreResource = Mage::getSingleton('core/resource');
        $salesFromTo = $helperClass->getSalesFromTo();
        $from = $salesFromTo['from'];
        $to = $salesFromTo['to'];
        $hours = $this->getNumberHoursFromTwoDate($from, $to);
        $wproducts = Mage::getResourceModel('catalog/product_collection');
        $wproducts->getSelect()
                ->join(
                    array('oos_history' => $coreResource->getTableName('erp_inventory_outofstock_tracking')),
                    "e.entity_id=oos_history.product_id",
                    array('outofstock_date',
                        'instock_date',
                        'number_days_instock' => new Zend_Db_Expr('(' . $hours . ' - SUM(TIMESTAMPDIFF(HOUR,GREATEST(outofstock_date,\'' . $from . '\'),IFNULL(instock_date,"' . $to . '"))))/24')
                    ));
        $wproducts->getSelect()->group('e.entity_id');
        return $wproducts;
    }

    protected function getOrderItemCollection($listItemIds, $helperClass) {
        $salesFromTo = $helperClass->getSalesFromTo();
        $coreResource = Mage::getSingleton('core/resource');
        $daysInStock = $this->getNumberDaysInstock($helperClass);
        $this->removeTempTables(array('days_instock_table'));
        $this->createTempTable('days_instock_table', $daysInStock);
        $days = $this->getNumberDaysFromTwoDate($salesFromTo['from'], $salesFromTo['to']);
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->addFieldToSelect('*');
        $collection->getSelect()->where("item_id IN ({$listItemIds})");
        $collection->getSelect()
                ->joinLeft(
                        array('days_instock_templ' => $coreResource->getTableName('days_instock_table')), "main_table.product_id=days_instock_templ.entity_id", array('number_days_instock'));
        $collection->getSelect()->columns(array(
            'total_qty_ordered' => new Zend_Db_Expr('SUM(qty_ordered)'),
            'avg_qty_ordered' => new Zend_Db_Expr("ROUND(SUM(qty_ordered)/IFNULL(number_days_instock,'$days'),2)")));
        $collection->getSelect()->group('main_table.product_id');
        return $collection;
    }

    protected function getPOProductCollection($helperClass) {
        $warehouseSelected = $helperClass->getWarehouseSelected();
        $supplierSelected = $helperClass->getsupplierSelected();
        $supplierSelectedStr = implode(',', $supplierSelected);
        $collection = Mage::getModel('inventorypurchasing/purchaseorder_productwarehouse')->getCollection();
        $collection->addFieldToSelect(array('product_id', 'purchase_order_id'));
        if (count($warehouseSelected) > 1)
            $collection->addFieldToFilter('main_table.warehouse_id', array('in' => $warehouseSelected));
        else
            $collection->addFieldToFilter('main_table.warehouse_id', $warehouseSelected[0]);
        $collection->getSelect()
                ->joinLeft(
                        array('purchase_order' => $collection->getTable('inventorypurchasing/purchaseorder')), "main_table.purchase_order_id=purchase_order.purchase_order_id", array('supplier_id'));
        if (count($supplierSelected) > 1)
            $collection->getSelect()->where("purchase_order.supplier_id IN ({$supplierSelectedStr})");
        else
            $collection->getSelect()->where("purchase_order.supplier_id = {$supplierSelectedStr}");
        $collection->getSelect()->columns(array(
            'in_purchasing' => new Zend_Db_Expr('IFNULL(SUM(`qty_order` - `qty_received` + `qty_returned`),0)')));
        $collection->getSelect()->group('product_id');
        return $collection;
    }

    protected function removeTempTables($tempTableArr) {
        $coreResource = Mage::getSingleton('core/resource');
        $sql = "";
        foreach ($tempTableArr as $tempTable) {
            $sql .= "DROP TABLE  IF EXISTS " . $coreResource->getTableName($tempTable) . ";";
        }
        $coreResource->getConnection('core_write')->query($sql);
        return;
    }

    protected function createTempTable($tempTable, $collection) {
        $coreResource = Mage::getSingleton('core/resource');
        $_temp_sql = "CREATE TEMPORARY TABLE " . $coreResource->getTableName($tempTable) . " ("; // CREATE TEMPORARY TABLE
        $_temp_sql .= $collection->getSelect()->__toString() . ");";
        $coreResource->getConnection('core_write')->query($_temp_sql);
        return;
    }

    /**
     * Filter text field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterTextCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        $collection->getSelect()->where($field . ' like \'%' . $filter . '%\'');
        return $collection;
    }

    /**
     * Filter number field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterNumberCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        if (isset($filter['from']) && $filter['from'] != '') {
            $collection->getSelect()->having($field . ' >= ' . $filter['from']);
        }
        if (isset($filter['to']) && $filter['to'] != '') {
            $collection->getSelect()->having($field . ' <= ' . $filter['to']);
        }
        $collection->setIsGroupCountSql(true);
        $collection->setResetHaving(true);
        return $collection;
    }

    protected function _filterDateCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        if (isset($filter['from']) && $filter['from'] != '') {
            $from = Mage::getModel('core/date')->date('Y-m-d 00:00:00', $filter['from']);
            $collection->getSelect()->having($field . ' >= \'' . $from . '\'');
        }
        if (isset($filter['to']) && $filter['to'] != '') {
            $to = Mage::getModel('core/date')->date('Y-m-d 23:59:59', $filter['to']);
            $collection->getSelect()->having($field . ' <= \'' . $to . '\'');
        }
        $collection->setIsGroupCountSql(true);
        $collection->setResetHaving(true);
        return $collection;
    }

    protected function _getRealFieldFromAlias($alias) {
        $helperClass = $this->_helperClass;
        if (!$helperClass) {
            $filter = $this->getRequest()->getParam('top_filter');
            $helperClass = Mage::helper('inventorysupplyneeds');
            $helperClass->setTopFilter($filter);
        }
        $salesFromTo = $helperClass->getSalesFromTo();
        $getNumberDaysForecast = $helperClass->getNumberDaysForecast();
        $coreResource = Mage::getSingleton('core/resource');
        switch ($alias) {
            case 'product_sku':
                $field = 'catalog_product_entity.sku';
                break;
            case 'avg_qty_ordered':
                $field = "order_item.avg_qty_ordered";
                break;
            case 'total_qty_ordered':
                $field = 'order_item.total_qty_ordered';
                break;
            case 'total_available_qty':
                $field = new Zend_Db_Expr('SUM(main_table.available_qty)');
                break;
            case 'out_of_stock_date':
                $field = new Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(SUM(main_table.available_qty)/order_item.avg_qty_ordered) DAY)");
                break;
            case 'supplyneeds':
                $field = new Zend_Db_Expr("GREATEST((ROUND(SUM(order_item.qty_ordered)/{$salesFromTo['count']},2) * {$getNumberDaysForecast} - SUM(main_table.available_qty)),0)");
                break;
            case 'in_purchasing':
                $field = "tmp_po_product.inpurchasing";
                break;
        }
        return $field;
    }

    public function _getSelectedProducts() {
        $productArrays = $this->getProducts();
        $products = '';
        $supplierProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                parse_str(urldecode($productArray), $supplierProducts);
                if (count($supplierProducts)) {
                    foreach ($supplierProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
        return $products;
    }

    public function addExportType($url, $label) {
        if ($filter = $this->getRequest()->getParam('top_filter'))
            $exportUrl = $this->getUrl($url, array('_current' => false, 'top_filter' => $filter));
        else
            $exportUrl = $this->getUrl($url, array('_current' => false));
        $this->_exportTypes[] = new Varien_Object(
                array(
            'url' => $exportUrl,
            'label' => $label
                )
        );
        return $this;
    }

}
