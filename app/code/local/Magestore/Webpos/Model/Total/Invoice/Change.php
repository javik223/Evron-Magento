<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_WebPOS_Model_Total_Invoice_Change extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order = $invoice->getOrder();
        if ($order->getWebposChange() < 0.0001) {
            return;
        }
        $invoice->setWebposChange($order->getWebposChange())
                ->setWebposBaseChange($order->getWebposBaseChange());
    }

}
