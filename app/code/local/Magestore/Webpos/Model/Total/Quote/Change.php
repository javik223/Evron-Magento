<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Model_Total_Quote_Change extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this->setCode('webpos_change');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address) {

        $quote = $address->getQuote();
        $cashin = $address->getWebposCash();
        if (empty($cashin)) {
            return $this;
        }
        $grandTotal = $address->getGrandTotal();
        $change = $cashin - $grandTotal;
        if ($change > 0) {
            $basechange = $change / $quote->getStore()->convertPrice(1);
            $quote->setWebposChange($change);
            $quote->setWebposBaseChange($basechange);
            $address->setWebposChange($change)
                    ->setWebposBaseChange($basechange);
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        return $this;
    }

}
