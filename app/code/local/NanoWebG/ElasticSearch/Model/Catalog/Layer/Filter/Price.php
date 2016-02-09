<?php
/**
 * Filters the price in the layered navigation.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    const CACHE_TAG = 'MAXPRICE';

    protected $_elasticsearch_prod_collection;
    protected $_filt_data;

    /**
     * Adds new facet condition to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Price
     */
    public function newFacetCondition()
    {

        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Price newFacetCondition', null, 'elasticsearch_debug.log');
        }

        $range = $this->getPriceRange();
        
        $maxPrice = $this->getMaxPrice();
        if ($maxPrice > 0) {
            $priceFacets = array();
            $facetCount = (int) ceil($maxPrice / $range);

            for ($i = 0; $i < $facetCount + 1; $i++) {
                $from = ($i === 0) ? '' : ($i * $range);
                $to = ($i === $facetCount) ? '' : (($i + 1) * $range);
                $priceFacets[] = array(
                    'from' => $from,
                    'to' => $to,
                    'include_upper' => !($i < $facetCount)
                );
            }

            $this->getLayer()->getProductCollection()->newFacetCondition($this->_getFilteredField(), $priceFacets);
        }

        return $this;
    }

    /**
     * Returns the cache tag.
     *
     * @return string
     */
    public function getCacheTag()
    {
        return self::CACHE_TAG;
    }

    /**
     * Retrieves max price.
     *
     * @return float
     */
    public function getMaxPrice()
    {
        $Params = $this->getLayer()->getProductCollection()->getExtendedSearchParams();
        $uniquePart = strtoupper(md5(serialize($Params)));
        $cacheKey = 'MAXPRICE_' . $this->getLayer()->getStateKey() . '_' . $uniquePart;

        $cachedData = Mage::app()->loadCache($cacheKey);
        if (!$cachedData) {
            $stats = $this->getLayer()->getProductCollection()->getStats($this->_getFilteredField());

            $max = $stats[$this->_getFilteredField()]['max'];
            if (!is_numeric($max)) {
                $max = parent::getMaxPrice();
            }

            $cachedData = (float) $max;
            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cachedData, $cacheKey, $tags);
        }

        return $cachedData;
    }

    /**
     * Apply price range filter.
     *
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Price
     */
    protected function _applyPriceRange()
    {
        

        $interval = $this->getInterval();

        if (!$interval) {
            return $this;
        }

        list($from, $to) = $interval;
        if ($from === '' && $to === '') {
            return $this;
        }

        if ($to !== '') {
            $to = (float) $to;
            if ($from == $to) {
                $to += .01;
            }
        }

        $field = $this->_getFilteredField();
        $value = array(
            $field => array(
                'include_upper' => !($to < $this->getMaxPrice())
            )
        );

        if (!empty($from)) {
            $value[$field]['from'] = $from;
        }
        if (!empty($to)) {
            $value[$field]['to'] = $to;
        }

        $this->getLayer()->getProductCollection()->addFqRangeFilter($value);

        return $this;
    }

    /**
     * Returns price field.
     *
     * @return string
     */
    protected function _getFilteredField()
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $priceField = 'price_' . $customerGroupId . '_' . $websiteId;

        return $priceField;
    }

    /**
     * Retrieves the data of the current item.
     *
     * @return array
     */
    protected function _getItemsData()
    {

        $data = array();
        $facets = $this->getLayer()->getProductCollection()->getFacetedData($this->_getFilteredField());
       
        if (!empty($facets)) {
            foreach ($facets as $key => $count) {
                if (!$count) {
                    unset($facets[$key]);
                }
            }
            $i = 0;
            foreach ($facets as $key => $count) {
                $i++;
                preg_match('/^\[(\d*) TO (\d*)\]$/', $key, $rangeKey);
                $fromPrice = $rangeKey[1];
                $toPrice = ($i < count($facets)) ? $rangeKey[2] : '';

                if($this->_elasticsearch_prod_collection===null) {
           
                  $data[] = array(
                    'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                    'value' => $fromPrice . '-' . $toPrice,
                   'count' => $count
                   
                  );
                }else{
                  $data[] = array(
                    'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                    'value' => $fromPrice . '-' . $toPrice,
                   // 'count' => $count
                    'count' => 1
                  );
            }
            }
        }
        
        return $data;
    }

    public function rebuildFilter($searchResultsProdCollection)
    {       
       if($this->_elasticsearch_prod_collection===null){

          $this->_elasticsearch_prod_collection = $searchResultsProdCollection;
       }

      $this->_getItemsData();
    }
}
