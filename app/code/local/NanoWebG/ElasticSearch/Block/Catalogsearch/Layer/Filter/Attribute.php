<?php
/**
 * Filters the attributes in layered navigation in a query search context.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   
 */
class NanoWebG_ElasticSearch_Block_Catalogsearch_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Specifies the filter model name.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalogsearch_Layer_Filter_Attribute
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'nanowebg_elasticsearch/catalogsearch_layer_filter_attribute';
    }

    /**
     * New facet condition added to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Block_Catalogsearch_Layer_Filter_Attribute
     */
    public function newFacetCondition()
    {
        $this->_filter->newFacetCondition();

        return $this;
    }

    /**
     * Creates filter model.
     *
     * @return NanoWebG_ElasticSearch_Block_Catalogsearch_Layer_Filter_Attribute
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());

        return $this;
    }

    
}
