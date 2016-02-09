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
 * @package     Magestore_SimiPOS
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * SimiPOS Cash Total Model
 * 
 * @category    Magestore
 * @package     Magestore_SimiPOS
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Total_Quote_Cash extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this->setCode('webpos_cash');
    }

    /**
     * Prepare Data to Storage for Order
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magestore_Webpos_Model_Total_Quote_Cash
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {

        $quote = $address->getQuote();
        $cashin = Mage::getSingleton('webpos/session')->getWebposCash();
        if ($cashin && $cashin <= 0) {
            return $this;
        }
        $baseCashin = $cashin / $quote->getStore()->convertPrice(1);
        $quote->setWebposCash($cashin);
        $quote->setWebposBaseCash($baseCashin);

        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }

        $address->setWebposCash($quote->getWebposCash())
                ->setWebposBaseCash($quote->getWebposBaseCash());
        /*
          $address->setTotalPaid($quote->getWebposCash())
          ->setBaseTotalPaid($quote->getWebposBaseCash());
         */
        if ($quote->getWebposCash() >= $address->getGrandTotal()) {
            /*
              $address->setTotalRefunded($quote->getWebposCash() - $address->getGrandTotal())
              ->setBaseTotalRefunded($quote->getWebposBaseCash() - $address->getBaseGrandTotal());
              if ($payment = $quote->getPayment()) {
              if ($method = $payment->getMethodInstance()) {
              $address->setTotalPaid($address->getTotalRefunded())
              ->setBaseTotalPaid($address->getBaseTotalRefunded());
              }
              }
             */
        } else {
            //$address->setGrandTotal($address->getGrandTotal() - $quote->getWebposCash()  );
            //$address->setBaseGrandTotal($address->getBaseGrandTotal() - $quote->getWebposBaseCash());
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        return $this;
    }

}
