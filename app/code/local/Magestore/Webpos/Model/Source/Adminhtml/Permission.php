<?php

class Magestore_Webpos_Model_Source_Adminhtml_Permission extends Varien_Object {

    const ALL_PERMISSION = 1;
    const CREATE_ORDER = 2;
    const VIEW_ORDER_THIS_USER = 3;
    const VIEW_ORDER_OTHER_STAFF = 4;
    const VIEW_ORDER_ALL_ORDER = 5;
    const MANAGE_ORDER_THIS_USER = 6;
    const MANAGE_ORDER_OTHER_STAFF = 7;
    const MANAGE_ORDER_ALL_ORDER = 8;
    const CAN_USE_CART_CUSTOM_DISCOUNT = 9;
    const CAN_USE_CART_COUPON_CODE = 10;
    const CAN_USE_DISCOUNT_PER_ITEM = 11;
    const CAN_USE_CUSTOM_PRICE = 12;
    const CAN_USE_All_DISCOUNT = 13;
    const CAN_USE_XREPORT = 14;
    const CAN_USE_ZREPORT = 15;
    const CAN_USE_EODREPORT = 16;
    const CAN_USE_ALL_REPORT = 17;

    static public function getOptionArray() {

        return array(
            '1' => Mage::helper('webpos')->__('All Permissions'),
            '2' => Mage::helper('webpos')->__('Create Orders'),
            '3' => Mage::helper('webpos')->__('View orders created by this user'),
            '4' => Mage::helper('webpos')->__('View orders created by other users'),
            '5' => Mage::helper('webpos')->__('View all orders'),
            '6' => Mage::helper('webpos')->__('Manage orders created by this user'),
            '7' => Mage::helper('webpos')->__('Manage orders created by other users'),
            '8' => Mage::helper('webpos')->__('Manage all orders'),
            '9' => Mage::helper('webpos')->__('Apply custom discount per cart'),
            '10' => Mage::helper('webpos')->__('Apply coupon codes'),
            '11' => Mage::helper('webpos')->__('Apply custom discount per item'),
            '12' => Mage::helper('webpos')->__('Apply custom price'),
            '13' => Mage::helper('webpos')->__('Apply all discounts'),
            '14' => Mage::helper('webpos')->__('X-Report (mid-day report)'),
            '15' => Mage::helper('webpos')->__('Z-Report (end-of-day report)'),
            '16' => Mage::helper('webpos')->__('Daily POS Orders Summary'),
            '17' => Mage::helper('webpos')->__('All Reports')
        );
    }

    static public function toOptionArray() {
        $options = array(
            array('value' => '1', 'label' => Mage::helper('webpos')->__('All Permissions')),
            array('value' => '2', 'label' => Mage::helper('webpos')->__('Create Orders')),
            array('value' => '3', 'label' => Mage::helper('webpos')->__('View orders created by this user')),
            array('value' => '4', 'label' => Mage::helper('webpos')->__('View orders created by other users')),
            array('value' => '5', 'label' => Mage::helper('webpos')->__('View all orders')),
            array('value' => '6', 'label' => Mage::helper('webpos')->__('Manage orders created by this user')),
            array('value' => '7', 'label' => Mage::helper('webpos')->__('Manage orders created by other users')),
            array('value' => '8', 'label' => Mage::helper('webpos')->__('Manage all orders')),
            array('value' => '9', 'label' => Mage::helper('webpos')->__('Apply custom discount per cart')),
            array('value' => '10', 'label' => Mage::helper('webpos')->__('Apply coupon codes')),
            array('value' => '11', 'label' => Mage::helper('webpos')->__('Apply custom discount per item')),
            array('value' => '12', 'label' => Mage::helper('webpos')->__('Apply custom price')),
            array('value' => '13', 'label' => Mage::helper('webpos')->__('Apply all discounts')),
            array('value' => '14', 'label' => Mage::helper('webpos')->__('X-Report (mid-day report)')),
            array('value' => '15', 'label' => Mage::helper('webpos')->__('Z-Report (end-of-day report)')),
            array('value' => '16', 'label' => Mage::helper('webpos')->__('Daily POS Orders Summary')),
            array('value' => '17', 'label' => Mage::helper('webpos')->__('All Reports'))
        );
        return $options;
    }

    static public function getStoreValuesForForm() {
        $options = array(
            array('value' => '1', 'label' => Mage::helper('webpos')->__('All Permissions')),
            array('value' => array(
                    array('value' => '2', 'label' => Mage::helper('webpos')->__('Create Orders')),
                ), 'label' => Mage::helper('webpos')->__('Create Orders')),
            array('value' => array(
                    array('value' => '3', 'label' => Mage::helper('webpos')->__('Created by this user')),
                    array('value' => '4', 'label' => Mage::helper('webpos')->__('Created by other staff')),
                    array('value' => '5', 'label' => Mage::helper('webpos')->__('All orders')),
                ), 'label' => Mage::helper('webpos')->__('View Orders')),
            array('value' => array(
                    array('value' => '6', 'label' => Mage::helper('webpos')->__('Manage orders created by this user')),
                    array('value' => '7', 'label' => Mage::helper('webpos')->__('Manage orders created by other users')),
                    array('value' => '8', 'label' => Mage::helper('webpos')->__('Manage all orders')),
                ), 'label' => Mage::helper('webpos')->__('Manage Orders')),
            array('value' => array(
                    array('value' => '9', 'label' => Mage::helper('webpos')->__('Apply custom discount per cart')),
                    array('value' => '10', 'label' => Mage::helper('webpos')->__('Apply coupon codes')),
                    array('value' => '11', 'label' => Mage::helper('webpos')->__('Apply custom discount per item')),
                    array('value' => '12', 'label' => Mage::helper('webpos')->__('Apply custom price')),
                    array('value' => '13', 'label' => Mage::helper('webpos')->__('Apply all discounts')),
                ), 'label' => Mage::helper('webpos')->__('Manage Discounts')),
            array('value' => array(
                    array('value' => '14', 'label' => Mage::helper('webpos')->__('X-Report (mid-day report)')),
                    array('value' => '15', 'label' => Mage::helper('webpos')->__('Z-Report (end-of-day report)')),
                    array('value' => '16', 'label' => Mage::helper('webpos')->__('Daily POS Orders Summary')),
                    array('value' => '17', 'label' => Mage::helper('webpos')->__('All Reports')),
                ), 'label' => Mage::helper('webpos')->__('View Reports'))
        );
        return $options;
    }

}
