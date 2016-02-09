<?php
class Vishwasnature_Editableproduct_Block_Adminhtml_Catalog_Category_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
public function render(Varien_Object $row)
{
$value =  $row->getData($this->getColumn()->getIndex());
$productType = $row->getData('type_id');
if($productType == 'grouped'){
  $onclick = '';
  $class = 'editable_value';
}else{
     $onclick = 'showElement(this)';
     $class = 'editable_value editableCssClass';
}
return '<span onclick="'.$onclick.'" prodId="'.$row->getData('entity_id').'" attrinfo="price" class="'.$class.'" id="price_'.$row->getData('entity_id').'">'.$value.'</span>'
        .'<span style="display:none;clear:both;float:left;" class="updating_text">Updating...</span>';
}
}
?>