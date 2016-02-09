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
 * @package     Magestore_Inventorywebpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywebpos_Helper_Data extends Mage_Core_Helper_Abstract { 
	
	public function searchBarcode($searchTerm){
		$barcodeCol = Mage::getModel('inventorybarcode/barcode')->getCollection()
			->addFieldToFilter('barcode',$searchTerm);
		$barcode = $barcodeCol->getFirstItem();
		if($productId = $barcode->getProductEntityId())return $productId;
		else return 0;
	}
    public function removeTempTables($tempTableArr) {
        $coreResource = Mage::getSingleton('core/resource');
        $sql = "";
        foreach ($tempTableArr as $tempTable) {
            $sql .= "DROP TABLE  IF EXISTS " . $coreResource->getTableName($tempTable) . ";";
        }
        $coreResource->getConnection('core_write')->query($sql);
        return;
    }

    public function createTempTable($tempTable, $collection) {
        $coreResource = Mage::getSingleton('core/resource');
        $_temp_sql = "CREATE TEMPORARY TABLE " . $coreResource->getTableName($tempTable) . " ("; // CREATE TEMPORARY TABLE
        $_temp_sql .= $collection->getSelect()->__toString() . ");";
        $coreResource->getConnection('core_write')->query($_temp_sql);
        return;
    }

    public function getWarehouseProductCollection($warehouseSelected) {
		/* S: set default WID - Daniel */
		$warehouseSelected = ($warehouseSelected != null)?$warehouseSelected:$this->_getCurrentWarehouseId();
		/* E: set default WID - Daniel */
        $coreResource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToSelect(array('product_id', 'total_qty', 'available_qty'));
        $collection->addFieldToFilter('warehouse_id', $warehouseSelected);
        $collection->addFieldToFilter('available_qty', array('gt' => 0));
        return $collection;
    }

    public function _getCurrentWarehouseId() {
        $wid_session = Mage::getSingleton('core/session')->getCurrentWarehouseId();
        if ($wid_session)
            return $wid_session;
        else {
			/* S: Get webpos user id - Daniel */
			$currentUserId = Mage::getSingleton('webpos/session')->getUserId();
			/* E: Get webpos user id - Daniel */
            $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                    ->addFieldToFilter('user_id', $currentUserId)
                    ->addFieldToFilter('can_create_shipment', 1);
            $wid_col = $userCollection->getfirstItem()->getWarehouseId();
			Mage::getSingleton('core/session')->setCurrentWarehouseId($wid_col);
            return $wid_col;
        }
    }

    public function getWarehousesByWebposUser($currentUserId) {
        $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                ->addFieldToFilter('user_id', $currentUserId)
                ->addFieldToFilter('can_create_shipment', 1);
        $userCollection->getSelect()
                ->joinLeft(array('inventory_warehouse' => $userCollection->getTable('inventoryplus/warehouse')), "main_table.warehouse_id = inventory_warehouse.warehouse_id", array('*'));
        $warehouses = array();
        foreach ($userCollection as $user) {
            $warehouses[$user->getWarehouseId()] = $user->getWarehouseName();
        }
        return $warehouses;
    }

}
