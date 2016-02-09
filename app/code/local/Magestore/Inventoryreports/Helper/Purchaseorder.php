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
 * @package     Magestore_Inventoryplus
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
class Magestore_Inventoryreports_Helper_Purchaseorder extends Mage_Core_Helper_Abstract {
    
    /**
     * Get purchased collection
     * 
     * @param array $requestData
     */
    public function getCollection($requestData) {
        $timeRange = Mage::helper('inventoryreports')->getTimeRange($requestData);
        $report = isset($requestData['report_radio_select']) ? $requestData['report_radio_select'] : 'po_sku';
        switch($report){
            case 'po_supplier':
                $collection = $this->getPOSupplierCollection($timeRange);
                break;
            case 'po_warehouse':
                $collection = $this->getPOWarehouseCollection($timeRange);
                break;
            case 'po_sku':
                $collection = $this->getPOSKUCollection($timeRange);
                break;
        }
        return $collection;
    }
    
    /**
     * Get purchases collection by supplier
     * 
     * @param array $timeRange
     */
    public function getPOSupplierCollection($timeRange){
        $collection = Mage::getResourceModel('inventorypurchasing/purchaseorder_collection');
        $collection->addFieldToFilter('purchase_on', array(
            'from' => $timeRange['from'],
            'to' => $timeRange['to'],
            'date' => true,
        ));             
        $collection->addFieldToFilter('status', Magestore_Inventorypurchasing_Model_Status::STATUS_COMPLETE);
        $collection->getSelect()->group('main_table.supplier_id');
        //$collection->getSelect()->order('IFNULL(SUM(main_table.total_amount),0) DESC');
        $collection->getSelect()->columns(array(
            'all_purchase_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.purchase_order_id SEPARATOR ",")'),
            'total_order' => new Zend_Db_Expr('COUNT(DISTINCT main_table.purchase_order_id)'),
            'total_value' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_amount),0)'),
            'total_qty' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_products_recieved),0)'),
        ));
        return $collection;
    }
    
    /**
     * Get purchases collection by warehouse
     * 
     * @param array $timeRange
     */
    public function getPOWarehouseCollection($timeRange){
        $collection = Mage::getResourceModel('inventorypurchasing/purchaseorder_productwarehouse_collection');
        $collection->addFieldToFilter('PO.purchase_on', array(
            'from' => $timeRange['from'],
            'to' => $timeRange['to'],
            'date' => true,
        ));  
        $collection->getSelect()->joinLeft(
                array('PO' => $collection->getTable('inventorypurchasing/purchaseorder')),
                    'main_table.purchase_order_id = PO.purchase_order_id',
                    array('purchase_on', 'status')
        );    
        $collection->getSelect()->joinLeft(
                array('POP' => $collection->getTable('inventorypurchasing/purchaseorder_product')),
                    'main_table.purchase_order_id = POP.purchase_order_id AND main_table.product_id = POP.product_id',
                    array('cost', 'discount', 'tax')
        );          
        $collection->getSelect()->joinLeft(
                array('warehouse' => $collection->getTable('inventoryplus/warehouse')),
                    'main_table.warehouse_id= warehouse.warehouse_id',
                    array('warehouse_name')
        );         
        $collection->addFieldToFilter('PO.status', Magestore_Inventorypurchasing_Model_Status::STATUS_COMPLETE);
        $collection->getSelect()->where('warehouse.warehouse_id is NOT NULL');
        $collection->getSelect()->group('main_table.warehouse_id');
        //$collection->getSelect()->order('IFNULL(SUM(main_table.total_amount),0) DESC');
        $collection->getSelect()->columns(array(
            'all_purchase_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.purchase_order_id SEPARATOR ",")'),
            'total_order' => new Zend_Db_Expr('COUNT(DISTINCT main_table.purchase_order_id)'),
            'total_value' => new Zend_Db_Expr('SUM( (main_table.qty_received - main_table.qty_returned)* IFNULL(POP.cost,0) * (100 + IFNULL(POP.tax,0) - IFNULL(POP.discount,0)) )/100'),
            'total_qty' => new Zend_Db_Expr('IFNULL(SUM(main_table.qty_received - main_table.qty_returned),0)'),
        ));
        
        return $collection;
    }    
    
    /**
     * Get purchases collection by SKU
     * 
     * @param array $timeRange
     */
    public function getPOSKUCollection($timeRange){
        $collection = Mage::getResourceModel('inventorypurchasing/purchaseorder_product_collection');
        $collection->addFieldToFilter('purchase_on', array(
            'from' => $timeRange['from'],
            'to' => $timeRange['to'],
            'date' => true,
        ));  
        $collection->getSelect()->joinLeft(
                array('PO' => $collection->getTable('inventorypurchasing/purchaseorder')),
                    'main_table.purchase_order_id= PO.purchase_order_id',
                    array('status','purchase_on')
        );  
        /*
        $collection->getSelect()->joinLeft(
                array('productSuper' => $collection->getTable('catalog/product_super_link')),
                    'main_table.product_id= productSuper.product_id',
                    array('parent_id')
        );     
         */
        $collection->getSelect()->joinLeft(
                array('product' => $collection->getTable('catalog/product')),
                    'main_table.product_id = product.entity_id',
                    array('sku')
        );          
        $collection->getSelect()->where('main_table.qty_recieved > 0');
        //$collection->addFieldToFilter('PO.status', Magestore_Inventorypurchasing_Model_Status::STATUS_COMPLETE);
        $collection->getSelect()->group('product.entity_id');       
        $collection->getSelect()->columns(array(
            'all_purchase_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.purchase_order_id SEPARATOR ",")'),
            //'all_child_product_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT productSuper.product_id SEPARATOR ",")'),
            'total_order' => new Zend_Db_Expr('COUNT(DISTINCT main_table.purchase_order_id)'),
            'total_value' => new Zend_Db_Expr('SUM( (main_table.qty_recieved - main_table.qty_returned)* IFNULL(main_table.cost,0) * (100 + IFNULL(main_table.tax,0) - IFNULL(main_table.discount,0)) / 100)'),
            'total_qty' => new Zend_Db_Expr('IFNULL(SUM(main_table.qty_recieved - main_table.qty_returned),0)'),
            //'child_product_qty' => new Zend_Db_Expr('GROUP_CONCAT(CONCAT_WS(":", main_table.product_id, (main_table.qty_recieved - main_table.qty_returned)) SEPARATOR ",")'),
        ));  
        return $collection;
    }
    
    
    /**
     * Get purchased qty in each warehouse, filterred by product ids
     * 
     * @param array $POIDs
     * @param array $productIds
     * @param string $warehouseId
     * @return int|float
     */
    public function getWarehousePOQtyByProduct($POIDs, $productIds, $warehouseId){
        $warehousePOProducts = Mage::getResourceModel('inventorypurchasing/purchaseorder_productwarehouse_collection')
                                    ->addFieldToFilter('purchase_order_id', array('in'=> $POIDs))
                                    ->addFieldToFilter('product_id', array('in'=>$productIds))
                                    ->addFieldToFilter('warehouse_id', $warehouseId);     
        $qty = 0;
        if(count($warehousePOProducts)){
            foreach($warehousePOProducts as $warehousePOProduct){
                $qty += $warehousePOProduct->getQtyReceived() - $warehousePOProduct->getQtyReturned();
            }
        }
        return $qty;
    }
    
    /**
     * Get purchased qty in each warehouse
     * 
     * @param array $POIDs
     * @param array $productIds
     * @param string $warehouseId
     * @return int|float
     */
    public function getWarehousePOQty($POIDs, $warehouseId){
        $warehousePOProducts = Mage::getResourceModel('inventorypurchasing/purchaseorder_productwarehouse_collection')
                                    ->addFieldToFilter('purchase_order_id', array('in'=> $POIDs))
                                    ->addFieldToFilter('warehouse_id', $warehouseId);     
        $qty = 0;
        if(count($warehousePOProducts)){
            foreach($warehousePOProducts as $warehousePOProduct){
                $qty += $warehousePOProduct->getQtyReceived() - $warehousePOProduct->getQtyReturned();
            }
        }
        return $qty;
    }    
        
}
