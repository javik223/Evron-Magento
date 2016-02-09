<?php

class Magestore_Inventorylowstock_Model_Notification
{

    const CRON_STRING_PATH = 'crontab/jobs/magestore_inventorylowstock/schedule/cron_expr';
    const CRON_STRING_PATH_RUN = 'crontab/jobs/magestore_inventorylowstock/run/model';
    const PRIORITY_EMERGENCY = 1;
    const PRIORITY_NORMAL = 2;

    public function notification()
    {
        if (!Mage::getStoreConfig('inventoryplus/notice/stock_notice', Mage::app()->getStore()->getStoreId())) {
            return;
        }
        if (!Mage::helper('inventorylowstock')->hasSupplier()) {
            return;
        }

        // Get variables
        $enable = Mage::getStoreConfig('inventoryplus/notice/stock_notice', Mage::app()->getStore()->getStoreId());
        $emailNoticeAdmin = Mage::getStoreConfig('inventoryplus/notice/email_notice', Mage::app()->getStore()->getStoreId());
        $notice_for = Mage::getStoreConfig('inventoryplus/notice/notice_for', Mage::app()->getStore()->getStoreId());
        $now = Mage::getModel('core/date')->timestamp(time());

        $time = 0;
        $setDay = 0;
        $setMonth = 0;
        $result = array();

        $notificationLog = Mage::getModel('inventorylowstock/notificationlog')->getCollection();
        if ($notificationLog->getSize() != 0) {
            $lastNotification = $notificationLog->getLastItem();
            if ($lastNotification->getId() && $lastNotification->getStatus() == 0) {
                $time = $lastNotification->getCurrentTime();
                $setDay = $lastNotification->getCurrentDay();
                $setMonth = $lastNotification->getCurrentMonth();
                $result = Mage::helper('inventorylowstock')->getCronExpression(
                    $time,
                    $setDay,
                    $setMonth
                );
                $lastNotification->setData('status', 1)->save();
                $oldEmailLogs = Mage::getModel('inventorylowstock/sendemaillog')->getCollection()
                    ->addFieldToFilter('status', 0);
                foreach ($oldEmailLogs as $oldEmailLog) {
                    $oldEmailLog->setData('status', 1)->save();
                }
            }
        } else {
            $time = date('H', $now);
            $setDay = date('d', $now);
            $setMonth = date('m', $now);
            $result = Mage::helper('inventorylowstock')->getCronExpression(
                date('H', $now),
                date('d', $now),
                date('m', $now)
            );
        }

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

        $cronExprArray = array(
            intval(0),
            $result['time'],
            $result['day'],
            $result['month'],
            '*',
        );
        $cronExprString = join(' ', $cronExprArray);
        try {
            Mage::getModel('inventorylowstock/notificationlog')
                ->setData('current_time', $time)
                ->setData('current_day', $setDay)
                ->setData('current_month', $setMonth)
                ->setData('status', 0)
                ->setData('last_update', date('Y-m-d H:i:s', $now))
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('inventorylowstock')->__('Unable to save notification log.'));
        }
        try {
            Mage::getModel('core/config')->saveConfig(self::CRON_STRING_PATH, $cronExprString);
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }

}
