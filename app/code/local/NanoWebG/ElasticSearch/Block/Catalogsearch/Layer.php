<?php
/**
 * Defines custom filter blocks by overriding the default layer view process.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   
 */
class NanoWebG_ElasticSearch_Block_Catalogsearch_Layer extends Mage_CatalogSearch_Block_Layer
{
    /**
     * Boolean filter block name.
     *
     * @var string
     */
    protected $_booleanBlockName;

    protected $_catalogSearchFilters;


    /**
     * Replaces default block names if engine is active.
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();

        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Block_Catalogsearch_Layer _initBlocks', null, 'elasticsearch_debug.log');
        }
           
        if (Mage::helper('nanowebg_elasticsearch')->useElasticSearch()) {
            
            $this->_categoryBlockName = 'nanowebg_elasticsearch/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'nanowebg_elasticsearch/catalogsearch_layer_filter_attribute';
            $this->_priceFilterBlockName = 'nanowebg_elasticsearch/catalog_layer_filter_price';
            $this->_decimalFilterBlockName = 'nanowebg_elasticsearch/catalog_layer_filter_decimal';
            $this->_booleanBlockName   = 'nanowebg_elasticsearch/catalog_layer_filter_boolean';

        }
    }

    /**
     * Creates layout if Elastic Search engine.
     * Difference between parent method is newFacetCondition() call on each created block.
     *
     * @return NanoWebG_ElasticSearch_Block_Catalogsearch_Layer
     */
    protected function _prepareLayout()
    {
        /** @var $helper NanoWebG_ElasticSearch_Helper_Data */ 
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Block_Catalogsearch_Layer _prepareLayout', null, 'elasticsearch_debug.log');
        }

        $helper = Mage::helper('nanowebg_elasticsearch');
        if (!$helper->useElasticSearch()) {
            if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                Mage::log('NanoWebG_ElasticSearch_Block_Catalogsearch_Layer _prepareLayout parent (do not use elastic)', null, 'elasticsearch_debug.log');
            }
            parent::_prepareLayout();
        } else {

           
            $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
                ->setLayer($this->getLayer());

            $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
                ->setLayer($this->getLayer())
                ->init();

            
            $this->setChild('layer_state', $stateBlock);
            $this->setChild('category_filter', $categoryBlock->newFacetCondition());

            $filterables = $this->_getFilterableAttributes();
            
           
            $filters = array();
            foreach ($filterables as $filter) {

                if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
                    Mage::log('NanoWebG_ElasticSearch_Block_Catalogsearch_Layer _prepareLayout next attribute:'.$filter->getAttributeCode(), null, 'elasticsearch_debug.log');
                }

                if ($filter->getAttributeCode() == 'price') {
                    $filterBlockName = $this->_priceFilterBlockName;
                } elseif ($filter->getSourceModel() == 'eav/entity_attribute_source_boolean') {
                    $filterBlockName = $this->_booleanBlockName;
                } elseif ($filter->getBackendType() == 'decimal') {
                    $filterBlockName = $this->_decimalFilterBlockName;
                } else {
                    $filterBlockName = $this->_attributeFilterBlockName;
                }

                $filters[$filter->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($filter)
                    ->init();
            }

            $this->_catalogSearchFilters = $filters;

            foreach ($filters as $filterName => $block) {
                

                $this->setChild($filterName, $block->newFacetCondition());
            }

            $this->getLayer()->apply();
        }

        return $this;
    }

    /**
     * Checks if layered block can be displayed.
     *
     * @return bool
     */
    public function ShowBlock()
    {
        return ($this->canShowOptions() || count($this->getLayer()->getState()->getFilters()));
    }

    /**
     * Returns the current catalog layer.
     *
     * @return NanoWebG_ElasticSearch_Model_Catalogsearch_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        /** @var $helper NanoWebG_ElasticSearch_Helper_Data */
        $helper = Mage::helper('nanowebg_elasticsearch');
        
         if ($helper->useElasticSearch()) {
            return Mage::getSingleton('nanowebg_elasticsearch/catalogsearch_layer');
        }

        return parent::getLayer();
    }

    public function getCatalogSearchLayers()
    {
        return $this->_catalogSearchFilters;
    }


}
