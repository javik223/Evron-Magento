<?php

class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Renderer_Supplier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $product_id = $row->getProductId();
        $supplier_products = $readConnection->fetchAll("SELECT `supplier_id`,`cost` FROM `" . $resource->getTableName('inventorypurchasing/supplier_product') . "` WHERE (product_id = $product_id)");
        $strings = array();
        foreach ($supplier_products as $supplier_product) {
            $supplier_id = $supplier_product['supplier_id'];
            $cost = (float) $supplier_product['cost'];
            $supplier_name = $readConnection->fetchAll("SELECT `supplier_name` FROM `" . $resource->getTableName('inventorypurchasing/supplier') . "` WHERE (supplier_id = $supplier_id)");
            $name = $supplier_name[0]['supplier_name'];
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/inpu_supplier/edit', array('id' => $supplier_id));
            $string = "<a href=" . $url . ">" . $name . "</a> - Cost price: $" . round($cost, 2);
            $strings[] = $string;
        }
        $supplier_string = implode('<br/>', $strings);
        return $supplier_string;
    }

}

?>
