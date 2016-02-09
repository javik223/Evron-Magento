<?php

class Magestore_Webpos_Model_Mysql4_Survey extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('webpos/survey', 'survey_id');
    }
}