<?php
/**
 * Overrides default catalogsearch layer model to handle custom product collection filtering.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   Copyright (c) 2014 Nanowebgroup (http://www.nanowebgroup.com)
 */
class NanoWebG_ElasticSearch_Model_Catalogsearch_Layer extends Mage_CatalogSearch_Model_Layer
{
    public function getProductCollection()
    {
        $category = $this->getCurrentCategory();
        if (isset($this->_productCollections[$category->getId()])) {
            $productCollection = $this->_productCollections[$category->getId()];

            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('NanoWebG_ElasticSearch_Model_Catalogsearch_Layer elastic search this product collection category ['.$category->getId().']', null, 'elasticsearch_debug.log');
            }
        } else {

            
            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('NanoWebG_ElasticSearch_Model_Catalogsearch_Layer elastic search getProductCollection top', null, 'elasticsearch_debug.log');
            }
            /** @var $productCollection NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection */
            $productCollection = Mage::helper('catalogsearch')
                ->getEngine()
                ->getResultCollection('NanoWebG_ElasticSearch_Model_Catalogsearch_Layer->getProductCollection')
                ->setStoreId($category->getStoreId())
                ->addFqFilter(array('store_id' => $category->getStoreId()));
            $this->prepareProductCollection($productCollection);
            $this->_productCollections[$category->getId()] = $productCollection;

            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('NanoWebG_ElasticSearch_Model_Catalogsearch_Layer elastic search getProductCollection bottom', null, 'elasticsearch_debug.log');
            }
        }
        return $productCollection;
    }
}
