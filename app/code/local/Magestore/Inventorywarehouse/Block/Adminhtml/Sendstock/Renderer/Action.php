<?php
    class Magestore_Inventorywarehouse_Block_Adminhtml_Sendstock_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $sendstockId = $row->getId();
        if(Mage::helper('inventorywarehouse/sendstock')->checkCancelSendstock($sendstockId)){
            $html = '<a href="'.$this->getUrl('adminhtml/inw_sendstock/edit',array('id'=>$sendstockId)).'">'.Mage::helper('inventorywarehouse')->__('Edit').'</a>';
        }else{
            $html = '<a href="'.$this->getUrl('adminhtml/inw_sendstock/edit',array('id'=>$sendstockId)).'">'.Mage::helper('inventorywarehouse')->__('View').'</a>';
        }
        return $html;
    }
}
?>
