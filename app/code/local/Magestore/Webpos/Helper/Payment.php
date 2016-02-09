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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Helper
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Helper_Payment extends Mage_Core_Helper_Abstract {
    /*
      These are some functions to get payment method information
     */

    public function getCashMethodTitle() {
        $title = Mage::getStoreConfig('payment/cashforpos/title');
        if ($title == '')
            $title = "Cash ( For Web POS only)";
        return $title;
    }

    public function isCashPaymentEnabled() {
        return Mage::getStoreConfig('payment/cashforpos/active');
    }

    public function getCcMethodTitle() {
        $title = Mage::getStoreConfig('payment/ccforpos/title');
        if ($title == '')
            $title = "Cash ( For Web POS only)";
        return $title;
    }

    public function isCcPaymentEnabled() {
        return Mage::getStoreConfig('payment/ccforpos/active');
    }

    public function isWebposShippingEnabled() {
        return Mage::getStoreConfig('carriers/webpos_shipping/active');
    }

    public function getCp1MethodTitle() {
        $title = Mage::getStoreConfig('payment/cp1forpos/title');
        if ($title == '')
            $title = "Web POS - Custom Payment 1";
        return $title;
    }

    public function isCp1PaymentEnabled() {
        return Mage::getStoreConfig('payment/cp1forpos/active');
    }

    public function getCp2MethodTitle() {
        $title = Mage::getStoreConfig('payment/cp2forpos/title');
        if ($title == '')
            $title = "Web POS - Custom Payment 2";
        return $title;
    }

    public function isCp2PaymentEnabled() {
        return Mage::getStoreConfig('payment/cp2forpos/active');
    }

}
