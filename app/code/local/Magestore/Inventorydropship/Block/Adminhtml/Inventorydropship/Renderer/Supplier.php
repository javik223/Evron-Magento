<?php

class Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Renderer_Supplier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $supplierId = $row->getSupplierId();
        $supplierName = $row->getSupplierName();
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/inpu_supplier/edit',array('id'=>$supplierId));
        $content = '<a href='.$url.'>'.$supplierName.'</a>';
        return $content;
    }

}

?>
