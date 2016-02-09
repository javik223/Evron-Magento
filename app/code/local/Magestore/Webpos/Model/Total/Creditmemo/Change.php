<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_WebPOS_Model_Total_Creditmemo_Change extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        if (!$creditmemo->getInvoice() || !$creditmemo->getInvoice()->getId()) {
            return;
        }
        $order = $creditmemo->getOrder();
        if ($order->getWebposChange() < 0.0001) {
            return;
        }
        $creditmemo->setWebposChange($order->getWebposChange())
                ->setWebposBaseChange($order->getWebposBaseChange());
    }

}
