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
class Magestore_Inventorywarehouse_Helper_Requeststock extends Mage_Core_Helper_Abstract {

    public function checkCancelRequeststock($id) {
        $store = Mage::app()->getStore();
        $days = 24 * 60 * 60 * Mage::getStoreConfig('inventoryplus/transaction/cancel_time', $store->getId());
        $requestStock = Mage::getModel('inventorywarehouse/requeststock')->load($id);
        $createdAt = strtotime($requestStock->getCreatedAt()) + $days;
        $now = strtotime(now("y-m-d"));
        $warehouseId = $requestStock->getWarehouseIdFrom();
        $admin = Mage::getSingleton('admin/session')->getUser();
        if ($warehouseId && Mage::helper('inventoryplus/warehouse')->canSendAndRequest($admin->getId(), $warehouseId)) {
            if (($requestStock->getStatus() == 1) && ($createdAt > $now))
                return true;
        }
        return false;
    }

}
