<?php
/**
 * Handles custom product collection filtering by overriding the default layer model.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{
    /**
     * Returns product collection in the current category.
     *
     * @return NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection
     */
    public function getProductCollection()
    {
        
      
        /** @var $currentCategory Mage_Catalog_Model_Category */
        $currentCategory = $this->getCurrentCategory();
        /** @var $collection NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection */

        if (isset($this->_productCollections[$currentCategory->getId()])) {
            $ProductCollection = $this->_productCollections[$currentCategory->getId()];
        } else {

            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
              Mage::log('NanoWebG_ElasticSearch_Model_Catalog_Layer getProductCollection elasticsearch model', null, 'elasticsearch_debug.log');
            }

            $ProductCollection = Mage::helper('catalogsearch')
                ->getEngine()
                ->getResultCollection()
                ->setStoreId($currentCategory->getStoreId())
                ->addFqFilter(array('store_id' => $currentCategory->getStoreId()));

            $this->prepareProductCollection($ProductCollection);
            $this->_productCollections[$currentCategory->getId()] = $ProductCollection;
        }
        
              
        return $ProductCollection;
    }
}
