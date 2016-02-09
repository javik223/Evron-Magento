<?php

 class SSTech_Categorygridfilter_Block_Adminhtml_Catalog_Product_Grid_Render_Thumbnail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $width = Mage::helper('categorygridfilter')->getThumbnailWidth();
        $product = Mage::getModel('catalog/product')->load($row->getEntityId());        
        if($product->getId())
            $image_url = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
        $out = "<img src=". $image_url ." width='". $width ."px' title='". $product->getName() ."'/>"; 
        return $out;
    }

}
