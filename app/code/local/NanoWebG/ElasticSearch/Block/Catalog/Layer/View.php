<?php
/**
 * Define custom filter blocks by overriding the default layer view process.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   
 */
class NanoWebG_ElasticSearch_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    /**
     * Boolean Filter block name.
     *
     * @var string
     */
    protected $_booleanBlockName;

    /**
     * Registers current layer in the Mage registry.
     *
     * @see Mage_Catalog_Block_Product_List::getLayer()
     */
    protected function _construct()
    {
        
        parent::_construct();
        Mage::register('current_layer', $this->getLayer());
    }

    /**
     * Replaces default block names if engine is active.
     */
    protected function _initBlocks()
    {
        if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
            Mage::log('NanoWebG_ElasticSearch_Block_Catalog_Layer_View _initBlocks', null, 'elasticsearch_debug.log');
        }
        parent::_initBlocks();

        if (Mage::helper('nanowebg_elasticsearch')->useElasticSearch()){
            $this->_categoryBlockName        = 'nanowebg_elasticsearch/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'nanowebg_elasticsearch/catalog_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'nanowebg_elasticsearch/catalog_layer_filter_price';
            $this->_decimalFilterBlockName   = 'nanowebg_elasticsearch/catalog_layer_filter_decimal';
            $this->_booleanBlockName   = 'nanowebg_elasticsearch/catalog_layer_filter_boolean';
        }
    }

    /**
     * Creates the layout if engine is active.
     * Difference between parent method is newFacetCondition() call on each created block.
     *
     * @return NanoWebG_ElasticSearch_Block_Catalog_Layer_View
     */
    protected function _prepareLayout()
    {
        // if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
        //     Mage::log('NanoWebG_ElasticSearch_Block_Catalog_Layer_View _prepareLayout', null, 'elasticsearch_debug.log');
        // }

        $helper = Mage::helper('nanowebg_elasticsearch');

        if (!$helper->useElasticSearch()) {
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
                if ($filter->getAttributeCode() == 'price') {
                    $filterBlockName = $this->_priceFilterBlockName;
                } elseif ($filter->getBackendType() == 'decimal') {
                    $filterBlockName = $this->_decimalFilterBlockName;
                } elseif ($filter->getSourceModel() == 'eav/entity_attribute_source_boolean') {
                    $filterBlockName = $this->_booleanBlockName;
                } else {
                    $filterBlockName = $this->_attributeFilterBlockName;
                }

                $filters[$filter->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($filter)
                    ->init();
            }

            foreach ($filters as $filterName => $block) {
                $this->setChild($filterName, $block->newFacetCondition());
            }

            $this->getLayer()->apply();
        }

        return $this;
    }

    /**
     * Returns the current catalog layer.
     *
     * @return NanoWebG_ElasticSearch_Model_Catalog_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        /** @var $helper NanoWebG_ElasticSearch_Helper_Data */
         
          $helper = Mage::helper('nanowebg_elasticsearch');
        if(!$helper->useElasticSearch()){
           return parent::getLayer();
        }else{
            return Mage::getSingleton('nanowebg_elasticsearch/catalog_layer');
        }       
    }
}
