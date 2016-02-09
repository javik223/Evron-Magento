<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Payment_Method_Cc_Ccforpos extends Mage_Payment_Block_Form {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('webpos/webpos/payment/method/form/ccforpos.phtml');
    }

}
