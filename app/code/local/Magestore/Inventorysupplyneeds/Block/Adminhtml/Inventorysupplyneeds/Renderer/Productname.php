<?php

class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Renderer_Productname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $product_id = $row->getProductId();
        $product = Mage::getModel('catalog/product')->load($product_id);
        if ($product->getId())
            return $product->getName();
        else
            return $row->getProductName();
    }

}

?>
