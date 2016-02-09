<?php
    class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Renderer_Qtyorder_Refunded
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {   
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $model = Mage::getModel('sales/order_item');
        $model->load($row->getOrderId(), 'order_id');
        return $model->getQtyRefunded();
    }
}
?>
