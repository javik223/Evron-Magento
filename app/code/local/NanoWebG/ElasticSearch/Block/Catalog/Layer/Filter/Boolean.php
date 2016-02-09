<?php
/**
 * Filters the boolean attributes in the layered navigation.
 *
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   
 */
class NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Boolean extends NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Attribute
{
    /**
     * Specifies the filter model name.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Boolean
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'nanowebg_elasticsearch/catalog_layer_filter_boolean';
    }
}
