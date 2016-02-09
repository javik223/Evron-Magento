<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Model_Sales_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping {

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $amount = $address->getShippingAmount();
        if ($amount != 0 || $address->getShippingDescription()) {
            if ($address->getShippingDescription()) {
                $title = $address->getShippingDescription();
            } else {
                $title = Mage::helper('sales')->__('Shipping & Handling');
            }
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $address->getShippingAmount()
            ));
        }
        return $this;
    }

}
