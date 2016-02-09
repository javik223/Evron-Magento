<?php

class Magestore_Inventorywebpos_Model_Mysql4_Webposuser extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the warehouse_webpos_user_id refers to the key field in your database table.
        $this->_init('inventorywebpos/webposuser', 'warehouse_webpos_user_id');
    }
}