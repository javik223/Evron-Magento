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
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Helper_Stockmovement extends Mage_Core_Helper_Abstract {

    public function getMovementReportCollection($requestData) {
        //variable request data
        $report_type = isset($requestData['report_radio_select']) ? $requestData['report_radio_select'] : null;
        $warehouse = isset($requestData['warehouse_select']) ? $requestData['warehouse_select'] : null;
        $timeRange = Mage::helper('inventoryreports')->getTimeRange($requestData);
        /* Prepare Collection */
        //switch report type
        $dbResource = new Mage_Core_Model_Resource_Setup('core_setup');
        $dbResource->run('SET SESSION group_concat_max_len = 999999;');
        switch ($report_type) {
            case 'stock_in':
                return $this->getStockInCollection($timeRange['from'], $timeRange['to'], $warehouse);
            case 'stock_out':
                return $this->getStockOutCollection($timeRange['from'], $timeRange['to'], $warehouse);
        }
    }

    public function getStockInCollection($datefrom, $dateto, $warehouse) {
        $collection = Mage::getResourceModel('inventoryplus/transaction_collection')
                ->addFieldToFilter('type', array('in' => array(2, 3, 6)))
                ->addFieldToFilter('created_at', array('from' => $datefrom, 'to' => $dateto))
        ;
        if ($warehouse) {
            $collection->addFieldToFilter('warehouse_id_to', $warehouse);
        }
        $collection->getSelect()
                ->joinLeft(array(
                    'transactionproduct' => $collection->getTable('inventoryplus/transaction_product')), 'main_table.warehouse_transaction_id = transactionproduct.warehouse_transaction_id', array('transactionproductqty' => new Zend_Db_Expr('IFNULL(SUM(transactionproduct.qty),0)'))
                )
                ->columns(array(
                    'all_movement_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.warehouse_transaction_id SEPARATOR ",")'),
                    'numberoftransaction' => new Zend_Db_Expr('IFNULL(COUNT(DISTINCT main_table.warehouse_transaction_id),0)'),
                    'numbertransactionproduct' => new Zend_Db_Expr('IFNULL(COUNT(DISTINCT transactionproduct.product_id),0)')
                ))
                ->group('main_table.type')
        ;
        return $collection;
    }

    public function getStockOutCollection($datefrom, $dateto, $warehouse) {
        $collection = Mage::getResourceModel('inventoryplus/transaction_collection')
                ->addFieldToFilter('type', array('in' => array(1, 4, 5)))
                ->addFieldToFilter('created_at', array('from' => $datefrom, 'to' => $dateto))
        ;
        if ($warehouse) {
            $collection->addFieldToFilter('warehouse_id_from', $warehouse);
        }
        $collection->getSelect()
                ->joinLeft(array(
                    'transactionproduct' => $collection->getTable('inventoryplus/transaction_product')), 'main_table.warehouse_transaction_id = transactionproduct.warehouse_transaction_id', array('transactionproductqty' => new Zend_Db_Expr('IFNULL(-SUM(transactionproduct.qty),0)'))
                )
                ->columns(array(
                    'all_movement_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.warehouse_transaction_id SEPARATOR ",")'),
                    'numberoftransaction' => new Zend_Db_Expr('IFNULL(COUNT(DISTINCT main_table.warehouse_transaction_id),0)'),
                    'numbertransactionproduct' => new Zend_Db_Expr('IFNULL(COUNT(DISTINCT transactionproduct.product_id),0)')
                ))
                ->group('main_table.type')
        ;
        return $collection;
    }

}

?>