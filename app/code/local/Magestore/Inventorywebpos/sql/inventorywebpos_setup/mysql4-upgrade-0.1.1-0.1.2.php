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
/** @var $installer Mage_Core_Model_Resource_Setup */

if (Mage::app()->getStore()->isAdmin()) {
    $installer = $this;
    $installer->startSetup();
    $resource = Mage::getModel('core/resource');

// Check if module Webpos is installed, convert location to warehouse

    if (Mage::helper('core')->isModuleEnabled('Magestore_Webpos')) {
        $webposUserLocation = Mage::getModel('webpos/userlocation')->getCollection();
        $currentAdmin = Mage::getSingleton('admin/session')->getUser();
        if ($webposUserLocation->getFirstItem()->getId()) {
            $countryDefault = Mage::getStoreConfig('general/country/default');
            foreach ($webposUserLocation as $location) {
                $newConvertWarehouse = Mage::getModel('inventoryplus/warehouse');
                $newConvertWarehouse->setWarehouseName($location->getDisplayName());
                $newConvertWarehouse->setStatus(1);
                if ($currentAdmin->getId()) {
                    $newConvertWarehouse->setCreatedBy($currentAdmin->getUsername());
                } else {
                    $newConvertWarehouse->setCreatedBy(Mage::helper('inventoryplus')->__('Webpos Location'));
                }
                $newConvertWarehouse->setCreatedAt(now());
                $newConvertWarehouse->setCountryId($countryDefault);
                $newConvertWarehouse->save();

                if ($newConvertWarehouse->getId()) {
                    $permission = Mage::getModel('inventoryplus/warehouse_permission');
                    $permission->setData('warehouse_id', $newConvertWarehouse->getId())
                            ->setData('admin_id', $currentAdmin->getId())
                            ->setData('can_edit_warehouse', 1)
                            ->setData('can_adjust', 1);
                    try {
                        $permission->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }

// End

    $installer->endSetup();
}