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
class Magestore_Inventorywarehouse_Helper_Storepickup extends Mage_Core_Helper_Abstract {

    protected $_mapping = array(
        'warehouse_name' => 'store_name',
        'street' => 'address',
        'city' => 'city',
        'country_id' => 'country',
        'state' => 'state',
        'state_id' => 'state_id',
        'postcode' => 'zipcode',
        'manager_name' => 'store_manager',
        'telephone' => 'store_phone',
        'manager_email' => 'store_email',
    );
    protected $_selectedWarehouseId = null;

    /**
     * Convert data of store to warehouse
     * 
     * @param Varien_Object $store
     */
    public function storeToWarehouse($store) {
        $data = array();
        foreach ($this->_mapping as $field => $alias) {
            $data[$field] = $store->getData($alias);
        }
        return $data;
    }

    /**
     * Convert data of warehouse to store
     * 
     * @param Varien_Object $warehouse
     */
    public function warehouseToStore($warehouse) {
        $data = array();
        foreach ($this->_mapping as $field => $alias) {
            $data[$alias] = $warehouse->getData($field);
        }
        return $data;
    }

    /**
     * Update Store pickup Information
     * 
     * @param type $warehouse
     */
    public function updateStore($warehouse) {
        $this->stopSync('inventoryplus_warehouse');

        if (!$this->doSync('storepickup_store')) {
            return;
        }

        if (!Mage::helper('core')->isModuleEnabled('Magestore_Storepickup')) {
            return;
        }

        $store = Mage::getModel('storepickup/store')->load($warehouse->getStorepickupId());
        if (!$store->getId())
            return;
        try {
            $store->addData($this->warehouseToStore($warehouse))
                    ->save();
        } catch (Exception $e) {
            
        }
    }

    /**
     * Sync or not
     * 
     * @param string $objectName
     * @return boolean
     */
    public function doSync($objectName) {
        if (Mage::registry('in_sync_stop_' . $objectName)) {
            return false;
        }
        return true;
    }

    /**
     * Stop sync
     * 
     * @param string $objectName
     * @return \Magestore_Inventorywarehouse_Helper_Storepickup
     */
    public function stopSync($objectName) {
        if (!Mage::registry('in_sync_stop_' . $objectName)) {
            Mage::register('in_sync_stop_' . $objectName, true);
        }
        return $this;
    }

    /**
     * Get selected warehouse id based on storepickup id
     * 
     * @param \Mage_Sales_Model_Order $order
     * @return int
     */
    public function getSelectedWarehouseId($order) {
        if (!Mage::helper('core')->isModuleEnabled('Magestore_Storepickup')) {
            return $this->_selectedWarehouseId;
        }

        if (!$this->_selectedWarehouseId) {
            $data = Mage::getSingleton('checkout/session')->getData('storepickup_session');
            if (isset($data['store_id']) && $data['store_id']) {
                $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
                        ->addFieldToFilter('storepickup_id', $data['store_id'])
                        ->getFirstItem();
                $this->_selectedWarehouseId = $warehouse->getId();
            }
        }
        return $this->_selectedWarehouseId;
    }

}
