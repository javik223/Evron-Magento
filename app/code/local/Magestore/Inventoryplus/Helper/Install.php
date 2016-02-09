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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Helper_Install extends Mage_Core_Helper_Abstract {

    /**
     * Check if is there any warehouse existed
     * 
     * @return boolean
     */
    public function isWarehouseExist() {
        $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection();
        if ($warehouses->getSize() > 0) {
            return true;
        }
        return false;
    }


    /**
     * 	Create a Default warehouse for Inventory.
     */
    public function createDefaultWarehouse() {
        $countryDefault = Mage::getStoreConfig('general/country/default');
        $warehouse = Mage::getModel('inventoryplus/warehouse');
        $admin = Mage::getModel('admin/session')->getUser()->getUsername();
        $warehouse->setWarehouseName('Primary Location')
                ->setStatus(1)
                ->setCreatedBy($admin)
                ->setCreatedAt(now())
                ->setCountryId($countryDefault)
                ->setIsRoot(1)
                ->setManager($admin)
                ->save();
        return;
    }

    /**
     * 	Insert admins permission for Default warehouse (All admins have all permissions).
     */
    public function addDefaultWarehousePermission() {
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
        if ($warehouse->getId()) {
            $adminIds = Mage::getModel('admin/user')->getCollection()->getAllIds();
            foreach ($adminIds as $adminId) {
                $permission = Mage::getModel('inventoryplus/warehouse_permission');
                $permission->setData('warehouse_id', $warehouse->getId())
                        ->setData('admin_id', $adminId)
                        ->setData('can_edit_warehouse', 1)
                        ->setData('can_adjust', 1)
                        ->setData('can_physical', 1)
                        ->setData('can_send_request_stock', 1)
                        ->setData('can_purchase_product', 1);
                try {
                    $permission->save();
                } catch (Exception $e) {
                    
                }
            }
        }
        return;
    }

}
