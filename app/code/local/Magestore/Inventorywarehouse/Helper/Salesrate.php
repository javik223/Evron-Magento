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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Helper_Salesrate extends Mage_Core_Helper_Abstract {
	public function calculateSalesRate($product_id){
        $requestData = Mage::helper('adminhtml')->prepareFilterString(Mage::app()->getRequest()->getParam('top_filter'));
        $warehouse = $datefrom = $dateto = '';

        if ($requestData && isset($requestData['warehouse_select']))
            $warehouse = $requestData['warehouse_select'];
        if ($requestData && isset($requestData['date_from'])){
            $datefrom = date("Y-m-d", strtotime($requestData['date_from']));
        }
        if (!$datefrom) {
            $now = now();            
            $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if ($requestData && isset($requestData['date_to'])){
            $dateto = date("Y-m-d", strtotime($requestData['date_to']));
        }   
        if (!$dateto) {
            $now = now();            
            $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if($datefrom)
            $datefrom = $datefrom . ' 00:00:00';
        if($dateto)
            $dateto = $dateto . ' 23:59:59';
        
        //calculate sales in period
        $salesData = $this->getSalesInPeriod($product_id, $datefrom, $dateto, $warehouse);
        //calculate purchase qty
        $purchaseQty = $this->getPurchaseInPeriod($product_id, $datefrom, $dateto, $warehouse);
        //calculate beginning balance
        $transactionQty = $this->getTransactionInPeriod($product_id, $datefrom, $dateto, $warehouse);
        $beginningBalance = $transactionQty + ($salesData['sales_qty'] + $salesData['qty_canceled']) - $purchaseQty;
        //calculate sale rates
        $sales_rate = (float) $salesData['sales_qty'] / (float) ($beginningBalance + $purchaseQty);
        if(!$sales_rate){
        	return 'N/A';
        }
        return $sales_rate;
    }

    public function getSalesInPeriod($product_id, $datefrom, $dateto, $warehouse){
        $orders = $this->getOrderInPeriod($datefrom, $dateto);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $salesData = array();
        if (!$warehouse) {
            $sales_qty = 0;
            if (count($orders) > 0) {
                $qtyOrdered  = 0;
                $qtyCanceled = 0;
                $qtyRefunded = 0;
                $stringOrders = implode(',',$orders);           
                $order_items = $readConnection->fetchAll("SELECT `parent_item_id`,`qty_refunded`,`qty_ordered`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$stringOrders'))");
                foreach ($order_items as $item) {
                    if($item['qty_ordered'] == 0 && $order_item['parent_item_id'])
                    {                                        
                        $parentId = $order_item['parent_item_id'];
                        $parents = $readConnection->fetchAll("SELECT `product_type`, `qty_ordered`,`qty_refunded`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (item_id = $parentId)");
                        foreach($parents as $parent) {
                            if($parent['product_type'] == 'configurable')
                            {
                                $qtyOrdered += $parent['qty_ordered'];                                    
                                $qtyCanceled += $parent['qty_canceled'];
                                $qtyRefunded += $parent['qty_refunded'];
                            }
                        }
                    } else {
                        $qtyOrdered += $item['qty_ordered'];
                        $qtyCanceled += $item['qty_canceled'];
                        $qtyRefunded += $item['qty_refunded'];
                    }
                }
                $sales_qty = ($qtyOrdered - $qtyCanceled - $qtyRefunded);
            }
        } else {
            $sales_qty = 0;
            if (count($orders) > 0) {
                $qtyOrdered  = 0;
                $qtyCanceled = 0;
                $qtyRefunded = 0;  
                $stringOrders = implode(',',$orders);
                $warehouse_order_ids = array();
                $warehouse_orders = $readConnection->fetchAll("SELECT `order_id` FROM `" . $resource->getTableName('inventoryplus/warehouse_shipment') . "` WHERE (product_id = '$product_id') AND (warehouse_id = '$warehouse') AND (order_id IN ('$stringOrders'))");
                foreach ($warehouse_orders as $warehouse_order) {
                    $warehouse_order_ids[] = $warehouse_order['order_id'];
                }
                $warehouse_order_ids_unique = array_unique($warehouse_order_ids);
                if(count($warehouse_order_ids_unique) > 0){
                	$string_warehouse_orders = implode("','", $warehouse_order_ids_unique);
                } else {
                	$string_warehouse_orders = 0;
                }
                $order_items = $readConnection->fetchAll("SELECT `qty_ordered`, `qty_refunded`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$string_warehouse_orders'))");
                foreach ($order_items as $item) {
                    if($item['qty_ordered'] == 0 && $order_item['parent_item_id'])
                    {                                        
                        $parentId = $order_item['parent_item_id'];
                        $parents = $readConnection->fetchAll("SELECT `product_type`, `qty_ordered`,`qty_refunded`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (item_id = $parentId)");
                        foreach($parents as $parent) {
                            if($parent['product_type'] == 'configurable')
                            {
                                $qtyOrdered += $parent['qty_ordered'];                                    
                                $qtyCanceled += $parent['qty_canceled'];
                                $qtyRefunded += $parent['qty_refunded'];
                            }
                        }
                    } else {
                        $qtyOrdered += $item['qty_ordered'];
                        $qtyCanceled += $item['qty_canceled'];
                        $qtyRefunded += $item['qty_refunded'];
                    }
                }
                $sales_qty = $qtyOrdered - $qtyCanceled - $qtyRefunded;
            }
        }
        $salesData['sales_qty'] = $sales_qty;
        $salesData['qty_canceled'] = $qtyCanceled;
        return $salesData;
        
    }

    public function getPurchaseInPeriod($product_id, $datefrom, $dateto, $warehouse){
        $purchaseOrders = $this->getPurchaseOrderInPeriod($datefrom,$dateto);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $purchase_qty = 0;
        if(!$warehouse){
            if (count($purchaseOrders) > 0) {
                $qtyOrdered  = 0;
                $qtyReturned = 0;
                $stringOrders = implode(',',$purchaseOrders);           
                $order_items = $readConnection->fetchAll("SELECT `qty_recieved`,`qty_returned` FROM `" . $resource->getTableName('inventorypurchasing/purchaseorder_product') . "` WHERE (product_id = '$product_id') AND (purchase_order_id IN ('$stringOrders'))");
                foreach ($order_items as $item) {
                    $qtyOrdered += $item['qty_recieved'];
                    $qtyReturned += $item['qty_returned'];
                }
                $purchase_qty = ($qtyOrdered - $qtyReturned);
            }
        } else {
            if (count($orders) > 0) {
                $qtyOrdered  = 0;
                $qtyReturned = 0;
                $stringOrders = implode(',',$orders);           
                $order_items = $readConnection->fetchAll("SELECT `qty_received`,`qty_returned` FROM `" . $resource->getTableName('inventorypurchasing/purchaseorder_productwarehouse') . "` WHERE (product_id = '$product_id') AND (purchase_order_id IN ('$stringOrders')) AND warehouse_id = '$warehouse'");
                foreach ($order_items as $item) {
                    $qtyOrdered += $item['qty_received'];
                    $qtyReturned += $item['qty_returned'];
                }
                $purchase_qty = ($qtyOrdered - $qtyReturned);
            }
        }
        return $purchase_qty;
    }

    public function getTransactionInPeriod($product_id, $datefrom, $dateto, $warehouse){
    	$resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $transactionIds = $this->getTransactionIdsInPeriod($datefrom, $dateto, $warehouse);
        if (count($transactionIds) > 0) {
            $transactionQty = 0;
            $stringTransactions = implode(',',$transactionIds);           
            $order_items = $readConnection->fetchAll("SELECT `qty` FROM `" . $resource->getTableName('inventorywarehouse/transaction_product') . "` WHERE (product_id = '$product_id') AND (warehouse_transaction_id IN ('$stringTransactions'))");
            foreach ($order_items as $item) {
                $transactionQty += $item['qty'];
            }
        }
        return $transactionQty;
    }
    public function getTransactionIdsInPeriod($datefrom, $dateto, $warehouse){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $transactionIds = array();
        $query = "SELECT `warehouse_transaction_id` FROM `" . $resource->getTableName('inventorywarehouse/transaction') . "` WHERE (created_at >= '$datefrom' AND created_at <= '$dateto') AND type IN ('1','2')";
        if($warehouse){
            $query .= "AND (warehouse_id_from = $warehouse) OR (warehouse_id_to = $warehouse)";
        }
        $transactions = $readConnection->fetchAll($query);
        if(count($transactions) > 0){
            foreach ($transactions as $order) {
                array_push($transactionIds, $order['warehouse_transaction_id']);
            }
        }
        
        return $transactionIds;
    }

    public function getPurchaseOrderInPeriod($datefrom, $dateto){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $orderIds = array();
        
        $orders = $readConnection->fetchAll("SELECT `purchase_order_id` FROM `" . $resource->getTableName('inventorypurchasing/purchaseorder') . "` WHERE (purchase_on >= '$datefrom' AND purchase_on <= '$dateto')");
        if(count($orders) > 0){
            foreach ($orders as $order) {
                array_push($orderIds, $order['purchase_order_id']);
            }
        }
        
        return $orderIds;
    }

    public function getOrderInPeriod($datefrom, $dateto) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $orderIds = array();
        $orders = $readConnection->fetchAll("SELECT `entity_id` FROM `" . $resource->getTableName('sales/order') . "` WHERE (created_at >= '$datefrom' AND created_at <= '$dateto')");
        if(count($orders) > 0){
            foreach ($orders as $order) {
                array_push($orderIds, $order['entity_id']);
            }
        }
        
        return $orderIds;
    }
}