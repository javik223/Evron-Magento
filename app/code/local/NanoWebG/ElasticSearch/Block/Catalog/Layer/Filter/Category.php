<?php
/**
 * Filters the categories in the layered navigation.
 *
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * 
 */
class NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Specifies the filter model name.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'nanowebg_elasticsearch/catalog_layer_filter_category';
    }

    /**
     * Adds new facet condition to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Category
     */
    public function newFacetCondition()
    {
        $this->_filter->newFacetCondition();

        return $this;
    }
}
