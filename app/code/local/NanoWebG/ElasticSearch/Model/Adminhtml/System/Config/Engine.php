<?php
/**
 * Class handling actions after engine config is saved
 * 
 * @category    Nanowebg
 * @package     Nanowebg_Elasticsearch
 * @version     1.0.3
 *   
 */
class NanoWebG_ElasticSearch_Model_Adminhtml_System_Config_Engine extends Mage_Core_Model_Config_Data
{
    /**
     * 
     *
     * @return Nanowebg_Elasticsearch_Model_Adminhtml_System_Config_Engine_Engine
     */
    protected function _afterSave()
    {
    
        
        return $this;
    }
}
