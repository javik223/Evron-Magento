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

class Magestore_Inventoryreports_Helper_Customer extends Mage_Core_Helper_Abstract {

    public function getCustomerReportCollection($requestData) {
        //variable request data
        $time_request = $requestData['select_time'];
        $report_type = $requestData['report_radio_select'];
        $warehouse = isset($requestData['warehouse_select']) ? $requestData['warehouse_select'] : null;
        $arrayCollection = array();
        $datefrom = '';
        $dateto = '';
        //get time range
        if ($time_request == 'range') {
            if (isset($requestData['date_from'])) {
                $datefrom = $requestData['date_from'];
            } else {
                $now = now();
                $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
            }
            if (isset($requestData['date_to'])) {
                $dateto = $requestData['date_to'];
            } else {
                $now = now();
                $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
            }
            $datefrom = $datefrom . ' 00:00:00';
            $dateto = $dateto . ' 23:59:59';
        } else {
            $time_range = Mage::helper('inventoryreports')->getTimeSelected($requestData);
            if (isset($time_range['date_from']) && isset($time_range['date_to'])) {
                $datefrom = $time_range['date_from'];
                $dateto = $time_range['date_to'];
            }
        }
        /* Prepare Collection */
        //switch report type
        switch ($report_type) {
            case 'customer':
                return $this->getCustomerCollection($datefrom, $dateto);
        }
    }

    public function getCustomerCollection($datefrom, $dateto) {
        $collection = Mage::getResourceModel('inventoryreports/sales_order_collection')
                ->addFieldToFilter('created_at', array('from' => $datefrom, 'to' => $dateto))
                ->addFieldToFilter('status', 'complete')
                ;
        $collection->setOrder('main_table.created_at');
        $collection->getSelect()
            ->joinLeft(array(
                'address' => $collection->getTable('sales/order_address')),
                'main_table.entity_id = address.parent_id',
                array('telephone')
            )
            ->columns(array(
                'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")'),
                'numberoforder' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
                'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'),
                'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'),
                //'customer_status' => 'CASE WHEN COUNT(DISTINCT main_table.entity_id) = 1 THEN "NEW" WHEN COUNT(DISTINCT main_table.entity_id) <10 THEN "RETURN" ELSE "VIP" END'
            ))
            ->group('address.telephone')
            ->where("address.address_type = 'billing'")    
            ;
        return $collection;
    }


}

?>