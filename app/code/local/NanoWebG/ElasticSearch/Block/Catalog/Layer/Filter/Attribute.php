<?php
/**
 * Filters the attributes in the layered navigation.
 *
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2014 Nanowebgroup (http://www.nanowebgroup.com)
 */
class NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Specifies the filter model name.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'nanowebg_elasticsearch/catalog_layer_filter_attribute';
    }

    /**
     * Craetes the filter model.
     *
     * @return NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Attribute
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());

        return $this;
    }

    /**
     * New facet condition added to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Attribute
     */
    public function newFacetCondition()
    {
        $this->_filter->newFacetCondition();

        return $this;
    }
}
