<?php
    class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Renderer_Productname
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $model = Mage::getModel('catalog/product')->load($row->getProductId());
        $product = Mage::getModel('catalog/product')->loadByAttribute('entity_id', $row->getProductId())->getName();
        return $product;
    }
}
?>
