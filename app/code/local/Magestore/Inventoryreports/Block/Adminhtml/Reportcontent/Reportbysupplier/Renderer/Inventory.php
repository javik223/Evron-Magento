<?php

class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbysupplier_Renderer_Inventory extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
    if($row->getData('total_inventory') > 0){
            return parent::render($row);
        }
        return $this->__('0');
    }

}

?>
