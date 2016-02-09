<?php
/**
 * ElasticSearch Model Observer.
 *
 * @category    NanoWebGroup
 * @package     Nanowebg_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Observer
{
    /**
     * Switches Elastic indexer to 'Reindexing Required' state when attribute searchable fields are changed
     *
     * @param Varien_Event_Observer $observer
     */

    public function requireElasticSearchReindex(Varien_Event_Observer $observer){

       if (Mage::helper('nanowebg_elasticsearch')->isActiveEngine()) {
           
            $attribute = $observer->getEvent()->getAttribute();

            if ($attribute->getData('is_searchable') != $attribute->getOrigData('is_searchable')
               || $attribute->getData('is_visible_in_advanced_search') != $attribute->getOrigData('is_visible_in_advanced_search')
               || $attribute->getData('is_filterable_in_search') != $attribute->getOrigData('is_filterable_in_search')) {
               
              
                Mage::getSingleton('index/indexer')->getProcessByCode('nanowebg_elasticsearch_indexer')
                    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }
    }

   public function checkProcess(Varien_Event_Observer $observer){

      if (Mage::helper('nanowebg_elasticsearch')->isActiveEngine()) { 

       
       $process = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
       $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);

       Mage::throwException(Mage::helper('index')->__('Catalog Search Index is unavailable while the catalog search engine is set to Elastic Search')); 

      }
       

   }
   
}