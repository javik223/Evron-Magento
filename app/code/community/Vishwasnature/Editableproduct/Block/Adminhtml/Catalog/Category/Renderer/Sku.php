<?php
class Vishwasnature_Editableproduct_Block_Adminhtml_Catalog_Category_Renderer_Sku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
public function render(Varien_Object $row)
{
$value =  $row->getData($this->getColumn()->getIndex());
return '<span onclick="showElement(this)" prodId="'.$row->getData('entity_id').'" attrinfo="sku" class="editable_value editableCssClass" id="sku_'.$row->getData('entity_id').'">'.$value.'</span>'
        .'<span style="display:none;clear:both;float:left;" class="updating_text">Updating...</span>';
}
}
?>