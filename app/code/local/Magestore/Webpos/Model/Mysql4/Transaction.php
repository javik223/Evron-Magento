<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('webpos/transaction', 'transaction_id');
    }
}
