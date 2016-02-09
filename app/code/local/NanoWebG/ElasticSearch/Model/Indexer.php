<?php
/**
 * 
 * @category    NanoWebGgroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * 
 */
class NanoWebG_ElasticSearch_Model_Indexer 
extends Mage_Index_Model_Indexer_Abstract

{
  
    const EVENT_MATCH_RESULT_KEY = 'nanowebg_elasticsearch_match_result';
 
    /**
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
            Mage_Index_Model_Event::TYPE_DELETE
        )
    );
 
    protected $_config;
    protected $_stores;


    public function __construct()
    {
        
        parent::__construct();

        $this->_config = Mage::helper('nanowebg_elasticsearch/elasticsearch')
                              ->getEngineConfig();

        $this->_stores = explode(",",$this->_config['stores']);
    }

   

    /**
     * Get Name
     * @return string
     */
    public function getName()
    {
        return 'Elastic Search';
    }
 
    /**
     * Get Description
     * @return string
     */
    public function getDescription()
    {
        return 'Rebuild Elastic Search Index';
    }
 
    /**
     * Save data in the event object to be processed 
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
          $dataObj = $event->getDataObject();
          $storeId  = $dataObj->getStoreId();
         
     
     if($dataObj->getId()){

        if($storeId){
           $event->addNewData('nanowebg_elasticsearch_store_id', $storeId); 
        }

        if($event->getType() == Mage_Index_Model_Event::TYPE_SAVE){
            $event->addNewData('nanowebg_elasticsearch_update_product_id', $dataObj->getId());

        }elseif($event->getType() == Mage_Index_Model_Event::TYPE_DELETE){
            $event->addNewData('nanowebg_elasticsearch_delete_product_id', $dataObj->getId());
        }elseif($event->getType() == Mage_Index_Model_Event::TYPE_MASS_ACTION){
            $event->addNewData('nanowebg_elasticsearch_mass_action_product_ids', $dataObj->getProductIds());

        }
     }else{
          $this->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
     }
    }
 
    /**
     * Process event
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
    

        $data = $event->getNewData();
        if(!empty($data['nanowebg_elasticsearch_update_product_id'])){
            $this->_processOnUpdateEvent($data['nanowebg_elasticsearch_update_product_id'],$data['nanowebg_elasticsearch_store_id']);
        }elseif(!empty($data['nanowebg_elasticsearch_delete_product_id'])){
            $this->_processOnDeleteEvent($data['nanowebg_elasticsearch_delete_product_id'],$data['nanowebg_elasticsearch_store_id']);
        }elseif(!empty($data['nanowebg_elasticsearch_mass_action_product_ids'])){
             $this->_processOnMassActionEvent($data['nanowebg_elasticsearch_mass_action_product_ids'],$data['nanowebg_elasticsearch_store_id']);
        }
   
    }
    
    /**
     * Process onUpdate event
     * @param id string
     */

    protected function _processOnUpdateEvent($productid,$storeId = null)
    {
        
        
        
        if(null === $storeId){
            $storeId = $this->_stores;
        }

        $index = Mage::getResourceSingleton('nanowebg_elasticsearch/elasticsearch_index');
       
        try{
             $index->removeDocs($storeId,$productid,'product');

             $index->rebuildProductIndexes($storeId,$productid,'product');
           

        }catch(Exception $e){

        }
        
       
        
        # rebuild parent product index 
        $productids = Mage::getModel('catalog/product_type_configurable')
                            ->getParentIdsByChild((int)$productid );
                 
        if(is_array($productids) && count($productids)){

           try{
             $index->removeDocs($storeId,$productids,'product');

             $index->rebuildProductIndexes($storeId,$productids,'product');
            


            }catch(Exception $e){
            
           }

        }

       
        $index->refreshIndex();
        
    }

    protected function _processOnDeleteEvent($productid,$storeId = null)
    {
        
        if(null === $storeId){
            $storeId = $this->_stores;
        }

        $index = Mage::getResourceSingleton('nanowebg_elasticsearch/elasticsearch_index');
      
        try{
             $index->removeDocs($storeId,$productid,'product');
   

        }catch(Exception $e){
            
        }
        
        # rebuild parent product index 
        $productids = Mage::getModel('catalog/product_type_configurable')
                            ->getParentIdsByChild((int)$productid );
                 
        if(is_array($productids) && count($productids)){

           try{
             $index->removeDocs($storeId,$productids,'product');

             $index->rebuildProductIndexes($storeId,$productids,'product');
            

            }catch(Exception $e){

           }

        }

       
        $index->refreshIndex();

    }

    protected function _processOnMassActionEvent($data)
    {
        # no need to intercept

    }

    /**
     * match whether the reindexing should be fired
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }
        $entity = $event->getEntity();
        $result = true;
        if($entity != Mage_Catalog_Model_Product::ENTITY){
            return;
        }
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);
        return $result;
    }
 
    /**
     * Rebuild all elastic index data
     */
    public function reindexAll()
    {
	    
       
        $index = Mage::getResourceSingleton('nanowebg_elasticsearch/elasticsearch_index');

        
        
           if($result = $index->connected()){

                if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                   Mage::log('Index inititated', null, 'elasticsearch_debug.log');
                }

             // $index->deleteIndex();
              $index->recreateIndex();
              
              $productIds = null;

              $index->rebuildProductIndexes($this->_stores,$productIds);
              $index->refreshIndex();
              
         

           }else{

                if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                   Mage::log('Could not connect to the Index. Index is NOT inititated', null, 'elasticsearch_debug.log');
                }
               $this->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
           } 

        

    }

    public function changeStatus($params){

        #shell method;
       
    }
}