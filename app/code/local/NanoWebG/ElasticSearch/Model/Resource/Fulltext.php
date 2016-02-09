<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class NanoWebG_ElasticSearch_Model_Resource_Fulltext extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     *
     * @var array
     */
    protected $_searchables     = null;

    /**
     * 
     * @var string
     */
    protected $_separator                = '|';

    /**
     * 
     * @var array
     */
    protected $_dates                    = array();

    /**
     * 
     * @var array
     */
    protected $_productTypes             = array();

    /**
     * 
     * @var object
     */
    protected $_ElasticEngine                   = null;

    /**
     * 
     * @deprecated after 1.6.1.0
     * @var bool
     */
    protected $_allowTableChanges       = true;


    protected $_productIndexes = array();

    protected $_helper;


    protected $_categoryNames;

    protected $_categoryDemotes;


     protected $_allowedImageExt = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('catalogsearch/fulltext', 'product_id');
        $this->_helper = Mage::helper('nanowebg_elasticsearch/elasticsearch');

        $engine_config = $this->_helper->getEngineConfig();

        if($engine_config['phptimeout'] && ctype_digit($engine_config['phptimeout'])){

           $timeout = (int)$engine_config['phptimeout'] * 60;
           set_time_limit ($timeout); 
          
        }

        if($engine_config['phpmemory'] && ctype_digit($engine_config['phpmemory'])){

           $memory = $engine_config['phpmemory']."M";
           ini_set("memory_limit",$memory);
        }
      
    }

    /**
     * 
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     *
     * @param  int|null $store_id
     * @param  int|array|null $product_ids
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function rebuildIndex($elasticsearchindex = null, $store_id = null, $product_ids = null)
    {
        if(is_null($elasticsearchindex)){

              return $this;
        }

        if (is_null($store_id)) {
            $stores = array_keys(Mage::app()->getStores());

            foreach ($stores as $store_id) {

                $this->_rebuildStoreIndex($elasticsearchindex,$store_id, $product_ids);
            }

        } elseif(is_array($store_id)) {

  
            foreach ($store_id as $s_id) {

                $this->_rebuildStoreIndex($elasticsearchindex,$s_id, $product_ids);
            }
            
        }else{

            
               $this->_rebuildStoreIndex($elasticsearchindex,$store_id, $product_ids);
        } 

        return $this;
    }

    
    /**
     * 
     * @return array | products searchable attributes 
     */
    public function getProductIndexes()
    {
        return $this->_productIndexes;
    }



    
    /**
     * Retrieve searchable products per store
     *
     * @param int $storeId
     * @param array $staticFields
     * @param array|int $productIds
     * @param int $lastProductId
     * @param int $limit
     * @return array
     */
    protected function _getSearchableProducts($storeId, array $staticFields, $productIds = null, $lastProductId = 0, $limit = 1000)
    {
        $websiteId      = Mage::app()->getStore($storeId)->getWebsiteId();

        
        $writeAdapter   = $this->_getWriteAdapter();

        $select = $writeAdapter->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('catalog/product')),
                array_merge(array('entity_id', 'type_id'), $staticFields)
            )
            ->join(
                array('website' => $this->getTable('catalog/product_website')),
                $writeAdapter->quoteInto(
                    'website.product_id=e.entity_id AND website.website_id=?',
                    $websiteId
                ),
                array()
            )
            ->join(
                array('stock_status' => $this->getTable('cataloginventory/stock_status')),
                $writeAdapter->quoteInto(
                    'stock_status.product_id=e.entity_id AND stock_status.website_id=?',
                    $websiteId
                ),
                array('in_stock' => 'stock_status')
            );

        if (!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $select->where('e.entity_id>?', $lastProductId)
            ->limit($limit)
            ->order('e.entity_id');

               
        $result = $writeAdapter->fetchAll($select);

       
        return $result;
    }

    
    /**
     * Delete search index data for store
     *
     * @param int $storeId Store View Id
     * @param int $productId Product Entity Id
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function cleanIndex($storeId = null, $productId = null)
    {
       // if ($this->_ElasticEngine) {
       //     $this->_ElasticEngine->cleanIndex($storeId, $productId);
       // }

        return $this;
    }



    /**
     * Rebuild store specific search index
     *
     * @param int $store_id 
     * @param int|array $product_ids 
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _rebuildStoreIndex($elasticsearhindex, $storeId, $productIds = null)
    {
                 
        #get searchable attributes
        $staticFields = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }

        $dynamicFields = array(
            'varchar'   => array_keys($this->_getSearchableAttributes('varchar')),
            'int'       => array_keys($this->_getSearchableAttributes('int')),
            'decimal'   => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime'  => array_keys($this->_getSearchableAttributes('datetime')),
            'text'      => array_keys($this->_getSearchableAttributes('text')),
           
        );

        # get visibility and status filters
        $visibility     = $this->_getSearchableAttribute('visibility');
        $status         = $this->_getSearchableAttribute('status');
        $statusVals     = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
       
        $allowedVisibilityValues  = Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();

        $lastProductId = 0;
       
        while (true){
            $products = $this->_getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);

            
            if (!$products) {
                break;
            }

            $productAttributes = array();
            $productRelations  = array();
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];

                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            $productIndexes    = array();
            $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);

            
            foreach ($products as $productData) {

                
                if (!isset($productAttributes[$productData['entity_id']])) {
                         continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                
              
                if (!isset($productAttr[$visibility->getId()])
                    || !in_array((int) $productAttr[$visibility->getId()], $allowedVisibilityValues) ) {
                    
                        continue;
                }
                if (!isset($productAttr[$status->getId()]) || !in_array($productAttr[$status->getId()], $statusVals)) {
                        continue;
                }

                $productIndex = array(
                    $productData['entity_id'] => $productAttr
                );

                
                if ($productChildren = $productRelations[$productData['entity_id']]) {
                    foreach ($productChildren as $productChildId) {
                        if (isset($productAttributes[$productChildId])) {
                            $productIndex[$productChildId] = $productAttributes[$productChildId];
                        }
                    }
                }
                
                #remove disabled products from index
                foreach ($productIndex as $key => $prod) {
                    
                    if($prod[$status->getId()] == 2){
                          unset($productIndex[$key]);
                    }
                }

                                
                $index = $this->_prepareProductIndex($productIndex, $productData, $storeId);

                $productIndexes[$productData['entity_id']] = $index;
                

                $this->_productIndexes[$storeId][$productData['entity_id']] = $index;

               
            }

             
            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log("-------------------------------------------------------------------", NULL, 'elasticsearch_debug.log', true);
                Mage::log("Mem.Used:".memory_get_usage()." "."Indexing Started at:".date('Y-m-d H:i:s'), NULL, 'elasticsearch_debug.log', true);
            }
             
                     
            foreach ($this->_productIndexes as $storeId => $products_indexes) {

            
                if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                    Mage::log("Mem.Used:".memory_get_usage()." "." next save at:".date('Y-m-d H:i:s'), NULL, 'elasticsearch_debug.log', true);
                }
        
                  $elasticsearhindex->saveEntityIndexes($storeId,$products_indexes);

                if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                    Mage::log("saved..", NULL, 'elasticsearch_debug.log', true);
                }


            }
            
             if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log("Mem.Used:".memory_get_usage()." "."Indexing Completed at:".date('Y-m-d H:i:s'), NULL, 'elasticsearch_debug', true);
                Mage::log("====================================================================", NULL, 'elasticsearch_debug', true);
            }
           
         
        }

    //   exit;

        return $this;
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return Mage_Eav_Model_Config
     */
    public function getEavConfig()
    {
        return Mage::getSingleton('eav/config');
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @return array
     */
    protected function _getSearchableAttributes($backendType = null)
    {
        if (is_null($this->_searchables)) {
            $this->_searchables = array();

            $productAttributeCollection = Mage::getResourceSingleton('nanowebg_elasticsearch/catalog_product_attribute_collection');

            $productAttributeCollection->addToIndexFilter(true);
           
            $attributes = $productAttributeCollection->getItems();

                        
            Mage::dispatchEvent('catelogsearch_searchable_attributes_load_after', array(
                'engine' => $this->_ElasticEngine,
                'attributes' => $attributes
            ));

            $entity = $this->getEavConfig()
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
                ->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->_searchables = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchables as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

           
            return $attributes;
        }

        
        return $this->_searchables;
    }

    
    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @return Mage_Eav_Model_Entity_Attribute
     */
    protected function _getSearchableAttribute($attribute)
    {
        $attributes = $this->_getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->getEavConfig()->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute);
    }

    /**
     * Returns expresion for field unification
     *
     * @param string $field
     * @param string $backendType
     * @return Zend_Db_Expr
     */
    protected function _unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = Mage::getResourceHelper('catalogsearch')->castField(
                $this->_getReadAdapter()->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s'));
        } else {
            $expr = Mage::getResourceHelper('catalogsearch')->castField($field);
        }
        return $expr;
    }

    /**
     * Load product(s) attributes
     *
     * @param int $storeId
     * @param array $productIds
     * @param array $attributeTypes
     * @return array
     */
    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        

        $result  = array();
        $selects = array();
        $adapter = $this->_getWriteAdapter();
        $ifStoreValue = $adapter->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable(array('catalog/product', $backendType));
                $selects[] = $adapter->select()
                    ->from(
                        array('t_default' => $tableName),
                        array('entity_id', 'attribute_id'))
                    ->joinLeft(
                        array('t_store' => $tableName),
                        $adapter->quoteInto(
                            't_default.entity_id=t_store.entity_id' .
                                ' AND t_default.attribute_id=t_store.attribute_id' .
                                ' AND t_store.store_id=?',
                            $storeId),
                        array('value' => $this->_unifyField($ifStoreValue, $backendType)))
                    ->where('t_default.store_id=?', 0)
                    ->where('t_default.attribute_id IN (?)', $attributeIds)
                    ->where('t_default.entity_id IN (?)', $productIds);

                     
            }
        }

       
        if ($selects) {
            $select = $adapter->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
            $query = $adapter->query($select);
            while ($row = $query->fetch()) {
                $result[$row['entity_id']][$row['attribute_id']] = $row['value'];
            }
        }
        
        return $result;
    }

    /**
     * Retrieve Product Type Instance
     *
     * @param string $typeId
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    protected function _getProductTypeInstance($typeId)
    {
        if (!isset($this->_productTypes[$typeId])) {
            $productEmulator = $this->_getProductEmulator();
            $productEmulator->setTypeId($typeId);

            $this->_productTypes[$typeId] = Mage::getSingleton('catalog/product_type')
                ->factory($productEmulator);
        }
        return $this->_productTypes[$typeId];
    }

    /**
     * Return all product children ids
     *
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array
     */
    protected function _getProductChildIds($productId, $typeId)
    {
        $typeInstance = $this->_getProductTypeInstance($typeId);
        $relation = $typeInstance->isComposite()
            ? $typeInstance->getRelationInfo()
            : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $this->getTable($relation->getTable())),
                    array($relation->getChildFieldName()))
                ->where("{$relation->getParentFieldName()}=?", $productId);
            if (!is_null($relation->getWhere())) {
                $select->where($relation->getWhere());
            }
            return $this->_getReadAdapter()->fetchCol($select);
        }

        return null;
    }

    /**
     * Retrieve Product Emulator (Varien Object)
     *
     * @return Varien_Object
     */
    protected function _getProductEmulator()
    {
        $productEmulator = new Varien_Object();
        $productEmulator->setIdFieldName('entity_id');

        return $productEmulator;
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return string
     */
    protected function _prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $attrCode = $attribute->getAttributeCode();

                        

            if (isset($productData[$attrCode])) {
                $value = $this->_getAttributeValue($attribute->getId(), $productData[$attrCode], $storeId);
                if ($value) {
                    # configurables/grouped
                    if (isset($index[$attrCode])) {
                        if (!is_array($index[$attrCode])) {
                            $index[$attrCode] = array($index[$attrCode]);
                        }
                        $index[$attrCode][] = $value;
                    }
                    
                    else {
                        $index[$attrCode] = $value;
                    }
                }
            }

            

        }
      
        

        foreach ($indexData as $entityId => $attributeData) {

            foreach ($attributeData as $attributeId => $attributeValue) {


                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);

                
                if (!is_null($value) && $value !== false) {
                    $attrCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();
                   

                    if (isset($index[$attrCode])) {
                        $index[$attrCode][$entityId] = $value;
                    } else {
                        $index[$attrCode] = array($entityId => $value);
                    }
                }
            }
        }

        $product = $this->_getProductEmulator()
                ->setId($productData['entity_id'])
                ->setTypeId($productData['type_id'])
                ->setStoreId($storeId);
            $typeInstance = $this->_getProductTypeInstance($productData['type_id']);
            if ($data = $typeInstance->getSearchableData($product)) {
                $index['options'] = $data;
            }
       

        if (isset($productData['in_stock'])) {
            $index['in_stock'] = $productData['in_stock'];
        }
        
        if(isset($storeId)){
            $index['store_id'] = $storeid;
        }

        $index['entity_id'] = $productData['entity_id'];


        #get categories.prices, with image, promoted
        $index = $this->addCatsPricesFlattenIndexFields($index, $storeId);
 
        return $index;
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $value
     * @param int $storeId
     * @return mixed
     */
    protected function _getAttributeValue($attributeId, $value, $storeId)
    {
        
        if(!$this->_getSearchableAttribute($attributeId)){
            return null;
        }
        $attribute = $this->_getSearchableAttribute($attributeId);
        if (!$attribute->getIsSearchable()) {
           
                return null;
           
        }

        if ($attribute->usesSource()) {
        
                return $value;
       
            $attribute->setStoreId($storeId);
            $value = $attribute->getSource()->getOptionText($value);

            if (is_array($value)) {
                $value = implode($this->_separator, $value);
            } elseif (empty($value)) {
                $inputType = $attribute->getFrontend()->getInputType();
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    return null;
                }
            }
        } elseif ($attribute->getBackendType() == 'datetime') {
            $value = $this->_getStoreDate($storeId, $value);
        } else {
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == 'price') {
                $value = Mage::app()->getStore($storeId)->roundPrice($value);
            }
        }

        $value = preg_replace("#\s+#siu", ' ', trim(strip_tags($value)));

        return $value;
    }

    
    /**
     * Retrieve Date value for store
     *
     * @param int $storeId
     * @param string $date
     * @return string
     */
    protected function _getStoreDate($storeId, $date = null)
    {
        if (!isset($this->_dates[$storeId])) {
            $timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeId);
            $locale   = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId);
            $locale   = new Zend_Locale($locale);

            $dateObj = new Zend_Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->_dates[$storeId] = array($dateObj, $locale->getTranslation(null, 'date', $locale));
        }

        if (!is_empty_date($date)) {
            list($dateObj, $format) = $this->_dates[$storeId];
            $dateObj->setDate($date, Varien_Date::DATETIME_INTERNAL_FORMAT);

            return $dateObj->toString($format);
        }

        return null;
    }





    // Deprecated methods

    /**
     * Set whether table changes are allowed
     *
     * @deprecated after 1.6.1.0
     * @param bool $value
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function setAllowTableChanges($value = true)
    {
        $this->_allowTableChanges = $value;
        return $this;
    }

    /**
     * Update category products indexes
     *
     * deprecated after 1.6.2.0
     *
     * @param array $productIds
     * @param array $categoryIds
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function updateCategoryIndex($productIds, $categoryIds)
    {
        return $this;
    }


    public function addCatsPricesFlattenIndexFields($index, $storeId, $productIds = null)
    {
        
        
        if ($productIds===null || !is_array($productIds)) {
            $productIds = array();

           
            $productIds[] = $index['entity_id'];
        }

       
        $categoryData = $this->_getCatalogCategoryData($storeId, $productIds);
        $priceData = $this->_getCatalogProductPriceData($productIds);
        $imageAndPromoData = $this->_getImageAndPromoData($productIds);


                foreach ($categoryData as $productId => $catData) {
 
                    if(is_array($catData)){

                       foreach ($catData as $cat => $value) {
                        $index[$cat][$index['entity_id']] = $value;
                   
                       }
                    }else{
                        $index[$catData] = $catData;
                    }
                }

                
                 foreach ($priceData as $productId => $priceData) {

                    if(is_array($priceData)){

                        foreach ($priceData as $price => $value) {
                        
                            $index[$price] = $value;

                        }
                     
                    }else{
                        $index['price'] = $priceData;
                    }
                    
                 }
               
                 foreach ($imageAndPromoData as $productId => $data) {
                    
                    if(is_array($data)){

                       foreach ($data as $name => $value) {
                        $index[$name][$index['entity_id']] = $value;
                     
                       }

                    }else{
                        $index[$data] = $data;
                    } 

                 }
     
        unset($productData);
        unset($categoryData);
        unset($priceData);
        unset($imageAndPromoData);

        return $index;
    }

   
    protected function _getCategoryDemotes($storeId){

     if($this->_categoryDemotes[$storeId]){
        return $this->_categoryDemotes[$storeId];
     }
 
        $helper = Mage::helper('nanowebg_elasticsearch/elasticsearch');
        $demote_config = rtrim($helper->getEngineConfig()['demote_category_terms'],";");
        $configDemotes = explode(";",$demote_config);

        $this->_categoryDemotes[$storeId] = $configDemotes;

        return $this->_categoryDemotes[$storeId];
    }
    /**
     * Retrieves category names.
     *
     * @param int $storeId
     * @return array
     */
    protected function _getCategoryNames($lang_code)
    {
     
      
      if($this->_categoryNames[$lang_code]){

          return $this->_categoryNames[$lang_code];
      }

      $cats_array = array();

      $categories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('*');
      
      

      foreach ($categories as $category) {

          if($category->getId()<=2)
            continue;

           $cats_array['name'][$category->getId()] = $category->getName();

          if(!$category->getIncludeInMenu())
             continue;

          $cat_terms = explode(" ", $category->getName());
          $terms = '';
          $names = array();

          foreach ($cat_terms as $key => $term) {
              
             $term = str_replace(",", "", $term);
             $term = html_entity_decode($term);
             $term = trim($term);
             

              if($term && !in_array($term, $names)){

                   if(strlen($term)>2){
                        $names[] = $term;
                        $terms .= $term.', ';
                      
                      if($lang_code=='en'){

                        $pos  = strrpos($term,'ies');
                        
                        if(!$pos===false && $pos===strlen($term)-3){ 
                       
                             $terms .= substr($term, 0, $pos).'y, ';
                             
                        }elseif(preg_match('/s$/', strtolower($term))) {
                           
                           $terms .= substr($term, 0, -1).', ';
                           $names[] = substr($term, 0, -1);
                        }
                      }
                   }
              }
          }

          $terms = str_replace(" ,", "", $terms);
          $terms = trim(rtrim($terms, ", "));
          
          $final_terms = explode(", ", $terms);
          $final_terms = array_unique($final_terms, SORT_REGULAR);
         
          if($final_terms){
               $cats_array[$category->getId()] = $final_terms;
      
          }
      }
          
      
      $this->_categoryNames[$lang_code] = $cats_array;
      
      return $this->_categoryNames[$lang_code];

    }

    protected function _getCategoryName($lang_code, $cat_id){

        if(!$cat_id)
           return '';

        return $this->_categoryNames[$lang_code]['name'][$cat_id];
    }
    /**
     * Retrieves category data for advanced index.
     *
     * @param int $storeId
     * @param array $productIds
     * @param bool $visibility
     * @return array
     */
    protected function _getCatalogCategoryData($storeId, $productIds, $visibility = true)
    {
        
       
        $adapter = $this->_getWriteAdapter();
      
        $columns = array(
            'product_id' => 'product_id',
            'parents' => new Zend_Db_Expr("GROUP_CONCAT(IF(is_parent = 1, category_id, '') SEPARATOR ' ')"),
            'anchors' => new Zend_Db_Expr("GROUP_CONCAT(IF(is_parent = 0, category_id, '') SEPARATOR ' ')"),
            'positions' => new Zend_Db_Expr("GROUP_CONCAT(CONCAT(category_id, '_', position) SEPARATOR ' ')"),
        );

        if ($visibility) {
            $columns['visibility'] = 'visibility';
        }

        $select = $adapter->select()
            ->from(array($this->getTable('catalog/category_product_index')), $columns)
            ->where('product_id IN (?)', $productIds)
            ->where('store_id = ?', $storeId)
            ->group('product_id');

        
       
        $result = array();
      
        $helper = Mage::helper('nanowebg_elasticsearch/elasticsearch');
        $lang_code = $helper->getLangCodeByStore(Mage::app()->getStore($storeId));

        $categoryTerms = $this->_getCategoryNames($lang_code);
        $configDemotes = $this->_getCategoryDemotes($storeId);
        
       
        foreach ($adapter->fetchAll($select) as $row) {

           
            $category_terms = array();
            $demoteCategoryTerms = array();
            $demote_int = 0;
            $cat_ids = explode(' ', trim($row['parents']));

            foreach ($cat_ids as $id) {

                foreach ($categoryTerms[$id] as $key => $term) {
                     
                     if(!in_array($term, $category_terms))
                        $category_terms[] = $term;

                    
                }


                 foreach ($configDemotes as $key => $demoteterm) {
                         
                           if(strpos(strtolower(' '.trim($this->_getCategoryName($lang_code,$id))),strtolower(trim($demoteterm)))){
                                $demote_int = 1;
                                break;
                           }
                 }  
               
            }

            $demoteCategoryTerms[] = $demote_int;

            $category_names = implode(", ", $category_terms);
            $cat_names_label = 'category_terms_'.$lang_code;
          
            $demote_cat_names_label = 'demote_category_terms';
            $demote_cat_names = $demoteCategoryTerms;

            $data = array(
                 'categories' => array_values(array_filter($cat_ids)), 
                 'show_in_categories' => array_values(array_filter(explode(' ', $row['anchors']))), 
                  $cat_names_label => $category_names,
                  $demote_cat_names_label => $demote_cat_names,
             
            );
            foreach (explode(' ', $row['positions']) as $value) {
                list($categoryId, $position) = explode('_', $value);
                $key = sprintf('pos_cat_%d', $categoryId);
                $data[$key] = $position;
            }
            if ($visibility) {
              
                $data['visibility'] = $row['visibility'];
            }

            $result[$row['product_id']] = $data;
        }
        
        
        return $result;
    }

    /**
     * Retrieves product price data for advanced index.
     *
     * @param array $productIds
     * @return array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $adapter = $this->_getWriteAdapter();
     
        $select = $adapter->select()
            ->from($this->getTable('catalog/product_index_price'),
                array('entity_id', 'customer_group_id', 'website_id', 'min_price'));

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            if (!isset($result[$row['entity_id']])) {
                $result[$row['entity_id']] = array();
            }
          
            $key = sprintf('price_%s_%s', $row['customer_group_id'], $row['website_id']);
            $result[$row['entity_id']][$key] = round($row['min_price'], 2);
        }

        return $result;
    }

    protected function _getImageAndPromoData($productIds = null)
    {

        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getTable('catalog/product_attribute_media_gallery'),
                array('entity_id', 'value'));

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $result = array();
        $allowedImageExt = $this->_allowedImageExt;
       
        foreach ($adapter->fetchAll($select) as $row) {
        
            if(!isset($result[$row['entity_id']]['with_image'])){
                
                $ext = pathinfo($row['value'], PATHINFO_EXTENSION);

                if(in_array($ext, $allowedImageExt)){
                  
                    $result[$row['entity_id']]['with_image'] = 1;
                    break;
                }
            }
        }
        

        return $result;
    }
}
