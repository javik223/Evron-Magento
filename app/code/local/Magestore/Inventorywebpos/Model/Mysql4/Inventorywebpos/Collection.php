<?php

class Magestore_Inventorywebpos_Model_Mysql4_Inventorywebpos_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventorywebpos/inventorywebpos');
    }
}