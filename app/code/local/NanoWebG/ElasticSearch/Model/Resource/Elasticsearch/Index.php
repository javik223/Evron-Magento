<?php
/**
 * 
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * 
 */
class NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Index 

{
    const CACHE_INDEX_PROPERTIES_ID = 'elasticsearch_index_properties';

    const UNIQUE_KEY = 'master_key';

   
    /**
     * Initializes search engine.
     *
     * @see NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Client
     */
    protected $_client;


    protected $_usedFields = array(
        self::UNIQUE_KEY,
        'sku',
        'id',
        'store_id',
        'price',
        'score',
        'categories',
        'show_in_categories',
        'visibility',
        'in_stock',
        
    );

    
    public function __construct()
    {
        
       
             
        $this->_client = Mage::getResourceSingleton('nanowebg_elasticsearch/elasticsearch_client');

        if(!$this->test()){

            if( Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('Elastic repository is not available. Check login credentials.', null, 'elasticsearch_debug.log');
            }
        }


    }

    /**
     * Cleans caches.
     *
     * @return NanoWebG_ElasticSearch_Model_Resource_Elasticsearch_Index
     */
    public function cleanCache()
    {
        Mage::app()->removeCache(self::CACHE_INDEX_PROPERTIES_ID);

        return $this;
    }

    /**
     * Cleans index.
     *
     * @param int $storeId
     * @param int $id
     * @param string $type
     * @return NanoWebG_ElasticSearch_Model_Resource_Engine_Elasticsearch
     */
    public function cleanIndex($storeId = null, $id = null, $type = 'product')
    {
        $this->_client->cleanIndex($storeId, $id, $type);

        return $this;
    }

    
    /**
     * Gets index.
     *
     * @return mixed
     */
    public function getIndex($index_name)
    {
        return $this->_client->getIndex($index_name);
    }

    /**
     * Deletes index.
     *
     * @return mixed
     */
    public function deleteIndex()
    {
        return $this->_client->deleteIndex();
    }

    public function recreateIndex()
    {
            $this->deleteIndex();
            $this->_client->prepareIndex();
    }
    
     public function refreshIndex()
    {
        return $this->_client->refreshIndex();
    }


    public function removeDocs($storeId = null, $id = null, $type = 'product')
    {
         $this->_client->removeDocs($storeId, $id, $type);

    }

    public function rebuildProductIndexes($storeId = null, $productIds = null)
    {
          $fulltext_indexes = Mage::getResourceSingleton('nanowebg_elasticsearch/fulltext');

          $fulltext_indexes->rebuildIndex($this, $storeId,$productIds);
    }
    

    /**
     * Saves products data in index.
     *
     * @param int $storeId
     * @param array $indexes
     * @param string $type
     * @return NanoWebG_ElasticSearch_Model_Resource_Engine_Elasticsearch
     */
    public function saveEntityIndexes($storeId, $indexes, $type = 'product')
    {
       // $indexes = $this->addAdvancedIndex($indexes, $storeId, array_keys($indexes));
       # implement indexes here
        
        $helper = $this->_getHelper();
        $store = Mage::app()->getStore($storeId);
        $localeCode = $helper->getLocale($store);
        $languageCode = $helper->getLangCodeByStore($store);
        $searchables = $helper->getSearchableAttributes();
        $sortables = $helper->getSortables();

        foreach ($indexes as &$data) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $value = array_values(array_filter(array_unique($value)));
                }
                if (array_key_exists($key, $searchables)) {
                    /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                    $attribute = $searchables[$key];

                    if ($attribute->getBackendType() == 'datetime') {
                        foreach ($value as &$date) {
                            $date = $this->_getDate($store->getId(), $date);
                        }
                        unset($date);
                    } elseif ($attribute->usesSource() && !empty($value)) {

                        if ($attribute->getFrontendInput() == 'multiselect') {
                            $val = is_array($value) ? $value[0] : $value;
                            $val = explode(',', $val);

                        }elseif ($attribute->getFrontendInput() == 'select') {
                              
                              $val = $value;

                        }
                    

                        if ($helper->isUsingOptions($attribute)) {
                            $value = (array) $val;
                            $data[$key . '_' . $languageCode] = $value;
                            foreach ($data[$key . '_' . $languageCode] as &$val) {
                                $val = $attribute->setStoreId($store->getId())
                                    ->getFrontend()
                                    ->getOption($val);
                            }
                            unset($val);
                        }
                    }
                }
                if (array_key_exists($key, $sortables)) {
                    $val = is_array($value) ? $value[0] : $value;
                    /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                    $attribute = $sortables[$key];
                    $attribute->setStoreId($store->getId());
                    $key = $helper->getSortableFieldName($sortables[$key], $localeCode);
                    if ($attribute->usesSource()) {
                        $val = $attribute->getFrontend()->getOption($val);
                    } elseif ($attribute->getBackendType() == 'decimal') {
                        $val = (double) $val;
                    }
                    $data[$key] = $val;
                }
            }
            unset($value);
            $data['store_id'] = $store->getId();
        }
        unset($data);

        $docs = $this->_prepareDocs($indexes, $type, $localeCode);

        $this->_addDocs($docs);

        return $this;
    }

    /**
     * Checks Elasticsearch availability.
     *
     * @return bool
     */
    public function test()
    {
        if (null !== $this->_test) {
            return $this->_test;
        }

        try {
            $this->_client->getStatus();
            $this->_test = true;
        } catch (Exception $e) {
            if ($this->_getHelper()->isDebugMode()) {
               // $this->_getHelper()->errorMode('Elasticsearch engine is not available');
                if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                    Mage::log('Elasticsearch engine is not available. Error:'.$e->getMessage(), null, 'elasticsearch_debug.log');
                }
            }
               Mage::getSingleton('adminhtml/session')->addError('Elastic Search repository is not available. Message: '.$e->getMessage());
            
            $this->_test = false;
        }

        return $this->_test;
    }

    public function connected()
    {
        return $this->test();
        //return true;
    }
    /**
     * Adds documents to index.
     *
     * @param array $docs
     * @return NanoWebG_ElasticSearch_Model_Resource_Engine_Elasticsearch
     */
    protected function _addDocs($docs)
    {
        if (!empty($docs)) {
            $this->_client->addDocuments($docs);
        }
        $this->_client->refreshIndex();

        return $this;
    }

    /**
     * Creates and prepares document for indexation.
     *
     * @param int $entityId
     * @param array $index
     * @param string $type
     * @return mixed
     */
    protected function _createDoc($entityId, $index, $type = 'product')
    {
        return $this->_client->createDoc($index[self::UNIQUE_KEY], $index, $type);
    }


    /**
     * Remove documents from index.
     *
     * @param array $docs
     * @return mixed
     */
    public function removeDocuments($storeId =null,$docIds = null, $type = 'product')
    {
        return $this->_client->cleanIndex($storeId, $docIds, $type);
    }

    /**
     * Returns search helper.
     *
     * @return NanoWebG_ElasticSearch_Helper_Elasticsearch
     */
    protected function _getHelper()
    {
        return Mage::helper('nanowebg_elasticsearch/elasticsearch');
    }

    


    protected function _prepareDocs($docsData, $type, $localeCode = null)
    {
        
        if (!is_array($docsData) || empty($docsData)) {
            return array();
        }

        $docs = array();
        foreach ($docsData as $entityId => $index) {
            $index[self::UNIQUE_KEY] = $entityId . '|' . $index['store_id'];
            $index['id'] = $entityId;
            $index = $this->_prepareIndexData($index, $localeCode);
            $docs[] = $this->_createDoc($entityId, $index, $type);
        }

        return $docs;
    }


     /**
     * Prepares index data before indexing.
     *
     * @param array $data
     * @param string $localeCode
     * @return array
     */
    protected function _prepareIndexData($data, $localeCode = null)
    {
        if (!is_array($data) || empty($data)) {
            return array();
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $this->_usedFields)) {
                continue;
            } elseif ($key == 'options') {
                unset($data[$key]);
                continue;
            }
            $field = $this->_getHelper()->getFieldName($key, $localeCode);
            $field = str_replace($this->_advancedIndexFieldsPrefix, '', $field);
            if ($field != $key) {
                $data[$field] = $value;
                unset($data[$key]);
            }
        }

        return $data;
    }

}
