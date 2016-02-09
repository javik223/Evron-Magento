<?php
/**
 * Returns avail search engines.
 *
 * @category    NanoWebG
 * @package     NanoWebG_Elasticsearch
 * @version     1.0.3
 *   
 */
class NanoWebG_ElasticSearch_Model_Adminhtml_System_Config_Stores_Options
{
    /**
     * 
     *
     * @return array
     */
    public function toOptionArray()
    {

        $stores_options = array();
       
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {

            $stores_options[] = array(
                  
                  'value' => $store->getId(),
                  'label' => $store->getName(),

                );
        }

        

        return $stores_options;
    }
}
