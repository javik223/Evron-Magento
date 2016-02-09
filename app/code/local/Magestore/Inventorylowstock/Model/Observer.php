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
 * @package     Magestore_Inventorypurchasing
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorypurchasing Observer Model
 *
 * @category    Magestore
 * @package     Magestore_Inventorypurchasing
 * @author      Magestore Developer
 */
class Magestore_Inventorylowstock_Model_Observer {

    const CRON_STRING_PATH = 'crontab/jobs/magestore_inventorylowstock/schedule/cron_expr';
    const CRON_STRING_PATH_RUN = 'crontab/jobs/magestore_inventorylowstock/run/model';
    const PRIORITY_EMERGENCY = 1;
    const PRIORITY_NORMAL = 2;

    public function stockChange($observer) {
        $insertSql = '';
        $stockItem = $observer->getDataObject();
        if (!$stockItem->getManageStock())
            return;
        if (in_array($stockItem->getProductTypeId(), array('configurable', 'bundle', 'grouped', 'virtual', 'downloadable')))
            return;
        $isInStock = $stockItem->getIsInStock();
        $productId = $stockItem->getProductId();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $sql = 'SELECT * from ' . $resource->getTableName('erp_inventory_outofstock_tracking') . ' WHERE (`product_id` = ' . $productId . ' AND `instock_date` IS NULL)';
        $results = $readConnection->fetchAll($sql);
        if (count($results) == 0 && $isInStock)
            return;
        if (count($results) == 0 && !$isInStock) {
            $insertSql .= ' INSERT INTO ' . $resource->getTableName('erp_inventory_outofstock_tracking') . ' (`product_id`, `outofstock_date`)' . " VALUES ('" . $productId . "', '" . now() . "');";
            $writeConnection->query($insertSql);
        }
        if (count($results) != 0 && $isInStock) {
            $updateSql = 'UPDATE ' . $resource->getTableName("erp_inventory_outofstock_tracking") . ' SET instock_date = \'' . now() . '\' WHERE oos_tracking_id = ' . $results[0]["oos_tracking_id"];
            $writeConnection->query($updateSql);
        }
        if (count($results) != 0 && !$isInStock)
            return;
    }

    public function coreConfigDataSaveAfter($observer) {
        if (Mage::app()->getRequest()->getParam('section') != 'inventoryplus')
            return;

        $configData = $observer->getEvent()->getConfigData();
        $useCron = $configData->getData('groups/notice/fields/use_cron');
        if ($useCron['value'] != 1) {
            return;
        }
        $now = Mage::getModel('core/date')->timestamp(time());
        $daylyUpdates = $configData->getData('groups/notice/fields/daily_updates/value');
        $specificDays = $configData->getData('groups/notice/fields/specificdays/value');
        $monthlyUpdates = $configData->getData('groups/notice/fields/monthly_updates/value');
        $specificMonths = $configData->getData('groups/notice/fields/specificmonths/value');
        $times = $configData->getData('groups/notice/fields/time_updates/value');
        $oldTime = 0;
        $i = 0;

        foreach ($times as $time) {
            if ((int) $time > (int) date('H', $now)) {        //If hour for cronning bigger than current hour.
                if ($i == 0) {
                    $oldTime = (int) $time;
                }
                $i++;
                if (((int) $time - (int) date('H', $now) <= (int) $oldTime - (int) date('H', $now))) {
                    $oldTime = (int) $time;
                }
            }
            if ((int) $times[count($times) - 1] <= (int) date('H', $now)) {
                if ($i == 0) {
                    $oldTime = (int) $time;
                }
                $i++;
                if (((int) $time - (int) date('H', $now) <= (int) $oldTime - (int) date('H', $now))) {
                    $oldTime = (int) $time;
                }
            }
        }
        if ((int) $oldTime < (int) date('H', $now)) {
            $oldTime = (int) $times[0];
        }
        $setTime = $oldTime;
        //set day
        if ($daylyUpdates == 1) {
            $setDay = '*';
        } else {
            $j = 0;
            $oldDay = 0;
            foreach ($specificDays as $specificDay) {
                if ((int) $specificDay == (int) date('d', $now) && (int) $setTime >= (int) date('H', $now)) {
                    $oldDay = $specificDay;
                    break;
                }
                if ((int) $specificDay >= (int) date('d', $now)) {
                    if ($j == 0) {
                        $oldDay = (int) $specificDay;
                    }
                    $j++;
                    if (((int) $specificDay - (int) date('d', $now) <= (int) $oldDay - (int) date('d', $now))) {
                        $oldDay = (int) $specificDay;
                    }
                }

                if ((int) $specificDays[count($specificDays) - 1] <= (int) date('d', $now)) {
                    if ($j == 0) {
                        $oldDay = (int) $specificDay;
                    }
                    $j++;
                    if (((int) $specificDay - (int) date('d', $now) <= (int) $oldDay - (int) date('d', $now))) {
                        $oldDay = (int) $specificDay;
                    }
                }
            }

            $setDay = $oldDay;
        }
//set month        
        if ($monthlyUpdates == 1) {
            $setMonth = '*';
        } else {
            $k = 0;
            $oldMonth = 0;
            foreach ($specificMonths as $specificMonth) {
                if ((int) $specificMonth == (int) date('m', $now) && (int) $setDay >= (int) date('d', $now)) {
                    $oldMonth = $specificMonth;
                    break;
                }
                if ((int) $specificMonth >= (int) date('m', $now)) {
                    if ($j == 0) {
                        $oldMonth = (int) $specificMonth;
                    }
                    $j++;
                    if (((int) $specificMonth - (int) date('m', $now) <= (int) $oldMonth - (int) date('m', $now))) {
                        $oldMonth = (int) $specificMonth;
                    }
                }

                if ((int) $specificMonths[count($specificMonths) - 1] <= (int) date('m', $now)) {
                    if ($j == 0) {
                        $oldMonth = (int) $specificMonth;
                    }
                    $j++;
                    if (((int) $specificMonth - (int) date('m', $now) <= (int) $oldMonth - (int) date('m', $now))) {
                        $oldMonth = (int) $specificMonth;
                    }
                }
            }
            $setMonth = $oldMonth;
        }
        /* Changed by Magnus - Re-change setTime */
        $times = $configData->getData('groups/notice/fields/time_updates/value');
        if (count($times) >= 24) {
            $setTime = '*';
        } else {
            $setTime = implode(',', $times);
        }
        $cronExprArray = array(
            intval(0),
            $setTime,
            $setDay,
            $setMonth,
            '*',
        );
        /* End changed by Magnus */
        $cronExprString = join(' ', $cronExprArray);
        $session = Mage::getSingleton('adminhtml/session');
        $session->setData('inventory_notification_time', $setTime);
        $session->setData('inventory_notification_day', $setDay);
        $session->setData('inventory_notification_month', $setMonth);
        try {
            Mage::getModel('core/config')->saveConfig(self::CRON_STRING_PATH, $cronExprString);
            Mage::getModel('core/config')->saveConfig(self::CRON_STRING_PATH_RUN, 'inventorylowstock/notification::notification');
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_notification.log');
        }
    }

    public function adminSystemConfigChangedSectionInventoryplus($observer) {
        $session = Mage::getSingleton('adminhtml/session');
        $setTime = $session->getData('inventory_notification_time');
        $setDay = $session->getData('inventory_notification_day');
        $setMonth = $session->getData('inventory_notification_month');

        $notificationLog = Mage::getModel('inventorylowstock/notificationlog')
                ->setData('current_time', $setTime)
                ->setData('current_day', $setDay)
                ->setData('current_month', $setMonth)
                ->setData('status', 0)
                ->setData('last_update', now())
                ->save();
        $session->unsetData('inventory_notification_time');
        $session->unsetData('inventory_notification_day');
        $session->unsetData('inventory_notification_month');
    }

    public function adminSessionUserLoginSuccess($observer) {
        if (!Mage::getStoreConfig('inventoryplus/notice/stock_notice')) {
            return;
        }
        if (Mage::getStoreConfig('inventoryplus/notice/use_cron')) {
            return;
        }
        try {
            // Mark other notification logs as old
            $notificationLogs = Mage::getModel('inventorylowstock/notificationlog')->getCollection()
                    ->addFieldToFilter('status', 0);
            foreach ($notificationLogs as $notificationLog) {
                $notificationLog->setData('status', 1)->save();
            }

            // Mark all send email log as old
            $oldEmailLogs = Mage::getModel('inventorylowstock/sendemaillog')->getCollection()
                    ->addFieldToFilter('status', 0);
            foreach ($oldEmailLogs as $oldEmailLog) {
                $oldEmailLog->setData('status', 1)->save();
            }

            $emailNoticeAdmin = Mage::getStoreConfig('inventoryplus/notice/email_notice');
            $notice_for = Mage::getStoreConfig('inventoryplus/notice/notice_for');
            // notify low stock for warehouse
            if ($notice_for == 1 || $notice_for == 3) {
                $dataClass = new Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Gridexport;
                $helperClass = Mage::helper('inventorylowstock');
                $dataClass->_prepareCollectionInContruct($helperClass);
                $warehouseProducts = $dataClass->getCollectionGrid();
                $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection();
                foreach ($warehouses as $warehouse) {
                    $emergencyLowStockProducts = array();
                    $normalLowStockProducts = array();
                    foreach ($warehouseProducts as $warehouseProduct) {
                        if (intval($warehouseProduct['supplyneeds_' . $warehouse->getId()]) > 0) {
                            array_push($emergencyLowStockProducts, $warehouseProduct);
                        } else {
                            if (intval($warehouseProduct['out_of_stock_date_' . $warehouse->getId()]) <= Mage::helper('inventorylowstock')->getNormalLowStock()) {
                                array_push($normalLowStockProducts, $warehouseProduct);
                            }
                        }
                    }
                    if (count($emergencyLowStockProducts) > 0) {
                        Mage::helper('inventorylowstock')->sendWarehouseEmail($warehouse, $emergencyLowStockProducts, self::PRIORITY_EMERGENCY);
                    }
                    if (count($normalLowStockProducts) > 0) {
                        Mage::helper('inventorylowstock')->sendWarehouseEmail($warehouse, $normalLowStockProducts, self::PRIORITY_NORMAL);
                    }
                }
            }

            // notify low stock for admin
            if ($notice_for == 2 || $notice_for == 3) {
                $dataClass = new Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid;
                $helperClass = Mage::helper('inventorylowstock');
                $dataClass->_prepareCollectionInContruct($helperClass);
                $stockProducts = $dataClass->getCollectionGrid();
                $emergencyLowStockProducts = array();
                $normalLowStockProducts = array();
                foreach ($stockProducts as $stockProduct) {
                    if (intval($stockProduct['supplyneeds']) > 0) {
                        array_push($emergencyLowStockProducts, $stockProduct);
                    } else {
                        if (intval($stockProduct['out_of_stock_date']) <= Mage::helper('inventorylowstock')->getNormalLowStock()) {
                            array_push($normalLowStockProducts, $stockProduct);
                        }
                    }
                }
                if (count($emergencyLowStockProducts) > 0) {
                    if ($emailNoticeAdmin)
                        Mage::helper('inventorylowstock')->sendSystemEmail($emergencyLowStockProducts, self::PRIORITY_EMERGENCY);
                }
                if (count($normalLowStockProducts) > 0) {
                    if ($emailNoticeAdmin) {
                        Mage::helper('inventorylowstock')->sendSystemEmail($normalLowStockProducts, self::PRIORITY_NORMAL);
                    }
                }
            }

            // Create new notification log record
            $now = Mage::getModel('core/date')->timestamp(time());

            Mage::getModel('inventorylowstock/notificationlog')
                    ->setData('current_time', date('H', $now))
                    ->setData('current_day', date('d', $now))
                    ->setData('current_month', date('m', $now))
                    ->setData('status', 0)
                    ->setData('last_update', date('Y-m-d H:i:s', $now))
                    ->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_notification.log');
        }
    }

}
