<?php
/**
 * Custom model for catalog product collection.
 *
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * @var NanoWebG_ElasticSearch_Model_Resource_Engine_Abstract Search engine.
     */
    protected $_ElasticEngine;

    /**
     * @var array Faceted data.
     */
    protected $_faceData = array();

    /**
     * @var array Facets conditions.
     */
    protected $_faceCondition = array();

    /**
     * @var array General default query.
     */
    protected $_defaultQuery = array('*' => '*');

    /**
     * @var string Search query text.
     */
    protected $_queryText = '';

    /**
     * @var array Search query filters.
     */
    protected $_queryFilters = array();

    /**
     * @var array Search query range filters.
     */
    protected $_queryRangeFilters = array();

    /**
     * @var array Search entity ids.
     */
    protected $_entityIds = array();

    /**
     * @var array Sort by definition.
     */
    protected $_sortBy = array();

    
    /**
     * Adds new facet condition to the current collection.
     *
     * @param string $field
     * @param mixed $condition
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function newFacetCondition($field, $condition = null)
    {
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection newFacetCondition', null, 'elasticsearch_debug.log');
            Mage::log('field:'.$field.' _facetsConditions:'.$this->_faceConditions, null, 'elasticsearch_debug.log');
        }

        if (array_key_exists($field, $this->_faceConditions)) {
            if (!empty($this->_faceConditions[$field])) {
                $this->_faceConditions[$field] = array($this->_faceConditions[$field]);
            }
            $this->_faceConditions[$field][] = $condition;
        } else {
            $this->_faceConditions[$field] = $condition;
        }

       return $this;
    }

    /**
     * Adds fields to filter.
     *
     * @param $fields
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function addFieldsToFilter($fields)
    {
        return $this;
    }

    /**
     * Stores the filter query.
     *
     * @param array $params
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function addFqFilter($params)
    {
        if (is_array($params)) {
            foreach ($params as $field => $value) {
                $this->_queryFilters[$field][] = $value;
            }
        }

        return $this;
    }

    /**
     * Stores the filter query range.
     *
     * @param array $params
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function addFqRangeFilter($params)
    {
        if (is_array($params)) {
            foreach ($params as $field => $value) {
                $this->_queryRangeFilters[$field][] = $value;
            }
        }

        return $this;
    }

    /**
     * Stores the query text filter.
     *
     * @param $query
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function addSearchFilter($query)
    {
        $this->_queryText = $query;

        return $this;
    }

    /**
     * Stores the search query filter.
     *
     * @param mixed $param
     * @param null $value
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function addSearchQfFilter($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->addSearchQfFilter($field, $value);
            }
        } elseif (isset($value)) {
            if (isset($this->_queryFilters[$param]) && !is_array($this->_queryFilters[$param])) {
                $this->_queryFilters[$param] = array($this->_queryFilters[$param]);
                $this->_queryFilters[$param][] = $value;
            } else {
                $this->_queryFilters[$param] = $value;
            }
        }

        return $this;
    }

    /**
     * Extends search parameters.
     *
     * @return array
     */
    public function getExtendedSearchParams()
    {
        $result = $this->_queryFilters;
        $result['query_text'] = $this->_queryText;

        return $result;
    }

    /**
     * Returns the faceted data.
     *
     * @param string $field
     * @return array
     */
    public function getFacetedData($field)
    {
        if (array_key_exists($field, $this->_faceData)) {
            return $this->_faceData[$field];
        }

        return array();
    }

    /**
     * Returns the size of the collection.
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $query = $this->_getQuery();
            $params = $this->_getParams();
            $params['limit'] = 1;
            $this->_ElasticEngine->getIdsByQuery($query, $params);
            $this->_totalRecords = $this->_ElasticEngine->getLastNumFound();
        }

        return $this->_totalRecords;
    }

    /**
     * Retrieves current stats.
     * Used for maximum price.
     *
     * @param $fields
     * @return mixed
     */
    public function getStats($fields)
    {
        $query = $this->_getQuery();
        $params = $this->_getParams();
        $params['limit'] = 0;

        if (!is_array($fields)) {
            $fields = array($fields);
        }
        foreach ($fields as $field) {
            $params['stats']['fields'][] = $field;
        }

        $this->_pageSize = false;
       //$this->_pageSize = 18;

        return $this->_ElasticEngine->getStats($query, $params);
    }

    /**
     * Sets the current search engine.
     *
     * @param NanoWebG_ElasticSearch_Model_Resource_Engine_Abstract $engine
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
     public function setEngine($engine)
    {
        $this->_ElasticEngine = $engine;


        return $this;
    }

    public function getEngine()
    {
        return $this->_ElasticEngine;
    }

    /**
     * Sets the sort order.
     *
     * @param string $attribute
     * @param string $dir
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        $this->_sortBy[] = array($attribute => $dir);

        return $this;
    }

    /**
     * Reorder collection.
     *
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!empty($this->_entityIds)) {
            $sortedItems = array();
            foreach ($this->_entityIds as $id) {
                if (isset($this->_items[$id])) {
                    $sortedItems[$id] = $this->_items[$id];
                }
            }
            $this->_items = &$sortedItems;
        }

        
        return $this;
    }

    /**
     * Filters collection by id.
     * Will also stores faceted data and total records.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _beforeLoad()
    {
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection _beforeLoad', null, 'elasticsearch_debug.log');
        }
       
        $ids = $this->getSearchResultIds();

        if (empty($ids)) {
            $ids = array(0); // Fix for no result
        }
        
        $page_ids = array();
        $params = $this->_getParams();

        $limit = (int) $params['limit'] ? (int) $params['limit'] : count($ids);
        $offset = (int) $params['offset'] ? (int) $params['offset'] : 0;

       // $max_offset = floor(count($ids)/$limit);
                
        $max = $offset + $limit;

        $max = $max > count($ids) ? count($ids) : $max;

        $offset = $offset + $limit > $max ? (int) floor($max/$limit)*$limit : $offset;

        foreach ($ids as $key => $value) {
            
            if($key < $offset){
                continue;
            }

             
            if($key > $max){
                break;
            }

            if($key >= $offset && $key < $max){
                $page_ids[] = $value;
            }
        }

        $this->addIdFilter($page_ids);
        $this->_entityIds = $page_ids;
        $this->_pageSize = false;

        Mage::dispatchEvent('nanowebg_elasticsearch_product_collection_before', array(
                'collection' => $this,

        ));

        return parent::_beforeLoad();
    }


    /**
     * Retrieves parameters.
     *
     * @return array
     */
    protected function _getParams()
    {
        $store = Mage::app()->getStore($this->getStoreId());
        $params = array();
        $params['locale_code'] = $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $params['filters'] = $this->_queryFilters;
        $params['range_filters'] = $this->_queryRangeFilters;

        if (!empty($this->_sortBy)) {
            $params['sort_by'] = $this->_sortBy;
        }

        if ($this->_pageSize !== false) {
            $page = ($this->_curPage  > 0) ? (int) $this->_curPage  : 1;
            $rowCount = ($this->_pageSize > 0) ? (int) $this->_pageSize : 1;
            $params['offset'] = $rowCount * ($page - 1);
            $params['limit'] = $rowCount;
        }

        if (!empty($this->_faceConditions)) {
            $params['facets'] = $this->_faceConditions;
        }

        return $params;
    }

    public function getESParams()
    {
        return $this->_getParams();
    }
    /**
     * Returns stored text query.
     *
     * @return string
     */
    protected function _getQuery()
    {
        return $this->_queryText;
    }

    public function getESQuery()
    
    {
        return $this->_getQuery();
    }

    public function getSearchResultIds()
    {
         
         $ids = array();

         if ($this->_ElasticEngine) {
            $result = $this->_ElasticEngine->getIdsByQuery($this->_getQuery(), $this->_getParams());
            $ids = isset($result['ids']) ? $result['ids'] : array();
            $this->_faceData = isset($result['faceted_data']) ? $result['faceted_data'] : array();
            $this->_totalRecords = isset($result['total_count']) ? $result['total_count'] : null;
        }

        return $ids;
    }
}
