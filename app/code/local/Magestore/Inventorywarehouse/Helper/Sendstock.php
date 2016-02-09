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
class Magestore_Inventorywarehouse_Helper_Sendstock extends Mage_Core_Helper_Abstract {

    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('sendstock_product_import', $data);
        } else {
            Mage::getModel('admin/session')->setData('null_sendstock_product_import', 1);
        }
    }

    public function checkCancelSendstock($id) {
        $store = Mage::app()->getStore();
        $days = 24 * 60 * 60 * Mage::getStoreConfig('inventoryplus/transaction/cancel_time', $store->getId());
        $sendStock = Mage::getModel('inventorywarehouse/sendstock')->load($id);
        $createdAt = strtotime($sendStock->getCreatedAt()) + $days;
        $now = strtotime(now("y-m-d"));
        $warehouseId = $sendStock->getWarehouseIdTo();
        $admin = Mage::getSingleton('admin/session')->getUser();

        if ($warehouseId && Mage::helper('inventoryplus/warehouse')->canSendAndRequest($admin->getId(), $warehouseId)) {
            if (($sendStock->getStatus() == 1) && ($createdAt > $now)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check permission to adjust stock.
     * 
     * @return boolean
     */
    public function getWarehouseByAdmin() {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouseIds = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('can_send_request_stock', 1);
        foreach ($collection as $assignment) {
            $warehouseIds[] = $assignment->getWarehouseId();
        }
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->addFieldToFilter('warehouse_id', array('in' => $warehouseIds));
        if (count($warehouseCollection)) {
            return true;
        }
        return false;
    }

}
