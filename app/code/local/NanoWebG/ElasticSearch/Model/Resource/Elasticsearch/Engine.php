<?php
/**
 * 
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * 
 */
class NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Engine 
extends NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Index

{
    
    protected $_isLayeredNavigationAllowed = true;

    protected $_lastNumFound;

    const DEFAULT_MAX_ROWS = 1000;
    public function isLeyeredNavigationAllowed()
    {
        return $this->_isLayeredNavigationAllowed;
    }


    /**
     * Returns catalog product collection with current search engine set.
     *
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function getResultCollection($requestor = null)
    {
        
        $collection =  Mage::getResourceModel('nanowebg_elasticsearch/catalog_product_collection');
        $engine = $collection->setEngine($this);
              
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Engine getResultCollection requestor:'.$requestor, null, 'elasticsearch_debug.log');        
        }

        return $engine;
    }


     /**
     * Retrieves product ids for specified query.
     *
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    public function getIdsByQuery($query, $params = array(), $type = 'product')
    {
        $ids = array();
        $params['fields'] = array('id');
        $resultTmp = $this->search($query, $params, $type);
        if (!empty($resultTmp['ids'])) {
            foreach ($resultTmp['ids'] as $id) {
                $ids[] = $id['id'];
            }
        }
        $result = array(
            'ids' => $ids,
            'total_count' => (isset($resultTmp['total_count'])) ? $resultTmp['total_count'] : null,
            'faceted_data' => (isset($resultTmp['facets'])) ? $resultTmp['facets'] : array(),
        );

        return $result;
    }

    /**
     * Performs search query and facetting.
     *
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    public function search($query, $params = array(), $type = 'product')
    {
        try {
            Varien_Profiler::start('NanoWegG_ElasticSearch');
            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Engine search started', null, 'elasticsearch_debug.log');
            }
            $result = $this->_search($query, $params, $type);

            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Engine search finished', null, 'elasticsearch_debug.log');
            }
            Varien_Profiler::stop('NanoWegG_ElasticSearch');

            return $result;
        } catch (Exception $e) {
            Mage::logException($e);
            if ($this->_getHelper()->isDebugMode()) {
                $this->_getHelper()->errorMode($e->getMessage());
            }
        }

        return array();
    }

    /**
     * @return array
     */
    public function getGlobalFilters($query = null)
    {
        $filters = array();

        if (!Mage::helper('cataloginventory')->isShowOutOfStock()) {
            $filters['in_stock'] = '1';
        }

        if (!empty($query)) {
            $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
        } else {
            $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
        }
        $filters['visibility'] = $visibility;

        return $filters;
    }

    /**
     * Retrieves stats for specified query.
     *
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    public function getStats($query, $params = array(), $type = 'product')
    {
        $stats = $this->_search($query, $params, $type);

        return isset($stats['facets']['stats']) ? $stats['facets']['stats'] : array();
    }

    
    /**
     * Prepares facets query response.
     *
     * @param mixed $response
     * @return array
     */
    protected function _prepareFacetsQueryResponse($response)
    {
        $result = array();
        foreach ($response as $attr => $data) {
            if (isset($data['terms'])) {
                foreach ($data['terms'] as $value) {
                    $result[$attr][$value['term']] = $value['count'];
                }
            } elseif (isset($data['_type']) && $data['_type'] == 'statistical') {
                $result['stats'][$attr] = $data;
            } elseif (isset($data['ranges'])) {
                foreach ($data['ranges'] as $range) {
                    $from = isset($range['from_str']) ? $range['from_str'] : '';
                    $to = isset($range['to_str']) ? $range['to_str'] : '';
                    $result[$attr]["[$from TO $to]"] = $range['total_count'];
                }
            } elseif (preg_match('/\(categories:(\d+) OR show_in_categories\:\d+\)/', $attr, $matches)) {
                $result['categories'][$matches[1]] = $data['count'];
            }
        }

        return $result;
    }

    /**
     * Prepares query response.
     *
     * @param \Elastica\ResultSet $response
     * @return array
     */
    protected function _prepareQueryResponse($response)
    {
        /* @var $response \Elastica\ResultSet */
        if (!$response instanceof \Elastica\ResultSet || $response->getResponse()->hasError() || !$response->count()) {
            return array();
        }
        $this->_lastNumFound = (int) $response->getTotalHits();
        $result = array();
        foreach ($response->getResults() as $doc) {
            $result[] = $this->_toArray($doc->getSource());
        }

        return $result;
    }

    /**
     * Transforms specified object to an array.
     *
     * @param $object
     * @return array
     */
    protected function _toArray($data)
    {
        if (!is_object($data) && !is_array($data)){
            return $data;
        }
        if (is_object($data)){
            $data = get_object_vars($data);
        }

        return array_map(array($this, '_toArray'), $data);
    }

    /**
     * Performs search and facetting.
     *
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    protected function _search($query, $params = array(), $type = 'product')
    {
        $_params = $this->_defaultQueryParams;
        if (is_array($params) && !empty($params)) {
            $_params = array_intersect_key($params, $_params) + array_diff_key($_params, $params);
        }

        $searchParams = array();
        $searchParams['offset'] = isset($_params['offset']) ? (int) $_params['offset'] : 0;
        $searchParams['limit'] = isset($_params['limit']) ? (int) $_params['limit'] : self::DEFAULT_MAX_ROWS;

        if (!is_array($_params['params'])) {
            $_params['params'] = array($_params['params']);
        }

        //$searchParams['sort'] = $this->_getHelper()->_sortFields($_params['sort_by']);
        $searchParams['sort'] = $this->_getHelper()->_sortFields($params['sort_by']);

        $useFacetSearch = (isset($params['facets']) && !empty($params['facets']));
        if ($useFacetSearch) {
            $searchParams['facets'] = $params['facets'];
        }

        if (!empty($_params['params'])) {
            foreach ($_params['params'] as $name => $value) {
                $searchParams[$name] = $value;
            }
        }

        if ($_params['store_id'] > 0) {
            $_params['filters']['store_id'] = $_params['store_id'];
        }

        $searchParams['filters'] = $_params['filters'];

        if (!empty($params['range_filters'])) {
            $searchParams['range_filters'] = $params['range_filters'];
        }

        if (!empty($params['stats'])) {
            $searchParams['stats'] = $params['stats'];
            $useFacetSearch = true;
        }

        if (!empty($params['filters'])) {
           $searchParams['filters'] = $params['filters'];
        }

        $data = $this->_client->search($query, $searchParams, $type);

        if (!$data instanceof \Elastica\ResultSet) {
            return array();
        }

        $result = array();
        /* @var $data \Elastica\ResultSet */
        if (!isset($params['params']['stats']) || $params['params']['stats'] != 'true') {
            $result = array(
                'ids' => $this->_prepareQueryResponse($data),
                'total_count' => $data->getTotalHits()
            );
            if ($useFacetSearch) {
                $result['facets'] = $this->_prepareFacetsQueryResponse($data->getFacets());
            }
        }

        return $result;
    }

    /**
     * Returns suggestions based on specfied query.
     *
     * @param string $query
     * @return array
     */
    protected function _suggest($query)
    {
        /* @var $result \Elastica\Response */
        $result = $this->_client->suggest($query);
        $data = $result->getData();
        $suggestions = array();
        if (isset($data['simple_phrase']) && isset($data['simple_phrase'][0]['options'])) {
            foreach ($data['simple_phrase'][0]['options'] as $suggest) {
                $suggestions[] = $suggest['text'];
            }
        }

        return $suggestions;
    }

    public function suggest($q)
    {
        return $this->_suggest($q);
    }
    /**
     * Returns last number of results found.
     *
     * @return int
     */
    public function getLastNumFound()
    {
        return $this->_lastNumFound;
    }

    public function allowAdvancedIndex()
    {
        return false;
    }

    public function getAllowedVisibility()
    {
        return Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
    }

    public function prepareEntityIndex($index, $separator = ' ')
    {
        return Mage::helper('catalogsearch')->prepareIndexdata($index, $separator);
    }

}
