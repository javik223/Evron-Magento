<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Salesrate_Renderer_SalesRate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $product_id = $row->getEntityId();
        $sales_rate = Mage::helper('inventorywarehouse/salesrate')->calculateSalesRate($product_id);
        return '<p style="text-align:center"><label name="sales_rate" id="sales_rate_' . $product_id . '">' . $sales_rate . '</label></p>
                ';
    }

}