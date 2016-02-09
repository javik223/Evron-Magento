<?php
/**
 * Filters the decimal attribute in the layered navigation.
 *
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   
 */
class NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Specifies the filter model name.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'nanowebg_elasticsearch/catalog_layer_filter_decimal';
    }
    
        /**
     * Adds new facet condition to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Decimal
     */
    public function newFacetCondition()
    {
        $this->_filter->newFacetCondition();

        return $this;
    }


    /**
     * Creates the filter model.
     *
     * @return NanoWebG_ElasticSearch_Block_Catalog_Layer_Filter_Decimal
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());

        return $this;
    }
}
