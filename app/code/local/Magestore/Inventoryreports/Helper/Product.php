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
class Magestore_Inventoryreports_Helper_Product extends Mage_Core_Helper_Abstract {
    
    /**
     * Get stock remaining collection, filterable by warehouse
     * 
     * @param array $requestData
     * @return collection
     */
    public function getStockRemainingCollection($requestData) {
        $collection = Mage::getResourceModel('inventoryreports/product_collection')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id');
        $collection->getSelect()->joinLeft(
                array('warehouseproduct' => $collection->getTable('inventoryplus/warehouse_product')), 'entity_id = product_id', array('warehouse_id', 'total_qty')
        );          
        $collection->getSelect()->columns(array('total_remain' => new Zend_Db_Expr("SUM(warehouseproduct.total_qty)")));
        //filter by warehouse
        if(isset($requestData['warehouse_select']) && $requestData['warehouse_select']) {
            $collection->getSelect()->where('`warehouseproduct`.`warehouse_id` = \''.$requestData['warehouse_select'].'\'');
        }        
        $collection->getSelect()->group('e.entity_id');
        $collection->setIsGroupCountSql(true);   
        return $collection;
    }
    
    /**
     * Get best seller collection
     * 
     * @param array $requetsData
     * return collection
     */
    public function getBestSellerCollection($requetsData) {
        $gettime = Mage::helper('inventoryreports')->getTimeSelected($requestData);
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->getSelect()
                ->joinLeft(
                        array('order' => $collection->getTable('sales/order')), 'main_table.order_id = order.entity_id', array('shipping_method')
        );

        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select']) {
            $collection->getSelect()->joinLeft(
                    array('warehouse' => $collection->getTable('inventoryplus/warehouse_shipment')), 'main_table.order_id = warehouse.order_id', array('warehouse_id')
            );
            $collection->getSelect()
                    //->columns('sum(main_table.qty_ordered) as total_ordered')
                    ->columns('sum(warehouse.qty_shipped) as total_invoiced')
                    ->columns('sum(warehouse.qty_refunded) as total_refunded')
                    ->columns('(sum(warehouse.qty_shipped) - sum(warehouse.qty_refunded)) as total_sold');
            $collection->getSelect()->where('warehouse.warehouse_id = \'' . $requestData['warehouse_select'] . '\'');
        } else {
            $collection->getSelect()
                    //->columns('sum(main_table.qty_ordered) as total_ordered')
                    ->columns('sum(main_table.qty_invoiced) as total_invoiced')
                    ->columns('sum(main_table.qty_refunded) as total_refunded')
                    ->columns('(sum(main_table.qty_invoiced) - sum(main_table.qty_refunded)) as total_sold');
        }
        if (isset($requestData['shipping_select']) && $requestData['shipping_select']) {
            $collection->getSelect()
                    ->where('order.shipping_method LIKE "%' . $requestData['shipping_select'] . '%"');
        }
        $collection->getSelect()->where('order.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '" AND `main_table`.`parent_item_id` is null');
        $collection->getSelect()->group('main_table.sku'); 
        return $collection;
    }
}
