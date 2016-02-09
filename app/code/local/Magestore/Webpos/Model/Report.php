<?php

/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 9:53 SA
 */
class Magestore_Webpos_Model_Report extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('webpos/report');
    }

}
