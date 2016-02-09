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
 * Inventorylowstock Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorylowstock
 * @author      Magestore Developer
 */
class Magestore_Inventorylowstock_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Template
{

    const PRIORITY_EMERGENCY = 1;
    const PRIORITY_NORMAL = 2;

    /**
     * Get low stock message for system when cron is running
     *
     * @return array
     */
    public function getSystemNotice()
    {
        $messages = array();
        $oldEmailLogs = Mage::getModel('inventorylowstock/sendemaillog')->getCollection()
            ->addFieldToFilter('status', 0)
            ->addFieldToFilter('type', 'System')
            ->addFieldToFilter('priority', self::PRIORITY_EMERGENCY);
        if ($oldEmailLogs->getSize()) {
            foreach ($oldEmailLogs as $oldEmailLog) {
                $url = Mage::helper('adminhtml')->getUrl('adminhtml/inl_notificationlog/view', array('id' => $oldEmailLog->getId()));

                $messages[] = $this->__('Your system is running out of stock on some products. ') . '  <a href="' . $url . '">' . $this->__('(Click here)') . '</a>' . $this->__(' to view details.') . '<br/>';
            }
        }

        return $messages;
    }

    /**
     * Get low stock message for warehouse when cron is running
     *
     * @return array
     */
    public function getWarehouseNotice()
    {
        $messages = array();
        $oldEmailLogs = Mage::getModel('inventorylowstock/sendemaillog')->getCollection()
            ->addFieldToFilter('status', 0)
            ->addFieldToFilter('type', 'Warehouse')
            ->addFieldToFilter('priority', self::PRIORITY_EMERGENCY);
        if ($oldEmailLogs->getSize()) {
            foreach ($oldEmailLogs as $oldEmailLog) {
                $url = Mage::helper('adminhtml')->getUrl('adminhtml/inl_notificationlog/view', array('id' => $oldEmailLog->getId()));
                $messages[] = $this->__("Warehouse <b>" . $oldEmailLog->getWarehouseName() . "</b> is running out of stock on some products! ") . '  <a href="' . $url . '">' . $this->__('(Click here)') . '</a>' . $this->__('  to view details.') . '<br/>';
            }
        }
        return $messages;
    }

    /**
     * Get low stock message for both system and warehouses
     *
     * @return array
     */
    public function getBothNotice() {
        $messages = array();
        $warehousenotices = $this->getWarehouseNotice();
        $systemnotices = $this->getSystemNotice();
        foreach ($warehousenotices as $wn) {
            $messages[] = $wn;
        }
        foreach ($systemnotices as $sn) {
            $messages[] = $sn;
        }
        return $messages;
    }

    /**
     * Get message when no supplier is found
     *
     * @return array
     */
    public function getMessageNoSupplier() {
        $messages = array();
        $messages[] = $this->__('You need to add supplier before using the Low Stock feature.');
        return $messages;
    }

    public function getSystemNoticeNormal()
    {
        $messages = array();
        $oldEmailLogs = Mage::getModel('inventorylowstock/sendemaillog')->getCollection()
            ->addFieldToFilter('status', 0)
            ->addFieldToFilter('type', 'System')
            ->addFieldToFilter('priority', self::PRIORITY_NORMAL);
        if ($oldEmailLogs->getSize()) {
            foreach ($oldEmailLogs as $oldEmailLog) {
                $url = Mage::helper('adminhtml')->getUrl('adminhtml/inl_notificationlog/view', array('id' => $oldEmailLog->getId()));

                $messages[] = $this->__('Your system is running out of stock on some products. ') . '  <a href="' . $url . '">' . $this->__('(Click here)') . '</a>' . $this->__(' to view details.') . '<br/>';
            }
        }

        return $messages;
    }

    public function getWarehouseNoticeNormal()
    {
        $messages = array();
        $oldEmailLogs = Mage::getModel('inventorylowstock/sendemaillog')->getCollection()
            ->addFieldToFilter('status', 0)
            ->addFieldToFilter('type', 'Warehouse')
            ->addFieldToFilter('priority', self::PRIORITY_NORMAL);
        if ($oldEmailLogs->getSize()) {
            foreach ($oldEmailLogs as $oldEmailLog) {
                $url = Mage::helper('adminhtml')->getUrl('adminhtml/inl_notificationlog/view', array('id' => $oldEmailLog->getId()));
                $messages[] = $this->__("Warehouse <b>" . $oldEmailLog->getWarehouseName() . "</b> is running out of stock on some products! ") . '  <a href="' . $url . '">' . $this->__('(Click here)') . '</a>' . $this->__('  to view details.') . '<br/>';
            }
        }
        return $messages;
    }

    public function getBothNoticeNormal() {
        $messages = array();
        $warehousenotices = $this->getWarehouseNoticeNormal();
        $systemnotices = $this->getSystemNoticeNormal();
        foreach ($warehousenotices as $wn) {
            $messages[] = $wn;
        }
        foreach ($systemnotices as $sn) {
            $messages[] = $sn;
        }
        return $messages;
    }
}