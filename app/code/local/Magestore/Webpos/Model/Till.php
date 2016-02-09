<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Model_Till extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('webpos/till');
    }

}