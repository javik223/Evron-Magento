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
class Magestore_Inventoryreports_Block_Adminhtml_Sales_History_Renderer_Purchased extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) 
    {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        foreach ($requestData as $key => $value)
            if (!empty($value))
                $filterData->setData($key, $value);
        $dateFrom = $filterData->getData('date_from',null);
        $dateTo = $filterData->getData('date_to',null);  
        $productId = $row->getId();
        $resource = Mage::getSingleton('core/resource');        
        $readConnection = $resource->getConnection('core_read');
        if($warehouseId = $this->getRequest()->getParam('warehouse_id')){
            $results = '';        
            $purchaseOrderIds = array();        
            $sql = 'SELECT `purchase_order_id` FROM '.$resource->getTableName('inventorypurchasing/purchaseorder').' where (`purchase_on` >= \''. $dateFrom .'\') and (`purchase_on` <= \''. $dateTo .'\') and (`status` != '. 7 .')';         
            $results = $readConnection->query($sql);            
            if($results){
                foreach($results as $result){            
                    $purchaseOrderIds[] = $result['purchase_order_id'];
                }
            }
            $products = Mage::getModel('inventorypurchasing/purchaseorder_productwarehouse')
                                        ->getCollection()
                                        ->addFieldToFilter('purchase_order_id',array('in'=>$purchaseOrderIds))
                                        ->addFieldToFilter('product_id',$productId)
                                        ->addFieldToFilter('warehouse_id',$warehouseId);
            $qtyPurchased = 0;
            if($products->getSize() > 0){
                foreach($products as $product){
                    $qtyPurchased += $product->getData('qty_received') - $product->getData('qty_returned');
                }
            }
        }else{            
            $results = '';        
            $purchaseOrderIds = array();        
            $sql = 'SELECT `purchase_order_id` FROM '.$resource->getTableName('inventorypurchasing/purchaseorder').' where (`purchase_on` >= \''. $dateFrom .'\') and (`purchase_on` <= \''. $dateTo .'\') and (`status` != '. 7 .')';         

            $results = $readConnection->query($sql);            
            if($results){
                foreach($results as $result){            
                    $purchaseOrderIds[] = $result['purchase_order_id'];
                }
            }
            $products = Mage::getModel('inventorypurchasing/purchaseorder_product')
                                        ->getCollection()
                                        ->addFieldToFilter('purchase_order_id',array('in'=>$purchaseOrderIds))
                                        ->addFieldToFilter('product_id',$productId);
            $qtyPurchased = 0;
            if($products->getSize() > 0){
                foreach($products as $product){
                    $qtyPurchased += $product->getData('qty_recieved') - $product->getData('qty_returned');
                }
            }
        }
        return $qtyPurchased;
    }
}