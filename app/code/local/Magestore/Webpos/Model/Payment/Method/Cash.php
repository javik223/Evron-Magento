<?php

class Magestore_Webpos_Model_Payment_Method_Cash extends Mage_Payment_Model_Method_Abstract {
    /* This model define payment method */

    protected $_code = 'cashforpos';
    protected $_infoBlockType = 'webpos/payment_method_cash_info_cash';

    public function isAvailable($quote = null) {
        $routeName = Mage::app()->getRequest()->getRouteName();
        $cashenabled = Mage::helper('webpos/payment')->isCashPaymentEnabled();
        if ($routeName == "webpos" && $cashenabled == true)
            return true;
        else
            return false;
    }

}

?>
