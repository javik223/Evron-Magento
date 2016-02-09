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
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Gridexport
    extends Magestore_Inventoryplus_Block_Adminhtml_Widget_Grid {

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
            $this->_helperClass = $helperClass;
        }
        return $helperClass;
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
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('inventoryplus')->__('SKU'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sku'
        ));
        $helperClass = $this->getHelperClass();
        $warehouseSelected = $helperClass->getWarehouseSelected();
        if (count($warehouseSelected) > 1) {
            foreach ($warehouseSelected as $warehouseId) {
                $this->addColumn('avg_qty_ordered_' . $warehouseId, array(
                    'header' => Mage::helper('inventoryplus')->__('Qty. sold/day'),
                    'align' => 'right',
                    'index' => 'avg_qty_ordered_' . $warehouseId,
                    'type' => 'number'
                ));
                $this->addColumn('total_qty_ordered_' . $warehouseId, array(
                    'header' => Mage::helper('inventoryplus')->__('Total Sold'),
                    'align' => 'right',
                    'index' => 'total_qty_ordered_' . $warehouseId,
                    'type' => 'number'
                ));
                $this->addColumn('available_qty_' . $warehouseId, array(
                    'header' => Mage::helper('inventoryplus')->__('Avail. Qty'),
                    'align' => 'right',
                    'index' => 'available_qty_' . $warehouseId,
                    'type' => 'number'
                ));
                $this->addColumn('in_purchasing_' . $warehouseId, array(
                    'header' => Mage::helper('inventoryplus')->__('In Purchasing'),
                    'align' => 'right',
                    'sortable' => false,
                    'filter' => false,
                    'width' => '80px',
                    'index' => 'in_purchasing_' . $warehouseId
                ));
                $this->addColumn('out_of_stock_date_' . $warehouseId, array(
                    'header' => Mage::helper('inventoryplus')->__('Out-of-stock Date'),
                    'align' => 'left',
                    'index' => 'out_of_stock_date_' . $warehouseId,
                    'type' => 'date'
                ));
                $this->addColumn('supplyneeds_' . $warehouseId, array(
                    'header' => Mage::helper('inventoryplus')->__('Supply Needs'),
                    'align' => 'right',
                    'index' => 'purchase_more_' . $warehouseId,
                    'type' => 'number'
                ));
            }
        }
        $this->addColumn('avg_qty_ordered', array(
            'header' => Mage::helper('inventoryplus')->__('Qty. sold/day'),
            'align' => 'right',
            'index' => 'avg_qty_ordered',
            'type' => 'number'
        ));
        $this->addColumn('total_qty_ordered', array(
            'header' => Mage::helper('inventoryplus')->__('Total Sold'),
            'align' => 'right',
            'index' => 'total_qty_ordered',
            'type' => 'number'
        ));
        $this->addColumn('total_available_qty', array(
            'header' => Mage::helper('inventoryplus')->__('Avail. Qty'),
            'align' => 'right',
            'index' => 'total_available_qty',
            'type' => 'number'
        ));
        $this->addColumn('in_purchasing', array(
            'header' => Mage::helper('inventoryplus')->__('Qty On Order'),
            'align' => 'right',
            'index' => 'in_purchasing',
            'sortable' => false,
            'filter' => false,
            'width' => '80px'
        ));
        $this->addColumn('out_of_stock_date', array(
            'header' => Mage::helper('inventoryplus')->__('Out-of-stock Date'),
            'align' => 'left',
            'index' => 'out_of_stock_date',
            'type' => 'date'
        ));
        $this->addColumn('supplyneeds', array(
            'header' => Mage::helper('inventoryplus')->__('Supply Needs'),
            'align' => 'right',
            'index' => 'purchase_more',
            'type' => 'number'
        ));

        return parent::_prepareColumns();
    }

    public function _prepareCollectionInContruct($helperClass) {
        $dateto = $helperClass->getForecastTo();
        $salesFromTo = $helperClass->getSalesFromTo();
        $warehouseSelected = $helperClass->getWarehouseSelected();
        $supplierSelected = $helperClass->getsupplierSelected();
        $historySelected = $helperClass->getHistorySelected();
        $getNumberDaysForecast = $helperClass->getNumberDaysForecast();
        $purchase_more_rate = $helperClass->getRatePurchaseMore();
        $rate = $purchase_more_rate / 100;
        $coreResource = Mage::getSingleton('core/resource');
        if (count($warehouseSelected) > 1) { // If select MULTI warehouses in supplyneeds page
            foreach ($warehouseSelected as $warehouseId) {
                $listItemIds = $this->getListOrderItemIds($salesFromTo, array($warehouseId));
                $collectionWarehouse = $this->getTemporaryCollection($dateto, $salesFromTo, array($warehouseId), $supplierSelected, $historySelected, $getNumberDaysForecast, $rate, false);
                $_temp_sql = " DROP TABLE IF EXISTS " . $coreResource->getTableName('supplyneeds_step_' . $warehouseId) . ";";
                $_temp_sql .= " CREATE TEMPORARY TABLE " . $coreResource->getTableName('supplyneeds_step_' . $warehouseId) . " ("; // CREATE TEMPORARY TABLE
                $_temp_sql .= $collectionWarehouse->getSelect()->__toString() . ")";
                Mage::getSingleton('core/resource')->getConnection('core_write')->query($_temp_sql);
            }
            $collection = $this->getTemporaryCollection($dateto, $salesFromTo, $warehouseSelected, $supplierSelected, $historySelected, $getNumberDaysForecast, $rate, true);
            foreach ($warehouseSelected as $warehouseId) {
                $collection->getSelect()
                        ->joinLeft(
                                array('temp_supplyneeds_' . $warehouseId => $coreResource->getTableName('supplyneeds_step_' . $warehouseId)), "main_table.product_id=temp_supplyneeds_{$warehouseId}.product_id", array('temp_supplyneeds_' . $warehouseId . '.*'));
            }
        } else { 
            // If select ONE warehouse in supplyneeds page
            $collection = $this->getTemporaryCollection($dateto, $salesFromTo, $warehouseSelected, $supplierSelected, $historySelected, $getNumberDaysForecast, $rate, true);
        }
        $this->_collectionGrid = $collection;
    }

    protected function getTemporaryCollection($dateto, $salesFromTo, $warehouseSelected, $supplierSelected, $historySelected, $getNumberDaysForecast, $rate, $getSku) {
        if (count($warehouseSelected) == 1 && $getSku != true)
            $postfix = "_{$warehouseSelected[0]}";
        else
            $postfix = "";
        $listItemIds = $this->getListOrderItemIds($salesFromTo, $warehouseSelected);
        $tempTableArr = array('export_supplier_temp_table', 'export_order_item_temp_table', 'export_purchase_order_product_temp');
        $this->removeTempTables($tempTableArr);
        $w_productCol = $this->getWarehouseProductCollection($warehouseSelected, $getSku);
        $s_productCol = $this->getSupplierProductCollection($supplierSelected, $getSku);
        $this->createTempTable('export_supplier_temp_table', $s_productCol);
        if ($listItemIds) {
            $orderItemCol = $this->getOrderItemCollection($listItemIds, $salesFromTo, $warehouseSelected, $getSku);
            $this->createTempTable('export_order_item_temp_table', $orderItemCol);
        }
        $poProductCol = $this->getPOProductCollection($warehouseSelected, $supplierSelected, $getSku);
        $this->createTempTable('export_purchase_order_product_temp', $poProductCol);
        $coreResource = Mage::getSingleton('core/resource');
        $collection = $w_productCol;
        $collection->getSelect()
                ->join(
                        array('supplier_product' => $coreResource->getTableName('export_supplier_temp_table')), "main_table.product_id=supplier_product.product_id", array('supplier_product.all_supplier_id'));
        if ($listItemIds) {
            $collection->getSelect()
                    ->join(
                            array('order_item' => $coreResource->getTableName('export_order_item_temp_table')), "main_table.product_id=order_item.product_id", array('order_item.total_qty_ordered' . $postfix, 'order_item.avg_qty_ordered' . $postfix));
            $collection->getSelect()
                    ->joinLeft(
                            array('tmp_po_product' => $coreResource->getTableName('export_purchase_order_product_temp')), "main_table.product_id=tmp_po_product.product_id", array('in_purchasing' . $postfix => new Zend_Db_Expr("IFNULL(tmp_po_product.in_purchasing{$postfix},0)")));
            $collection->getSelect()->columns(array(
                'out_of_stock_date' . $postfix => new Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(SUM(main_table.available_qty)/order_item.avg_qty_ordered{$postfix}) DAY)"),
                'supplyneeds' . $postfix => new Zend_Db_Expr("GREATEST((order_item.avg_qty_ordered{$postfix} * {$getNumberDaysForecast} - SUM(main_table.available_qty)),0)"),
                'purchase_more' . $postfix => new Zend_Db_Expr("CEIL(GREATEST(((order_item.avg_qty_ordered{$postfix} * {$getNumberDaysForecast} - SUM(main_table.available_qty))* {$rate} - IFNULL(tmp_po_product.in_purchasing{$postfix},0)),0))"),
            ));
        } else {
            $collection->getSelect()
                    ->joinLeft(
                            array('tmp_po_product' => $coreResource->getTableName('export_purchase_order_product_temp')), "main_table.product_id=tmp_po_product.product_id", array('in_purchasing' . $postfix => new Zend_Db_Expr("IFNULL(tmp_po_product.in_purchasing{$postfix},0)")));
            $collection->getSelect()->columns(array(
                'out_of_stock_date' . $postfix => new Zend_Db_Expr('IF((SUM(main_table.product_id)*0)=0,NULL,(SUM(main_table.product_id)*0))'),
                'supplyneeds' . $postfix => new Zend_Db_Expr('SUM(main_table.product_id)*0'),
                'purchase_more' . $postfix => new Zend_Db_Expr('SUM(main_table.product_id)*0'),
                'total_qty_ordered' . $postfix => new Zend_Db_Expr('SUM(main_table.product_id)*0'),
                'avg_qty_ordered' . $postfix => new Zend_Db_Expr('SUM(main_table.product_id)*0')));
        }
        return $collection;
    }

    protected function getListOrderItemIds($salesFromTo, $warehouseSelected) {
        $coreResource = Mage::getSingleton('core/resource');
        $coreResource->getConnection('core_write')->query('SET SESSION group_concat_max_len = 1000000;');
        $warehouseSelectedStr = implode(',', $warehouseSelected);
        $conditionOne = "order_item.created_at > '{$salesFromTo['from']}' AND order_item.created_at < '{$salesFromTo['to']}' AND main_table.warehouse_id IN ({$warehouseSelectedStr})";
        $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection();
        $warehouseOrder->getSelect()
                ->joinLeft(
                        array('order_item' => $warehouseOrder->getTable('sales/order_item')), "main_table.item_id=order_item.item_id", array('item_id'));
        $warehouseOrder->getSelect()->where($conditionOne);
        $warehouseOrder->getSelect()->columns(array(
            'all_item_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.item_id SEPARATOR ",")')));
        $itemIds = $warehouseOrder->getFirstItem()->getAllItemId();
        return $itemIds;
    }

    protected function getWarehouseProductCollection($warehouseSelected, $getSku) {
        if (count($warehouseSelected) == 1 && $getSku != true)
            $postfix = "_{$warehouseSelected[0]}";
        else
            $postfix = "";
        $coreResource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToSelect('product_id');
        if (count($warehouseSelected) > 1)
            $collection->addFieldToFilter('warehouse_id', array('in' => $warehouseSelected));
        else
            $collection->addFieldToFilter('warehouse_id', $warehouseSelected[0]);
        $collection->getSelect()->group('main_table.product_id');
        $collection->getSelect()->columns(array(
            'total_available_qty' . $postfix => new Zend_Db_Expr('SUM(available_qty)'),
            'available_qty' . $postfix => 'available_qty'
        ));
        if (count($warehouseSelected) > 1)
            $collection->getSelect()
                    ->joinLeft(
                            array('catalog_product_entity'=> $coreResource->getTableName('catalog_product_entity')), "main_table.product_id = " . 'catalog_product_entity' . ".entity_id", array('sku'));
        return $collection;
    }

    protected function getSupplierProductCollection($supplierSelected, $getSku) {
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

    protected function getOrderItemCollection($listItemIds, $salesFromTo, $warehouseSelected, $getSku) {
        $helperClass = $this->getHelperClass();
        $salesFromTo = $helperClass->getSalesFromTo();
        $coreResource = Mage::getSingleton('core/resource');
        $daysInStock = $this->getNumberDaysInstock($helperClass);
        $this->removeTempTables(array('days_instock_table'));
        $this->createTempTable('days_instock_table', $daysInStock);
        $days = $this->getNumberDaysFromTwoDate($salesFromTo['from'], $salesFromTo['to']);
        if (count($warehouseSelected) == 1 && $getSku != true)
            $postfix = "_{$warehouseSelected[0]}";
        else
            $postfix = "";
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->addFieldToSelect('*');
        $collection->getSelect()->where("item_id IN ({$listItemIds})");
        $collection->getSelect()
                ->joinLeft(
                        array('days_instock_templ' => $coreResource->getTableName('days_instock_table')), "main_table.product_id=days_instock_templ.entity_id", array('number_days_instock'));
        $collection->getSelect()->columns(array(
            'total_qty_ordered'.$postfix => new Zend_Db_Expr('SUM(qty_ordered)'),
            'avg_qty_ordered'.$postfix => new Zend_Db_Expr("ROUND(SUM(qty_ordered)/IFNULL(number_days_instock,'$days'),2)")));
        $collection->getSelect()->group('product_id');
        return $collection;
    }

    protected function getPOProductCollection($warehouseSelected, $supplierSelected, $getSku) {
        if (count($warehouseSelected) == 1 && $getSku != true)
            $postfix = "_{$warehouseSelected[0]}";
        else
            $postfix = "";
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
            'in_purchasing' . $postfix => new Zend_Db_Expr('IFNULL(SUM(`qty_order` - `qty_received` + `qty_returned`),0)')));
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

    public function getGridUrl() {
        return false;
    }

    public function getRowUrl($row) {
        return false;
    }

    /**
     * Get loaded collection
     * 
     * @return collection
     */
    public function getCollectionData($productIds = array()) {
        $this->_isExport = true;
        $this->_prepareGrid();
        if (count($productIds)) {
            $this->getCollection()->addFieldToFilter('main_table.product_id', array('in' => $productIds));
        }
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        return $this->getCollection();
    }

    public function getFirstRowCsv() {
        $return = '""';
        $helperClass = $this->getHelperClass();
        $warehouseSelected = $helperClass->getWarehouseSelected();
        if (count($warehouseSelected) > 1) {
            foreach ($warehouseSelected as $warehouseId) {
                $warehouseName = Mage::getModel('inventoryplus/warehouse')->load($warehouseId)->getWarehouseName();
                $return .= ',"' . $warehouseName . '",""' . ',""' . ',""' . ',""' . ',""';
            }
            $return .= ',"' . $this->__('Total') . '",""' . ',""' . ',""' . ',""' . ',""';
        }
        return $return;
    }

    public function getCsv() {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = array();
        $csv .= $this->getFirstRowCsv() . "\n";
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv.= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($item)) . '"';
                }
            }
            $csv.= implode(',', $data) . "\n";
        }

        if ($this->getCountTotals()) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($this->getTotals())) . '"';
                }
            }
            $csv.= implode(',', $data) . "\n";
        }

        return $csv;
    }

}
