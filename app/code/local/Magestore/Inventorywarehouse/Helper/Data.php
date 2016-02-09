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
class Magestore_Inventorywarehouse_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getIncrementId($object) {
        $id = $object->getId();
        $len = strlen($id);
        $zeros = 10 - $len;

        $incrementId = '';
        for ($i = 1; $i < $zeros; $i++) {
            if ($i == 1) {
                $incrementId .= '1';
            } else {
                $incrementId.='0';
            }
        }
        $incrementId .= $id;
        if ($object instanceof Magestore_Inventory_Model_Stockissuing) {
            $incrementId = 'SI' . $incrementId;
        } elseif ($object instanceof Magestore_Inventory_Model_Stockreceiving) {
            $incrementId = 'SR' . $incrementId;
        } elseif ($object instanceof Magestore_Inventory_Model_Stocktransfering) {
            $incrementId = 'ST' . $incrementId;
        }
        return $incrementId;
    }
    
    public function getWarehouseNames() {
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection();
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getWarehouseName()] = $warehouse->getWarehouseName();
        }
        return $warehouses;
    }
    
    public function getAllWarehouseSendstock(){
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection();
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getWarehouseName()] = $warehouse->getWarehouseName();
        }
        if(empty($warehouses['Others']))
            $warehouses['Others']  = 'Others';
        return $warehouses;
    }
    
    public function getAllWarehouseSendstockforDestination(){
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection()->addFieldToFilter('status',1);
       
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        if(empty($warehouses['others']))
            $warehouses['others']  = 'Others';
        return $warehouses;
    }
    
    public function getAllWarehouseRequeststock(){
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection()->addFieldToFilter('status',1);
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        if(empty($warehouses['others']))
            $warehouses['others']  = 'Others';
        return $warehouses;
    }
	
	public function getAllWarehouseRequeststockGrid(){
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection();
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getWarehouseName()] = $warehouse->getWarehouseName();
        }
        if(empty($warehouses['Others']))
            $warehouses['Others']  = 'Others';
        return $warehouses;
    }
    
    public function getWarehouseByAdmin() {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $collection = $this->loadTransferAbleWarehouses($admin);
        $warehouses = array();
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        return $warehouses;
    }
    
    public function loadTransferAbleWarehouses($admin) {
        $warehouses = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $admin->getId())
                ->addFieldToFilter('can_send_request_stock', 1);
        foreach ($collection as $assignment) {
            $warehouses[] = $assignment->getWarehouseId();
        }
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->addFieldToFilter('warehouse_id', array('in' => $warehouses))
                ->addFieldToFilter('status',1);
        return $warehouseCollection;
    }
    
    public function getAllWarehouseSendstockWithId(){
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection()->addFieldToFilter('status',1);
       
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        if(empty($warehouses['others']))
            $warehouses['others']  = 'Others';
        return $warehouses;
    }
    
    public function canTransfer($adminId, $warehouseId) {
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('warehouse_id', $warehouseId)
        ;
        if ($collection->getSize()) {
            if ($collection->getFirstItem()->getCanSendRequestStock() == 1) {
                return true;
            }
        }
        return false;
    }
    
    public function getTransactionType() {
        return array(
            1 => $this->__('Send stock to another Warehouse or other destination'),
            2 => $this->__('Receive stock from another Warehouse or other source'),
            3 => $this->__('Receive stock from Purchase Order Delivery'),
            4 => $this->__('Send stock to Supplier for Return Order'),
            5 => $this->__('Send stock to Customer for Shipment'),
            6 => $this->__('Receive stock from Customer Refund'),
        );
    }
    
    // Find warehouse enough stock to ship
    // return warehouse_id , 0 if no one possible
    public function selectWarehouseToShip($product_id,$qty){        
        $warehouse = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()                    
                    ->addFieldToFilter('product_id', $product_id)
                    ->addFieldToFilter('total_qty', array('gteq' => $qty))
                    ->getFirstItem();        
        if($warehouse){
            return $warehouse->getWarehouseId();
        } else return 0;
    }    
    // Check shipment when create invoice
    // return boolean
    public function checkShipment($items){                
        if(!is_array($items)){                       
            return 0;
        }               
        foreach($items as $item_id => $qty){
            $item = Mage::getModel('sales/order_item')->load($item_id);
            $product_id = $item->getProductId();
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
            $manageStock = $stockItem->getManageStock();
            if ($stockItem->getUseConfigManageStock()) {
                $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
            }
            if (!$manageStock) {
                continue;
            }
            if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped', 'virtual', 'downloadable')))
                continue;
            
            if($this->selectWarehouseToShip($product_id,$qty) == 0){                
                return 0;
            };
        }
        return 1;
    }
    
}