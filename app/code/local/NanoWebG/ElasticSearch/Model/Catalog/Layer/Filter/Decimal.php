<?php
/**
 * Filters the decimal attribute in the layered navigation.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal
{
    const CACHE_TAG = 'MAXVALUE';

    /**
     * Adds new facet condition to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal
     */
    public function newFacetCondition()
    {
        
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal newFacetCondition', null, 'elasticsearch_debug.log');
        }

        $range = $this->getRange();
        $maxValue = $this->getMaxValue();
        if ($maxValue > 0) {
            $facets = array();
            $count = (int) ceil($maxValue / $range);

            for ($i = 0; $i < $count + 1; $i++) {
                $facets[] = array(
                    'from' => $i * $range,
                    'to' => ($i + 1) * $range,
                    'include_upper' => !($i < $count)
                );
            }

            $field = $this->_getFilteredField();
            $this->getLayer()->getProductCollection()->newFacetCondition($field, $facets);
        }

        return $this;
    }

    /**
     * Retrieves and applies the request parameter to the product collection.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $appliedfilter = $request->getParam($this->getRequestVar());
        if (!$appliedfilter) {
            return $this;
        }

        $appliedfilter = explode(',', $appliedfilter);
        if (count($appliedfilter) != 2) {
            return $this;
        }

        list($index, $range) = $appliedfilter;

        if ((int) $index && (int) $range) {
            $this->setRange((int) $range);

            $this->applyFilter($this, $range, $index);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderDecimalRanges($range, $index), $appliedfilter)
            );

            $this->_items = array();
        }

        return $this;
    }

    /**
     * Apply decimal filter range.
     *
     * @param NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal $filter
     * @param int $range
     * @param int $index
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Decimal
     */
    public function applyFilter($filter, $range, $index)
    {
        $results = array(
            $this->_getFilteredField() => array(
                'from' => ($range * ($index - 1)),
                'to'   => $range * $index,
            )
        );
        $filter->getLayer()->getProductCollection()->addFqFilter($results);

        return $this;
    }

    /**
     * Returns the decimal field name.
     *
     * @return string
     */
    protected function _getFilteredField()
    {
        $fieldName = Mage::helper('nanowebg_elasticsearch')->getFieldName($this->getAttributeModel());

        return $fieldName;
    }

    public function getMaxValue()
    {
        $Params = $this->getLayer()->getProductCollection()->getExtendedSearchParams();
        $uniquePart = strtoupper(md5(serialize($Params)));
        $cacheKey = 'MAXVALUE_' . $this->getLayer()->getStateKey() . '_' . $uniquePart;

        $cached = Mage::app()->loadCache($cacheKey);
        if (!$cached) {
            $stats = $this->getLayer()->getProductCollection()->getStats($this->_getFilteredField());

            $max = $stats[$this->_getFilteredField()]['max'];
            if (!is_numeric($max)) {
                $max = parent::getMaxValue();
            }

            $cached = (float) $max;
            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cached, $cacheKey, $tags);
        }

        return $cached;
    }

    /**
     * Renders the decimal ranges.
     *
     * @param int $range
     * @param float $value
     * @return string
     */
    protected function _renderDecimalRanges($range, $value)
    {
        /** @var $attributes Mage_Catalog_Model_Resource_Eav_Attribute */
        $attributes = $this->getAttributeModel();

        if ($attributes->getFrontendInput() == 'price') {
            return parent::_renderDecimalRanges($range, $value);
        }

        $from = ($value - 1) * $range;
        $to = $value * $range;

        if ($from != $to) {
            $to -= 0.01;
        }

        $to = Zend_Locale_Format::toFloat($to, array('locale' => Mage::helper('nanowebg_elasticsearch')->getLocale()));

        return Mage::helper('catalog')->__('%s - %s', $from, $to);
    }

    /**
     * Retrieves the data of the current item.
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $range = $this->getRange();
        $field = $this->_getFilteredField();
        $facets = $this->getLayer()->getProductCollection()->getFacetedData($field);

        $itemData = array();
        if (!empty($facets)) {
            foreach ($facets as $key => $count) {
                if ($count > 0) {
                    preg_match('/TO ([\d\.]+)\]$/', $key, $rangeKey);
                    $rangeKey = round($rangeKey[1] / $range);
                    $itemData[] = array(
                        'label' => $this->_renderDecimalRanges($range, $rangeKey),
                        'value' => $rangeKey . ',' . $range,
                        'count' => $count,
                    );
                }
            }
        }

        return $itemData;
    }
}
