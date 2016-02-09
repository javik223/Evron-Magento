<?php
/**
 * Returns avail search engines.
 *
 * @category    NanoWebG
 * @package     NanoWebG_Elasticsearch
 * @version     1.0.3
 *   
 */
class NanoWebG_ElasticSearch_Model_Adminhtml_System_Config_Engine_Options
{
    /**
     * 
     *
     * @return array
     */
    public function toOptionArray()
    {

        $engine_options = array();

        $options = array(
            'catalogsearch/fulltext_engine'  => Mage::helper('adminhtml')->__('MySQL'),
            'nanowebg_elasticsearch/elasticsearch_engine' => Mage::helper('adminhtml')->__('Elastic Search'),
        );

        
        foreach ($options as $key => $value) {
            $engine_options[] = array(
                'value' => $key,
                'label' => $value
            );
        }

        return $engine_options;
    }
}
