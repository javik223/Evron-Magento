<?php
    class Magestore_Inventorywarehouse_Block_Adminhtml_Requeststock_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $requeststockId = $row->getId();
        if(Mage::helper('inventorywarehouse/requeststock')->checkCancelRequeststock($requeststockId))
            $html = '<a href="'.$this->getUrl('adminhtml/inw_requeststock/edit',array('id'=>$requeststockId)).'">'.Mage::helper('inventorywarehouse')->__('Edit').'</a>';
        else
            $html = '<a href="'.$this->getUrl('adminhtml/inw_requeststock/edit',array('id'=>$requeststockId)).'">'.Mage::helper('inventorywarehouse')->__('View').'</a>';
        return $html;
    }
}
?>
