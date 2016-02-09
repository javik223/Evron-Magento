<?php

class Magestore_Inventoryreports_Block_Adminhtml_Supplier_Inventorybysupplier_Renderer_Inventory extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if($row->getData('total_inventory') || $row->getData('total_inventory') != null){
            return parent::render($row);
        }
        return $this->__('N/A');
    }

}

?>
