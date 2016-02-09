<?php

class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Renderer_Supplyneeds extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $product_id = $row->getProductId();
        return '
               <p style="text-align:center"><input type="text" name="qty_' . $row->getProductId() . '" id="qty_' . $row->getProductId() . '" size="15" style="text-align:center" onchange="supplyNeedsGrid.logChange(this.name,\'\')"/></p>
               <p style="text-align:center">
               <button type="button" name="maxButton" size="7" onclick="calMax(' . $product_id . ')">Auto Fill</button>
                </p>
                ';
    }

}

?>
