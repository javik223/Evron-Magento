<?php
class Vishwasnature_Editableproduct_Block_Adminhtml_Catalog_Category_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
public function render(Varien_Object $row)
{
$value =  $row->getData($this->getColumn()->getIndex());
$optionArr = Mage::getSingleton('catalog/product_status')->getOptionArray();
return '<span onclick="showElement(this)" prodId="'.$row->getData('entity_id').'" attrinfo="status" class="editable_value editableCssClass" id="status_'.$row->getData('entity_id').'">'.$optionArr[$value].'</span>'
        .'<span style="display:none;clear:both;float:left;" class="updating_text">Updating...</span>';
}
}
?>