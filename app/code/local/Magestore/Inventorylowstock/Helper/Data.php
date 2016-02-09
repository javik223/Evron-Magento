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
 * @package     Magestore_Inventorylowstock
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorylowstock Helper
 *
 * @category    Magestore
 * @package     Magestore_Inventorylowstock
 * @author      Magestore Developer
 */
class Magestore_Inventorylowstock_Helper_Data extends Magestore_Inventorysupplyneeds_Helper_Data {

    const WAREHOUSE_EMAIL = 1;
    const SYSTEM_EMAIL = 2;
    const PRIORITY_EMERGENCY = 1;
    const PRIORITY_NORMAL = 2;
    const IS_SUCCESS = 0;
    const IS_ERROR = 1;
    const XML_PATH_WAREHOUSE_EMAIL = 'inventoryplus/notice/warehouse_email';
    const XML_PATH_SYSTEM_EMAIL = 'inventoryplus/notice/system_email';

    /**
     * Get email subject
     *
     * @param $type
     * @return string
     */
    private function _getEmailSubject($type) {
        switch ($type) {
            case self::WAREHOUSE_EMAIL:
                return $this->__('Warehouse products are low');
            case self::SYSTEM_EMAIL:
                return $this->__('Products of system are low');
            default:
                break;
        }
    }

    /**
     * Send email
     *
     * @param $type
     * @param $subject
     * @param $recipientName
     * @param $recipientEmails
     * @param $url
     * @param null $warehouseName
     * @return int
     */
    private function _sendMail($type, $subject, $recipientName, $recipientEmails, $url, $warehouseName = null) {
        $storeId = Mage::app()->getStore()->getId();
        $template = Mage::getStoreConfig($type, $storeId);
        $mailTemplate = Mage::getModel('core/email_template');

        $translate = Mage::getSingleton('core/translate');

        $fromEmail = Mage::getStoreConfig('trans_email/ident_general/email'); //fetch sender email Admin
        if (!$fromEmail) {
            return self::IS_ERROR;
        }
        $fromName = Mage::getStoreConfig('trans_email/ident_general/name'); //fetch sender name Admin
        $sender = array('email' => $fromEmail, 'name' => $fromName);

        try {
            if ($type == 1) {
                $mailTemplate
                        ->setTemplateSubject($this->__('Warehouse products are low'))
                        ->sendTransactional(
                                $template, $sender, $recipientEmails, $recipientName, array(
                            'warehouse_name' => $warehouseName,
                            'manager_name' => $recipientName,
                            'link' => $url
                                )
                );
            } elseif ($type == 2) {
                $mailTemplate
                        ->setTemplateSubject($subject)
                        ->sendTransactional(
                                $template, $sender, $recipientEmails, $recipientName, array(
                            'manager_name' => $recipientName,
                            'link' => $url
                                )
                );
            }
            $translate->setTranslateInline(true);
            return self::IS_SUCCESS;
        } catch (Exception $e) {
            return self::IS_ERROR;
        }
    }

    /**
     * Create new notification log product object
     *
     * @param $type
     * @param $products
     * @param $logId
     * @param null $warehouse
     * @throws Exception
     */
    private function _createNotificationlogProduct($type, $products, $logId, $warehouse = null) {
        $now = Mage::getModel('core/date')->timestamp(time());
        foreach ($products as $product) {
            $productId = Mage::getModel("catalog/product")->getIdBySku($product['sku']);
            if(!$productId){
                continue;
            }
            try {
                Mage::getModel('inventorylowstock/notificationlog_product')
                        ->setData('product_id', $productId)
                        ->setData('send_email_log_id', $logId)
                        ->setData('qty_notify', ($type == self::WAREHOUSE_EMAIL) ? $product['available_qty_' . $warehouse->getId()] : $product['total_available_qty'])
                        ->setData('time_notify', date('Y-m-d H:i:s', $now))
                        ->save();
            } catch (Exception $e) {
                throw new Exception(Mage::helper('inventorylowstock')->__('Unable to save notification product.'));
            }
        }
    }

    /**
     * Get type of model in table send email log
     *
     * @param $type
     * @return int|string
     */
    private function _getTypeSendEmailLog($type) {
        switch ($type) {
            case self::WAREHOUSE_EMAIL:
                return $this->__('Warehouse');
            case self::SYSTEM_EMAIL:
                return $this->__('System');
            default:
                return self::IS_ERROR;
        }
    }

    /**
     * Create new send email log object
     *
     * @param $type
     * @param $recipientEmails
     * @param $recipientName
     * @param $url
     * @param $priority
     * @param string $warehouseName
     * @return int
     */
    private function _createSendEmailLog($type, $recipientEmails, $recipientName, $url, $priority, $warehouseName = '') {
        $now = Mage::getModel('core/date')->timestamp(time());
        $sendEmailLog = Mage::getModel('inventorylowstock/sendemaillog');
        try {
            $sendEmailLog->setData('sent_at', date('Y-m-d H:i:s', $now))
                    ->setData('type', $this->_getTypeSendEmailLog($type))
                    ->setData('email_received', $recipientEmails)
                    ->setData('warehouse_name', $warehouseName)
                    ->setData('manager_name', $recipientName)
                    ->setData('link', $url)
                    ->setData('status', 0)
                    ->setData('priority', $priority)
                    ->save();
            return $sendEmailLog->getId();
        } catch (Exception $e) {
            return self::IS_ERROR;
        }
    }

    /**
     * Send email to warehouse manager when there are low stock products in warehouse
     *
     * @param $warehouse
     * @param $warehouseProducts
     * @param $priority
     */
    public function sendWarehouseEmail($warehouse, $warehouseProducts, $priority) {
        // Get email subject
        $subject = $this->_getEmailSubject(self::WAREHOUSE_EMAIL);
        $recipientName = $warehouse->getManagerName();
        $recipientEmails = $warehouse->getManagerEmail();
        // Link to Warehouse detail
        $link = Mage::helper('adminhtml')->getUrl("adminhtml/inp_warehouse/edit", array('id' => $warehouse->getId(), 'loadptab' => true, 'uncheck_url_key' => true));
        // Get warehouse name
        $warehouseName = $warehouse->getWarehouseName();

        if (!$warehouse->getManagerEmail()) {
            $result = $this->_sendMail(self::XML_PATH_SYSTEM_EMAIL, $subject, $recipientName, $recipientEmails, $link, $warehouseName);
            if ($result == self::IS_ERROR) {
                return;
            }
        }

        $logId = $this->_createSendEmailLog(self::WAREHOUSE_EMAIL, $recipientEmails, $recipientName, $link, $priority, $warehouseName);
        $this->_createNotificationlogProduct(self::WAREHOUSE_EMAIL, $warehouseProducts, $logId, $warehouse);
    }

    /**
     * Send email to recipients list when there are low stock products in system
     *
     * @param $stockProducts
     * @param $priority
     */
    public function sendSystemEmail($stockProducts, $priority) {
        // Get email subject
        $subject = $this->_getEmailSubject(self::SYSTEM_EMAIL);
        $recipientName = $this->__('Managers');

        // Get list recipient emails from config
        $adminEmailsConfig = Mage::getStoreConfig('inventoryplus/notice/admin_email', Mage::app()->getStore()->getStoreId());

        // Remove space
        $adminEmailsConfig = str_replace(' ', '', $adminEmailsConfig);
        if (!$adminEmailsConfig)
            return;

        // Get array of emails from string
        $recipientEmails = explode(';', $adminEmailsConfig);
        if (empty($recipientEmails)) {
            return;
        }

        // Link to Catalog -> Manage Products
        $link = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/index', array('uncheck_url_key' => true));

        $result = $this->_sendMail(self::XML_PATH_SYSTEM_EMAIL, $subject, $recipientName, $recipientEmails, $link);
        if ($result == self::IS_ERROR) {
            return;
        }

        $logId = $this->_createSendEmailLog(self::SYSTEM_EMAIL, $adminEmailsConfig, $recipientName, $link, $priority);
        $this->_createNotificationlogProduct(self::SYSTEM_EMAIL, $stockProducts, $logId);
    }

    public function getCronExpression($oldTime, $oldDay, $oldMonth) {
        // Get config variable
        $times = Mage::getStoreConfig('inventoryplus/notice/time_updates');
        $dates = Mage::getStoreConfig('inventoryplus/notice/specificdays');
        $months = Mage::getStoreConfig('inventoryplus/notice/specificmonths');
        $dailyUpdate = Mage::getStoreConfig('inventoryplus/notice/daily_updates');
        $monthUpdate = Mage::getStoreConfig('inventoryplus/notice/monthly_updates');
        $now = Mage::getModel('core/date')->timestamp(time());

        //set time
        $timeUpdates = explode(',', $times);
        $positionTime = array_search($oldTime, $timeUpdates);

        $time = $timeUpdates[0];
        for ($i = $positionTime + 1; $i < count($timeUpdates); $i++) {
            if (!isset($timeUpdates[$i])) {
                $time = $timeUpdates[0];
            }
            if ($timeUpdates[$i] >= date('H', $now)) {
                $time = $timeUpdates[$i];
                break;
            } else {
                $time = $timeUpdates[0];
            }
        }

        //set day
        $setDay = '*';
        $dayList = explode(',', $dates);
        $positionDay = array_search($oldDay, explode(',', $dates));

        if ($dailyUpdate == 1) {
            $setDay = '*';
        } else {
            for ($i = $positionDay; $i < count($dayList); $i++) {
                if ((int) $dayList[$i] == (int) date('d', $now) && (int) $time >= (int) date('H', $now)) {
                    $setDay = $dayList[$i];
                    break;
                } else {
                    if (!isset($dayList[$i])) {
                        $setDay = $dayList[0];
                    }
                    if ($dayList[$i] >= date('d', $now)) {
                        $setDay = $dayList[$i];
                        break;
                    } else {
                        $setDay = $dayList[0];
                    }
                }
            }
        }

        //set month
        $setMonth = '*';
        $monthList = explode(',', $months);
        $positionMonth = array_search($oldMonth, explode(',', $months));
        if ($monthUpdate == 1) {
            $setMonth = '*';
        } else {
            for ($i = $positionMonth; $i < count($monthList); $i++) {
                if ((int) $monthList[$i] == (int) date('m', $now) && (int) $setDay >= (int) date('m', $now)) {
                    $setMonth = $monthList[$i];
                    break;
                } else {
                    if (!isset($monthList[$i])) {
                        $setMonth = $monthList[0];
                    }
                    if ($monthList[$i] >= date('m', $now)) {
                        $setMonth = $monthList[$i];
                        break;
                    } else {
                        $setMonth = $monthList[0];
                    }
                }
            }
        }

        return array(
            'time' => intval($time),
            'day' => $setDay,
            'month' => $setMonth
        );
    }

    public function getTypeList() {
        $options = array();

        $options[$this->__('System')] = $this->__('System');
        $options[$this->__('Warehouse')] = $this->__('Warehouse');


        return $options;
    }

    public function getWarehouseSelected() {
        $warehousesEnable = Mage::helper('inventoryplus/warehouse')->getAllWarehouseNameEnable();
        return array_keys($warehousesEnable);
    }

    public function getSupplierSelected() {
        $suppliersEnable = Mage::getModel('inventorypurchasing/supplier')->getCollection();  //supplyneed plugin depends on purchasing
        $suppliersEnable->addFieldToFilter('supplier_status', 1);
        $suppliers = array();
        foreach ($suppliersEnable as $supplier) {
            $suppliers[] = $supplier->getId();
        }
        return $suppliers;
    }

    public function getHistorySelected() {
        if (Mage::getStoreConfig('inventoryplus/notice/base_on_sales', Mage::app()->getStore()->getStoreId())) {
            $baseOnSales = Mage::getStoreConfig('inventoryplus/notice/base_on_sales', Mage::app()->getStore()->getStoreId());
            return $baseOnSales;
        }
        return 30;
    }

    public function getSalesFromTo() {
        $baseOnSales = $this->getHistorySelected();
        $data = array();
        $data['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00', '-' . $baseOnSales . ' days');
        $data['to'] = Mage::getModel('core/date')->date('Y-m-d 23:59:59');
        $data['count'] = $baseOnSales;

        return $data;
    }

    /**
     * Get *emergency* low stock date
     *
     * @return mixed
     */
    public function getForecastTo() {
        if (Mage::getStoreConfig('inventoryplus/notice/emergency_lowstock', Mage::app()->getStore()->getStoreId())) {
            $emergencyLowStock = Mage::getStoreConfig('inventoryplus/notice/emergency_lowstock', Mage::app()->getStore()->getStoreId());
            return Mage::getModel('core/date')->date('d-m-Y', '+' . $emergencyLowStock . ' days');
        }
        return Mage::getModel('core/date')->date('d-m-Y', '+30 days');
    }

    /**
     * Get number of days to *emergency* low stock date
     *
     * @return int|mixed
     */
    public function getNumberDaysForecast() {
        if (Mage::getStoreConfig('inventoryplus/notice/emergency_lowstock', Mage::app()->getStore()->getStoreId())) {
            $emergencyLowStock = Mage::getStoreConfig('inventoryplus/notice/emergency_lowstock', Mage::app()->getStore()->getStoreId());
            return $emergencyLowStock;
        }
        return 30;
    }

    /**
     * Get *notice* low stock date
     *
     * @return mixed
     */
    public function getNormalLowStock() {
        if (Mage::getStoreConfig('inventoryplus/notice/forecast_to', Mage::app()->getStore()->getStoreId())) {
            $forecastTo = Mage::getStoreConfig('inventoryplus/notice/forecast_to', Mage::app()->getStore()->getStoreId());
            return Mage::getModel('core/date')->date('d-m-Y', '+' . $forecastTo . ' days');
        }
        return Mage::getModel('core/date')->date('d-m-Y', '+30 days');
    }

    /**
     * Get number of days to *notice* low stock date
     *
     * @return int|mixed
     */
    public function getNumberDaysNormalLowStock() {
        if (Mage::getStoreConfig('inventoryplus/notice/forecast_to', Mage::app()->getStore()->getStoreId())) {
            $forecastTo = Mage::getStoreConfig('inventoryplus/notice/forecast_to', Mage::app()->getStore()->getStoreId());
            return $forecastTo;
        }
        return 30;
    }

    public function getRatePurchaseMore() {
        return '100';
    }

    public function hasSupplier() {
        $colection = Mage::getModel('inventorypurchasing/supplier')->getCollection();
        if ($colection->getSize() > 0)
            return true;
        return false;
    }

}
