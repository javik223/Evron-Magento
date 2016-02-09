<?php
/**
 * Filters the categories in the layered navigation.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    
    protected $_elasticsearch_prod_collection;
    /**
     * Adds category filter to collection.
     *
     * @param Mage_Catalog_Model_Category $category
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category
     */
    public function addCategoryFilter($category)
    {
        $categories = array(
            'categories' => $category->getId()
        );
        $this->getLayer()->getProductCollection()
            ->addFqFilter($categories);

        return $this;
    }

    /**
     * Adds new facet condition to the filter.
     *
     * @see NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection::newFacetCondition()
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category
     */
    public function newFacetCondition()
    {
        /** @var $category Mage_Catalog_Model_Category */
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category newFacetCondition', null, 'elasticsearch_debug.log');
        }
        $category = $this->getCategory();
        $childrenCategories = $category->getChildrenCategories();

        $isFlat = (bool) Mage::getStoreConfig('catalog/frontend/flat_catalog_category');
        $categories = ($isFlat)
            ? array_keys($childrenCategories)
            : array_keys($childrenCategories->toArray());

        $this->getLayer()->getProductCollection()->newFacetCondition('categories', $categories);
        return $this;
    }

    /**
     * Retrieves and applies the request parameter to product collection.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        
        /** @var $category Mage_Catalog_Model_Category */
        $category = $this->getCategory();
        $filter = (int) $request->getParam($this->getRequestVar());
        if ($filter) {
            $this->_categoryId = $filter;
        }

        


        if (!Mage::registry('current_category_filter')) {
            Mage::register('current_category_filter', $category);
        }

        

        if (!$filter) {
            $this->addCategoryFilter($category);

            return $this;
        }

        $this->_appliedCategory = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);
            $this->addCategoryFilter($this->_appliedCategory);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory->getName(), $filter)
            );
        }

        return $this;
    }

    /**
     * Retrieves the data of the current item.
     *
     * @return array
     */
    protected function _getItemsData()
    {
       
       if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category _getItemsData', null, 'elasticsearch_debug.log');
        }

        $layer = $this->getLayer();
        $key = $layer->getStateKey().'_SUBCATEGORIES';
        $result = $layer->getCacheData($key);
        
        // if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
        //     Mage::log('NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Category elasticsearch collection loaded', null, 'elasticsearch_debug.log');
        // }

        if ($result === null) {
            $categories = $this->getCategory()->getChildrenCategories();
            /** @var $productCollection NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Collection */
            $productCollection = $layer->getProductCollection();
            $ids = $productCollection->getSearchResultIds(); 

            $facets = $productCollection->getFacetedData('categories');

            $result = array();
            foreach ($categories as $category) {
                /** @var $category Mage_Catalog_Model_Category */
                $categoryId = $category->getId();
                if (isset($facets[$categoryId])) {
                    $category->setProductCount($facets[$categoryId]);
                } else {
                    $category->setProductCount(0);
                }
                if ($category->getIsActive() && $category->getProductCount()) {
                    $result[] = array(
                        'label' => Mage::helper('core')->escapeHtml($category->getName()),
                        'value' => $categoryId,
                        'count' => $category->getProductCount(),
                    );
                }
            }
            $tags = $layer->getStateTags();
            $layer->getAggregator()->saveCacheData($result, $key, $tags);
        }

        return $result;
    }
}
