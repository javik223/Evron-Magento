<?php
// Add custom autoload logic since Elactica library uses namespaces
spl_autoload_register(function($class) {
    if (false !== strpos($class, 'Elastica\\')) {
        $class = trim($class, '\\');
        $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('\\', ' ', $class)));
        $classFile .= '.php';
        @include $classFile;
    }
});

/**
 * Elasticsearch client.
 *
 * @category    NanoWegGroup
 * @package     NanoWegG_ElasticSearch
 * @version     1.0.3
 * 
 */
class NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Client extends \Elastica\Client
{
    

    protected $_index; 
    protected $_helper;
    protected $_dateFormat = 'date';

    /**
     * @var array Stop languages for token filter.
     * @link http://www.elasticsearch.org/guide/reference/index-modules/analysis/stop-tokenfilter.html
     */
    protected $_stopLanguages = array(
        'arabic', 'armenian', 'basque', 'brazilian', 'bulgarian', 'catalan', 'czech',
        'danish', 'dutch', 'english', 'finnish', 'french', 'galician', 'german', 'greek',
        'hindi', 'hungarian', 'indonesian', 'italian', 'norwegian', 'persian', 'portuguese',
        'romanian', 'russian', 'spanish', 'swedish', 'turkish',
    );
    /**
     * @var array Snowball languages.
     * @link http://www.elasticsearch.org/guide/reference/index-modules/analysis/snowball-tokenfilter.html
     */
    protected $_snowballLanguages = array(
        'Armenian', 'Basque', 'Catalan', 'Danish', 'Dutch', 'English', 'Finnish', 'French',
        'German', 'Hungarian', 'Italian', 'Kp', 'Lovins', 'Norwegian', 'Porter', 'Portuguese',
        'Romanian', 'Russian', 'Spanish', 'Swedish', 'Turkish',
    );

    
        
    public function __construct()
    {
        $this->_helper = Mage::helper('nanowebg_elasticsearch/elasticsearch');
        $config = $this->_helper->getEngineConfig();

        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('Initiating NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Client', null, 'elasticsearch_debug.log');
        }
        
        parent::__construct($config);
        if (!isset($config['index'])) {
            Mage::throwException('Index must be defined for search engine client.');
        }
        $this->setIndex($config['index']);
    }

    /**
     * Cleans index.
     *
     * @param int $storeId
     * @param int $id
     * @param string $type
     * @return mixed
     */
    public function cleanIndex($storeId = null, $id = null, $type = 'product')
    {
        $this->_prepareIndex();

        if ($this->getStatus()->indexExists($this->_index)) {
            if (null === $storeId) {
                // no store filter
                if (empty($id)) {
                    // delete ALL docs of type $type
                    return $this->getIndex($this->_index)->getType($type)->delete();
                } else {
                    // delete docs of type $type with _id in $id
                    foreach (Mage::app()->getStores() as $store) {
                        $this->cleanIndex($store->getId(), $id, $type);
                    }
                }
            } else {
                if (empty($id)) {
                    // delete ALL docs from specific store
                    $query = new \Elastica\Query\Term();
                    $query->setTerm('store_id', $storeId);
                    $response = $this->getIndex($this->_index)->getType($type)->deleteByQuery($query);

                    return $response;
                } else {
                    // delete docs from specific store with _id in $id
                    $ids = (array) $id;
                    foreach ($ids as &$id) {
                        $id .= '|' . $storeId;
                    }
                    unset($id);
                    
                    return $this->deleteIds($ids, $this->_index, $type);
                }
            }
        }

        return $this;
    }

    
     /**
     * Calls _prepareIndex (to set index settings).
     *
     * @return \Elastica\Client
     */

     public function prepareIndex()
     {
        $this->_prepareIndex();

     }
     /**
     * Removes documents from index.
     *
     * @param string $storeid
     * @param array $id
     * @param string $type
     * @return \Elastica\Document
     */

     public function removeDocs($storeId = null, $id = null, $type = 'product')
     {

         if ($this->getStatus()->indexExists($this->_index)) {
                
             $store_ids = array();

             if($storeId===null){
                 
                foreach (Mage::app()->getStores() as $store) {
                    
                    $store_ids[] = $store->getId();

                }
             }elseif(is_array($storeId)){

                    $store_ids = $storeId;

             }elseif($storeId>0){
                   
                   $store_ids[] = $storeId;
             }

             $ids = (array) $id;
             $prod_ids = array();

             foreach ($store_ids as $st_id) {
                            
               foreach ($ids as $pid) {
                       
                        $prod_ids[] = $pid .'|' . $st_id;
                    }
             }
                          
             $this->deleteIds($prod_ids, $this->_index, $type);
 
         }

         return $this;

     }
    /**
     * Create document to index.
     *
     * @param string $id
     * @param array $data
     * @param string $type
     * @return \Elastica\Document
     */
    public function createDoc($id = '', array $data = array(), $type = 'product')
    {
        return new \Elastica\Document($id, $data, $type, $this->_index);
    }

    
    /**
     * Deletes index.
     *
     * @return bool|\Elastica\Response
     */
    public function deleteIndex()
    {
        if ($this->getStatus()->indexExists($this->_index)) {
            return $this->getIndex($this->_index)->delete();
        }

        return true;
    }

    /**
     * Returns facets max size parameter.
     *
     * @return int
     */
    public function getFacetsMaxSize()
    {
        return (int) $this->getConfig('facets_max_size');
    }

    /**
     * Returns fuzzy max query terms parameter.
     *
     * @link http://www.elasticsearch.org/guide/reference/query-dsl/flt-query.html
     * @return int
     */
    public function getFuzzyMaxQueryTerms()
    {
        return (int) $this->getConfig('fuzzy_max_query_terms');
    }

    /**
     * Returns fuzzy min similarity parameter.
     *
     * @link http://www.elasticsearch.org/guide/reference/query-dsl/flt-query.html
     * @return float
     */
    public function getFuzzyMinSimilarity()
    {
        // 0 to 1 (1 excluded)
        return min(0.99, max(0, $this->getConfig('fuzzy_min_similarity')));
    }

    /**
     * Returns fuzzy prefix length.
     *
     * @link http://www.elasticsearch.org/guide/reference/query-dsl/flt-query.html
     * @return int
     */
    public function getFuzzyPrefixLength()
    {
        return (int) $this->getConfig('fuzzy_prefix_length');
    }

    /**
     * Returns fuzzy query boost parameter.
     *
     * @link http://www.elasticsearch.org/guide/reference/query-dsl/flt-query.html
     * @return float
     */
    public function getFuzzyQueryBoost()
    {
        return (float) $this->getConfig('fuzzy_query_boost');
    }

    /**
     * Returns query operator.
     *
     * @return string
     */
    public function getQueryOperator()
    {
        return $this->getConfig('query_operator');
    }

    /**
     * Checks if fuzzy query is enabled.
     *
     * @link http://www.elasticsearch.org/guide/reference/query-dsl/flt-query.html
     * @return bool
     */
    public function isFuzzyQueryEnabled()
    {
        return (bool) $this->getConfig('enable_fuzzy_query');
    }

    /**
     * Checks if ICU folding is enabled.
     *
     * @link http://www.elasticsearch.org/guide/reference/index-modules/analysis/icu-plugin.html
     * @return bool
     */
    public function isIcuFoldingEnabled()
    {
        return (bool) $this->getConfig('enable_icu_folding');
    }

    /**
     * Refreshes index
     *
     * @return NanoWebG_ElasticSearch_Model_Resource_Engine_Elasticsearch_Client
     */
    public function refreshIndex()
    {
        if ($this->getStatus()->indexExists($this->_index)) {
            $this->getIndex($this->_index)->refresh();
        }

        return $this;
    }

    /**
     * Handles search and facets.
     *
     * @param string $q
     * @param array $params
     * @param string $type
     * @return \Elastica\ResultSet
     * @throws Exception
     */
    public function search($q, $params = array(), $type = 'product')
    {
        if ($this->getStatus()->indexExists($this->_index)) {
            Varien_Profiler::start('ELASTICA_SEARCH');
            $facets = $this->_getAllFacets($q, $params);
            $filteredQuery = $this->_getFilteredQuery($q, $params);

            $query = \Elastica\Query::create($filteredQuery);

            $query->setFacets($facets)
                ->setFrom($params['offset'])
                ->setSize($params['limit']);

            $helper = Mage::helper('nanowebg_elasticsearch/elasticsearch');
            $demote_terms = explode(";",strtolower(rtrim($helper->getEngineConfig()['demote_category_terms'],";")));

            $include_demote = 1;

            $query_terms = explode(" ", trim(strtolower($q)));
                    
            foreach ($query_terms as $qterm) {
               
               foreach ($demote_terms as $dterm) {
                  if(strpos(strtolower(" ".$dterm),$qterm)){
                     $include_demote = 0;
                     break;
                  }
               }
               if(!$include_demote)
                     break;
            }

            if (isset($params['sort']) && !empty($params['sort'])) {

                $config = $this->_helper->getEngineConfig();
                foreach ($params['sort'] as $sort) {

                 
                    if($sort['_score']=='desc'){
                   
                        if($config['demote_without_image']==1){
                       
                            if($include_demote){
                                  $imageCatDemoteSort = array( array(
                                     'with_image' => 'desc',
                                     'demote_category_terms' => 'asc',
                                     ),
                                  );
                            }else{
                                 $imageCatDemoteSort = array( array(
                                     'with_image' => 'desc',
                                     ),
                                  );
                            }

                         }else{

                            if($include_demote){
                                  $imageCatDemoteSort = array( array(
                                      'demote_category_terms' => 'asc',
                                     ),
                                );
                             }
                         }
                   

                        if($imageCatDemoteSort) {  
                           $query->setSort($imageCatDemoteSort);
                           $query->addSort($sort);
                        }
                    }else{

                        $query->addSort($sort);

                    }

                }
            }

         
            Mage::dispatchEvent('nanowebg_elasticsearch_query_before', array(
                'client' => $this,
                'query' => $query,
            ));
            $result = $this->getIndex($this->_index)
                ->getType($type)
                ->search($query);

            Varien_Profiler::stop('ELASTICA_SEARCH');
            return $result;
        }

        return array();
    }

    /**
     * Stores index name.
     *
     * @param $index
     * @return NanoWebG_ElasticSearch_Model_Resource_Engine_Elasticsearch_Client
     */
    public function setIndex($index)
    {
        $this->_index = $index;

        return $this;
    }

    /**
     * Performs suggest query for specified text query.
     *
     * @param string $q
     * @return \Elastica\Response
     */
    public function suggest($q)
    {
        $params = array(
            'text' => $q,
            'simple_phrase' => array(
                'phrase' => array(
                    'field' => $this->_helper->getSuggestFieldName(),
                    'max_errors' => 0.9
                )
            )
        );
        $suggest = new \Elastica\Request($this->_index . '/_suggest', \Elastica\Request::POST, $params);
        $suggest->setConnection($this->getConnection());

        return $suggest->send();
    }

    /**
     * Returns attribute type for indexation.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return string
     */
    protected function _getAttributeType($attribute)
    {
        $type = 'string';
        if ($attribute->getBackendType() == 'decimal') {
            $type = 'double';
        } elseif ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
            $type = 'boolean';
        } elseif ($attribute->getBackendType() == 'datetime') {
            $type = 'date';
        } elseif ($this->_helper->isUsingOptions($attribute)) {
            $type = 'option'; // custom type to build an object for option id (facets) and label (search)
        } elseif ($attribute->usesSource() || $attribute->getFrontendClass() == 'validate-digits') {
            $type = 'integer';
        }

        return $type;
    }

    /**
     * @param string $q
     * @return \Elastica\Query\AbstractQuery
     */
    protected function _getBaseQuery($q)
    {
        if (empty($q)) {
            $baseQuery = new \Elastica\Query\MatchAll();
        } else {
            $q = $this->_helper->_searchQuery($q);
            $baseQuery = new \Elastica\Query\Bool();

            if ($this->isFuzzyQueryEnabled()) {
                $fuzzy = new \Elastica\Query\FuzzyLikeThis();
                $fuzzy->addFields($this->_getSearchFields(true, $q))
                    ->setLikeText($q)
                    ->setMinSimilarity($this->getFuzzyMinSimilarity())
                    ->setPrefixLength($this->getFuzzyPrefixLength())
                    ->setMaxQueryTerms($this->getFuzzyMaxQueryTerms())
                    ->setBoost($this->getFuzzyQueryBoost());
                $baseQuery->addShould($fuzzy);
            }

            $queryString = new \Elastica\Query\QueryString($q);
            $queryString->setFields($this->_getSearchFields(true, $q));
            $queryString->setDefaultOperator($this->getQueryOperator());
            $queryString->setBoost('2');
            $baseQuery->addShould($queryString);

           
        }

        return $baseQuery;
    }

    /**
     * @param string $q
     * @param array $params
     * @return array
     */
    protected function _getAllFacets($q, array $params)
    {       
        $facets = array();
        $hasStats = isset($params['stats']) && isset($params['stats']['fields']) && !empty($params['stats']['fields']);
        if ($hasStats) {
            foreach ($params['stats']['fields'] as $field) {
                $facet = new \Elastica\Facet\Statistical($field);
                $facet->setParam('field', $field);
                $facets[] = $facet;
            }
        }
        if (isset($params['facets']) && !empty($params['facets'])) {
            $properties = $this->_getIndexProperties();
            $filters = isset($params['filters']) ? $params['filters'] : array();

            foreach ($params['facets'] as $field => $conds) {
                $queryFilter = null;
                if (array_key_exists($field, $filters)) {
                    $_params = $params;
                   #AM unset comment out for ATM (Custom Code). Need to test for other clients
                  //  unset($_params['filters'][$field]);
                    $queryFilter = new \Elastica\Filter\Query($this->_getFilteredQuery($q, $_params));
                }
                if (null === $conds) {
                    // Terms Facet
                    if (!$hasStats && array_key_exists($field, $properties)) {
                        $facet = new \Elastica\Facet\Terms($field);
                        if ($properties[$field]['type'] == 'multi_field') {
                            $field .= '.untouched';
                        }
                        $facet->setField($field)
                            ->setAllTerms(true)
                            ->setSize($this->getFacetsMaxSize());
                        if ($queryFilter) {
                            $facet->setGlobal()->setFilter($queryFilter);
                        }
                        $facets[] = $facet;
                    }
                } elseif (is_array($conds)) {
                    if (isset($conds[0]['from']) && isset($conds[0]['to'])) {
                        if (!$hasStats) {
                            // Range Facet
                            $facet = new \Elastica\Facet\Range($field);
                            $facet->setField($field)
                                ->setRanges($this->_helper->_prepareRanges($conds));
                            $facets[] = $facet;
                        }
                    } else {
                        foreach ($conds as $cond) {
                            // Query Facet
                            $query = $this->_helper->_prepareQuery($field, $cond);
                            $facet = new \Elastica\Facet\Query($query);
                            $facet->setQuery(new \Elastica\Query\QueryString($query));
                            if ($queryFilter) {
                                $facet->setGlobal()->setFilter($queryFilter);
                            }
                            $facets[] = $facet;
                        }
                    }
                }
            }
        }

        return $facets;
    }

    /**
     * @param string $q
     * @param array $params
     * @return \Elastica\Query\Filtered
     */
    protected function _getFilteredQuery($q, array $params)
    {
        $baseQuery = $this->_getBaseQuery($q);
        $queryFilter = $this->_getQueryFilter($params);

        return new \Elastica\Query\Filtered($baseQuery, $queryFilter);
    }

    /**
     * Builds index properties for indexation according to available attributes and stores.
     *
     * @return array
     */
    protected function _getIndexProperties()
    {
        $cacheId = NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Index::CACHE_INDEX_PROPERTIES_ID;
        if ($properties = Mage::app()->loadCache($cacheId)) {
            return unserialize($properties);
        }

        $helper = $this->_helper;
        $indexSettings = $this->_getIndexSettings();
        $properties = array();

        $attributes = $helper->getSearchableAttributes(array('varchar', 'int'));
        foreach ($attributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ($this->_isIndexable($attribute)) {
                foreach (Mage::app()->getStores() as $store) {
                    /** @var $store Mage_Core_Model_Store */
                    $locale = $helper->getLocale($store);
                    $key = $helper->getFieldName($attribute, $locale);
                    $type = $this->_getAttributeType($attribute);
                    $weight = $attribute->getSearchWeight();
                    $languageCode = $helper->getLangCodeByStore($store);

                    if ($type === 'option') {
                        // Define field for option id
                        $properties[$key] = array(
                            'type' => 'integer',
                            'index' => 'not_analyzed',
                        );

                        // Define field for option label
                        $key .= '_' . $languageCode;
                        $properties[$key] = array(
                            'type' => 'string',
                            'boost' => $weight > 0 ? $weight : 1,
                        );
                        if (isset($indexSettings['analysis']['analyzer']['analyzer_' . $languageCode])) {
                            $properties[$key]['analyzer'] = 'analyzer_' . $languageCode;
                        }
                    } elseif ($type !== 'string') {
                        $properties[$key] = array(
                            'type' => $type,
                        );
                    } else {
                        $properties[$key] = array(
                            'type' => 'multi_field',
                            'fields' => array(
                                $key => array(
                                    'type' => $type,
                                    'boost' => $weight > 0 ? $weight : 1,
                                ),
                                'untouched' => array(
                                    'type' => $type,
                                    'index' => 'not_analyzed',
                                ),
                            ),
                        );
                        foreach (array_keys($indexSettings['analysis']['analyzer']) as $analyzer) {
                            $properties[$key]['fields'][$analyzer] = array(
                                'type' => 'string',
                                'analyzer' => $analyzer,
                                'boost' => $weight > 0 ? $weight : 1,
                            );
                        }
                    }
                }
            }
        }

        $attributes = $helper->getSearchableAttributes('text');
        foreach ($attributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            foreach (Mage::app()->getStores() as $store) {
                /** @var $store Mage_Core_Model_Store */
                $locale = $helper->getLocale($store);
                $key = $helper->getFieldName($attribute, $locale);
                $weight = $attribute->getSearchWeight();
                $properties[$key] = array(
                    'type' => 'multi_field',
                    'fields' => array(
                        $key => array(
                            'type' => 'string',
                            'boost' => $weight > 0 ? $weight : 1,
                        ),
                        'untouched' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        ),
                    ),
                );
                foreach (array_keys($indexSettings['analysis']['analyzer']) as $analyzer) {
                    $properties[$key]['fields'][$analyzer] = array(
                        'type' => 'string',
                        'analyzer' => $analyzer,
                        'boost' => $weight > 0 ? $weight : 1,
                    );
                }
            }
        }

        $attributes = $helper->getSearchableAttributes(array('static', 'varchar', 'decimal', 'datetime'));
        foreach ($attributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $key = $helper->getFieldName($attribute);
            if ($this->_isIndexable($attribute) && !isset($properties[$key])) {
                $type = $this->_getAttributeType($attribute);
                if ($type === 'option') {
                    continue;
                }
                $weight = $attribute->getSearchWeight();
                $properties[$key] = array(
                    'type' => $type,
                    'boost' => $weight > 0 ? $weight : 1,
                );
                if ($attribute->getBackendType() == 'datetime') {
                    $properties[$key]['format'] = $this->_dateFormat;
                }
            }
        }

        // Handle sortable attributes
        $attributes = $helper->getSortables();
        foreach ($attributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $type = 'string';
            if ($attribute->getBackendType() == 'decimal') {
                $type = 'double';
            } elseif ($attribute->getBackendType() == 'datetime') {
                $type = 'date';
                $format = $this->_dateFormat;
            }
            foreach (Mage::app()->getStores() as $store) {
                /** @var $store Mage_Core_Model_Store */
                $locale = $helper->getLocale($store);
                $key = $helper->getSortableFieldName($attribute, $locale);
                if (!array_key_exists($key, $properties)) {
                    $properties[$key] = array(
                        'type' => $type,
                        'index' => 'not_analyzed',
                    );
                    if (isset($format)) {
                        $properties[$key]['format'] = $format;
                    }
                }
            }
        }

        // Custom attributes
        $helper = Mage::helper('nanowebg_elasticsearch/elasticsearch');
        $lang_code = $helper->getLangCodeByStore(Mage::app()->getStore($storeId));

        $properties['visibility'] = array(
            'type' => 'integer',
        );
        $properties['store_id'] = array(
    
            'type' => 'integer',

        );
        $properties['in_stock'] = array(
            'type' => 'boolean',
        );
        
        #category names boost in search query
         $helper = $this->_helper;
         $config = $helper->getEngineConfig();
         
         foreach (Mage::app()->getStores() as $store) {
                                       
            $languageCode = $helper->getLangCodeByStore($store);
            $properties['category_terms_'.$languageCode] = array(
               'type' => 'string',
               'boost' => $config['category_boost'],
              
            );
           
        }

        

        if (Mage::app()->useCache('config')) {
            $lifetime = $this->_helper->getCacheTime();
            Mage::app()->saveCache(serialize($properties), $cacheId, array('config'), $lifetime);
        }

      
        return $properties;
    }

    /**
     * Returns indexation analyzers and filters configuration.
     *
     * @return array
     */
    protected function _getIndexSettings()
    {
        $indexSettings = array();
        $indexSettings['number_of_replicas'] = (int) $this->getConfig('number_of_replicas');
       
        $helper = $this->_helper;
        foreach (Mage::app()->getStores() as $store) {
           
            $languageCode = $helper->getLangCodeByStore($store);
            $lang = Zend_Locale_Data::getContent('en_GB', 'language', $helper->getLangCodeByStore($store));
            if (!in_array($lang, $this->_snowballLanguages)) {
                continue; 
            }
            $indexSettings['analysis']['analyzer']['analyzer_' . $languageCode] = array(
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => array('length', 'lowercase', 'asciifolding', 'snowball_' . $languageCode),
            );
            $indexSettings['analysis']['filter']['snowball_' . $languageCode] = array(
                'type' => 'snowball',
                'language' => $lang,
            );
        }

        if ($this->isIcuFoldingEnabled()) {
            foreach ($indexSettings['analysis']['analyzer'] as &$analyzer) {
                array_unshift($analyzer['filter'], 'icu_folding');
            }
            unset($analyzer);
        }

        $indexSettings = new Varien_Object($indexSettings);
        Mage::dispatchEvent('nanowebg_elasticsearch_index_settings', array('settings' => $indexSettings));

        return $indexSettings->getData();
    }

    /**
     * @param array $params
     * @return \Elastica\Filter\AbstractFilter
     */
    protected function _getQueryFilter(array $params)
    {
        if (!isset($params['filters']) || empty($params['filters'])) {
            $filters = '*';
        } else {
            $filters = $this->_helper->_prepareFilter($params['filters']);
        }
        $queryFilter = new \Elastica\Filter\Query(new \Elastica\Query\QueryString($filters));
        if (isset($params['range_filters']) && !empty($params['range_filters'])) {
            $andFilter = new \Elastica\Filter\BoolAnd();
            $andFilter->addFilter($queryFilter);
            $filter = new \Elastica\Filter\Range();
            foreach ($params['range_filters'] as $field => $rangeFilter) {
                $filter->addField($field, $rangeFilter);
            }
            $andFilter->addFilter($filter);
            $queryFilter = $andFilter;
        }

        return $queryFilter;
    }

    /**
     * Retrieves searchable fields according to text query.
     *
     * @param bool $onlyFuzzy
     * @param string $q
     * @return array
     */
    protected function _getSearchFields($onlyFuzzy = false, $q = '')
    {
        $properties = $this->_getIndexProperties();
        $fields = array();
        foreach ($properties as $key => $property) {
            if ($property['type'] == 'date'
                || ($property['type'] == 'multi_field' && $property['fields'][$key]['type'] == 'date')) {
                continue;
            }
            if (!is_bool($q)
                && ($property['type'] == 'boolean'
                    || ($property['type'] == 'multi_field' && $property['fields'][$key]['type'] == 'boolean'))) {
                continue;
            }
            if (!is_integer($q)
                && ($property['type'] == 'integer'
                    || ($property['type'] == 'multi_field' && $property['fields'][$key]['type'] == 'integer'))) {
                continue;
            }
            if (!is_double($q)
                && ($property['type'] == 'double'
                    || ($property['type'] == 'multi_field' && $property['fields'][$key]['type'] == 'double'))) {
                continue;
            }
            if (!$onlyFuzzy && $property['type'] == 'multi_field') {
                foreach (array_keys($property['fields']) as $field) {
                    if ($field != 'untouched') {
                        $fields[] = $key . '.' . $field;
                    }
                }
            } elseif (0 !== strpos($key, 'sort_by_')) {
                $fields[] = $key;
            }
        }
        $fields = new Varien_Object($fields);
        Mage::dispatchEvent('nanowebg_elasticsearch_fields', array('fields' => $fields));

        return $fields->getData();
    }

    /**
     * Checks if given index already exists.
     * Here because of a bug when calling exists() method directly on index object during index process.
     *
     * @param string $name
     * @return bool
     */
    protected function _indexExists($name)
    {
        $cluster = new \Elastica\Cluster($this);

        return in_array($name, $cluster->getIndexNames());
    }

    /**
     * Checks if attribute is indexable.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
    protected function _isIndexable($attribute)
    {
        return $this->_helper->isIndexable($attribute);
    }

    /**
     * Creates or updates Elasticsearch index.
     *
     * @link http://www.elasticsearch.org/guide/reference/mapping/core-types.html
     * @link http://www.elasticsearch.org/guide/reference/mapping/multi-field-type.html
     * @return NanoWebG_ElasticSearch_Model_Resource_Engine_Elasticsearch_Client
     * @throws Exception
     */
    protected function _prepareIndex()
    {
        try {
            $indexSettings = array('settings' => $this->_getIndexSettings());
            $index = $this->getIndex($this->_index);

            // Using custom _indexExists() method because of a bug in index creation when
            // calling exists() method on $index (ignores settings).
            if (!$this->_indexExists($this->_index)) {
                $indexSettings['settings']['number_of_shards'] = (int) $this->getConfig('number_of_shards');
                $index->create($indexSettings);
            } else {
                $index->setSettings(array(
                    'settings' => array(
                        // Only send allowed dynamic update properties
                        'number_of_replicas' => $indexSettings['settings']['number_of_replicas']
                    )
                ));
            }

            $mapping = new \Elastica\Type\Mapping();
            $mapping->setType($index->getType('product'));
            $mapping->setProperties($this->_getIndexProperties());

            Mage::dispatchEvent('nanowebg_elasticsearch_mapping_send_before', array(
                'client' => $this,
                'mapping' => $mapping,
            ));

            $mapping->send();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            throw $e;
        }

        return $this;
    }
}