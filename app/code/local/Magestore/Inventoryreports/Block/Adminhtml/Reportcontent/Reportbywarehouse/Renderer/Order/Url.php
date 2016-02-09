<?php
    class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Renderer_Order_Url
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {   
        $link = '<a href='.Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view/order_id/'.$row->getOrderId().'').'>View</a>';
        return $link;
    }
}
?>
