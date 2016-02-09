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
 * @package     Magestore_Inventoryphysicalstocktaking
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryphysicalstocktaking Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * check permission
     * @return string
     */
    public function getPhysicalWarehouseByAdmin($warehouse = null) {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouseIds = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('can_physical', 1);
        if (count($collection) > 0) {
            foreach ($collection as $assignment) {
                $warehouseIds[] = $assignment->getWarehouseId();
            }
            $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('warehouse_id', array('in' => $warehouseIds));
            return $warehouseCollection;
        } else {
            return false;
        }
    }

    /**
     * check permission
     * @return string
     */
    public function getAdjustWarehouseByAdmin() {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouseIds = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('can_adjust', 1)
                ->addFieldToFilter('can_physical', 1);
        foreach ($collection as $assignment) {
            $warehouseIds[] = $assignment->getWarehouseId();
        }
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('warehouse_id', array('in' => $warehouseIds));
        return $warehouseCollection;
    }

    /**
     * get physical label status
     * @return string
     */
    public function getStatusPhysicalLabel($status) {
        $return = $this->__('Pending');
        if ($status == 1) {
            $return = $this->__('Completed');
        } else if ($status == 2) {
            $return = $this->__('Canceled');
        }
        return $return;
    }

    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', $data);
        }
    }

    public function getPhysicalPermission($data) {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouseIds = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('can_physical', 1)
                ->addFieldToFilter('warehouse_id', $data->getWarehouseId());
        if (count($collection) > 0) {
            return true;
        }
        return false;
    }

}
