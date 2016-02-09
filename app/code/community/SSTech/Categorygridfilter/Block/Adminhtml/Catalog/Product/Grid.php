<?php

require_once 'Mage/Adminhtml/Block/Catalog/Product/Grid.php';
class SSTech_Categorygridfilter_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{      
    protected function _prepareColumns()
    {
        $helper = Mage::helper('categorygridfilter');
        if($helper->isCategoryEnabled() == 1){
            $this->addColumnAfter('categorygridfilter_category_list', array(
                    'header'	=> Mage::helper('catalog')->__('Category'),
                    'index'		=> 'categorygridfilter_category_list',
                    'sortable'	=> false,
                    'width' => '250px',
                    'type'  => 'options',
                    'options'	=> Mage::getSingleton('categorygridfilter/system_config_source_category')->toOptionArray(),
                    'renderer'	=> 'SSTech_Categorygridfilter_Block_Adminhtml_Catalog_Product_Grid_Render_Category',
                    'filter_condition_callback' => array($this, 'filterCallback'),
            ),"name");
        }
        
        if($helper->isThumbnailEnabled()==1){
        $this->addColumnAfter('thumbnail', array(
            'header' => Mage::helper('catalog')->__('Thumbnail'),
            'align' => 'left',
            'index' => 'thumbnail',
            'renderer' => 'SSTech_Categorygridfilter_Block_Adminhtml_Catalog_Product_Grid_Render_Thumbnail',
            'width' => '107'
        ),"entity_id");
    }
       return parent::_prepareColumns();
    }
    public function filterCallback($collection, $column)
    {
            $value = $column->getFilter()->getValue();
            $_category = Mage::getModel('catalog/category')->load($value);
            $collection->addCategoryFilter($_category);
            return $collection;
    }
   
}
?>