<?php

class Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $orderId = $row->getOrderId();        
        $orderIncrement = $row->getIncrementId();
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view',array('order_id'=>$orderId));
        $content = '<a href='.$url.'>'.$orderIncrement.'</a>';
        return $content;
    }

}

?>
