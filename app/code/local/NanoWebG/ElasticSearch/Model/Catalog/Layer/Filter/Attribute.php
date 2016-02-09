<?php
/**
 * Filters the attributes in the layered navigation.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * New facet condition added to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute
     */
    public function newFacetCondition()
    {
        $this->getLayer()
            ->getProductCollection()
            ->newFacetCondition($this->_getFilteredField());

        return $this;
    }

    /**
     * Applies the request parameter to the product collection.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $appliedfilter = $request->getParam($this->_requestVar);
        if (is_array($appliedfilter) || null === $appliedfilter) {
            return $this;
        }

        $text = $this->_getOptionText($appliedfilter);
        if ($this->_isFilter($appliedfilter) && strlen($text)) {
            $this->applyFilter($this, $appliedfilter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $appliedfilter));
            $this->_items = array();
        }

        return $this;
    }

     /**
     * Checks if filter is valid.
     *
     * @param string $filter
     * @return bool
     */
    protected function _isFilter($filter)
    {
        return !empty($filter);
    }
    
    /**
     * Applies the valid filter to product collection.
     *
     * @param $filter
     * @param $value
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute
     */
    public function applyFilter($filter, $value)
    {
        if (!$this->_isFilter($value)) {
            $value = array();
        } else if (!is_array($value)) {
            $value = array($value);
        }

        $attribute = $filter->getAttributeModel();
        $searchparam = Mage::helper('nanowebg_elasticsearch')->getParams($attribute, $value);

        $this->getLayer()
            ->getProductCollection()
            ->addSearchQfFilter($searchparam);

        return $this;
    }

    /**
     * Returns facets data.
     *
     * @return array
     */
    protected function _getAllFacets()
    {
        /** @var $productCollection NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection */
        $productCollection = $this->getLayer()->getProductCollection();
        $field = $this->_getFilteredField();
        $allFacets = $productCollection->getFacetedData($field);

        return $allFacets;
    }

    /**
     * Returns the filtered attribute field name.
     *
     * @return string
     */
    protected function _getFilteredField()
    {
        /** @var $attributes Mage_Catalog_Model_Resource_Eav_Attribute */
        $attributes = $this->getAttributeModel();
        $field = Mage::helper('nanowebg_elasticsearch')->getFieldName($attributes);

        return $field;
    }

    /**
     * Retrieves the data of the current item.
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var $attributes Mage_Catalog_Model_Resource_Eav_Attribute */
        $attributes = $this->getAttributeModel();
        $this->_requestVar = $attributes->getAttributeCode();

        $layer = $this->getLayer();
        $key = $layer->getStateKey() . '_' . $this->_requestVar;
        $itemdata = $layer->getAggregator()->getCacheData($key);

        if ($itemdata === null) {
            $facets = $this->_getAllFacets();

            $itemdata = array();
            if (array_sum($facets) > 0) {
                if ($attributes->getFrontendInput() != 'text') {
                    $selectOptions = $attributes->getFrontend()->getSelectOptions();
                } else {
                    $selectOptions = array();
                    foreach ($facets as $label => $count) {
                        $selectOptions[] = array(
                            'label' => $label,
                            'value' => $label,
                            'count' => $count,
                        );
                    }
                }
                foreach ($selectOptions as $option) {
                    if (is_array($option['value']) || !Mage::helper('core/string')->strlen($option['value'])) {
                        continue;
                    }
                    $count = 0;
                    $label = $option['label'];
                    if (isset($facets[$option['value']])) {
                        $count = (int) $facets[$option['value']];
                    }
                    if (!$count && $this->_getIsFilterabled($attributes) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        continue;
                    }
                    $itemdata[] = array(
                        'label' => $label,
                        'value' => $option['value'],
                        'count' => (int) $count,
                    );
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attributes->getId()
            );

            $tags = $layer->getStateTags($tags);
            $layer->getAggregator()->saveCacheData($itemdata, $key, $tags);
        }

        return $itemdata;
    }

    /**
     * Returns option label.
     *
     * @param int $optionId
     * @return bool|int|string
     */
    protected function _getOptionText($optionId)
    {
        if ($this->getAttributeModel()->getFrontendInput() == 'text') {
            return $optionId; // not an option id
        }

        return parent::_getOptionText($optionId);
    }
}
