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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyproduct_Renderer_Time extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) 
    {
        $time = '';
        $count = 0;
        $totalTime = 0;
        $now = time(); // or your date as well
        
        $block = new Magestore_Inventoryreports_Block_Adminhtml_Supplier_Product_Grid;        
        $filter   = $block->getParam($block->getVarNameFilter(), null);
        $condorder = '';
        if($filter){
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach($data as $value=>$key){
                if($value == 'supplier_id'){
                    $condorder = $key;
                }                
            }
        }
        
        if(Mage::helper('core')->isModuleEnabled('Magestore_Inventorybarcode')){
            $resource = Mage::getSingleton('core/resource');        
            $readConnection = $resource->getConnection('core_read');
            $results = '';
            $purchaseOrderIds = array();
            if($condorder){
                $sql = 'SELECT distinct(`purchaseorder_purchase_order_id`) FROM '.$resource->getTableName('inventorybarcode/barcode').' where (`product_entity_id` = '.$row->getId().
                                                                                                        ') and (`supplier_supplier_id` = '. $condorder .') and (`qty` > '. 0 .')'; 
            }else{
                $sql = 'SELECT distinct(`purchaseorder_purchase_order_id`) FROM '.$resource->getTableName('inventorybarcode/barcode').' where (`product_entity_id` = '.$row->getId().
                                                                                                        ') and (`qty` > '. 0 .')';     
            }
            $results = $readConnection->query($sql);            
            if($results){
                foreach($results as $result){            
                    $purchaseOrderIds[] = $result['purchaseorder_purchase_order_id'];
                }
            }
            $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                                            ->getCollection()
                                            ->addFieldToFilter('purchase_order_id',array('in'=>$purchaseOrderIds));
            $count += $purchaseOrders->getSize();
            $notPurchases = Mage::getModel('inventorybarcode/barcode')
                                    ->getCollection()
                                    ->addFieldToFilter('purchaseorder_purchase_order_id','')
                                    ->addFieldToFilter('qty',array('gt'=>0));
            $count += $notPurchases->getSize();
            foreach($purchaseOrders as $purchaseOrder){
                $your_date = strtotime($purchaseOrder->getPurchaseOn());
                $datediff = $now - $your_date;
                $totalTime += floor($datediff/(60*60*24));
                $time = 1;
            }
            
            if($time == ''){
                return 'N/A';
            }
            $time = round($totalTime/$count,1);
            if($condorder){
                return '<a href="#" onclick="showTimeDelivery('.$condorder.','.$row->getId().');return false;" title="'.Mage::helper('inventoryreports')->__('Report Time Inventory By Product').'">'.$time.'</a>';
            }else{
                return '<a href="#" onclick="showTimeDeliveryByProduct('.$row->getId().');return false;" title="'.Mage::helper('inventoryreports')->__('Report Time Inventory By Product').'">'.$time.'</a>';
            }
        }
        $deliveries = Mage::getModel('inventorypurchasing/purchaseorder_delivery')
                            ->getCollection()
                            ->addFieldToFilter('product_id',$row->getId());
        foreach($deliveries as $delivery){            
            $count++;            
            $your_date = strtotime($delivery->getDeliveryDate());
            $datediff = $now - $your_date;
            $time = 1;
            $totalTime += floor($datediff/(60*60*24));
        }
        if($time == ''){
            return 'N/A';
        }
        $time = round($totalTime/$count,1);
        return $time;
    }
}