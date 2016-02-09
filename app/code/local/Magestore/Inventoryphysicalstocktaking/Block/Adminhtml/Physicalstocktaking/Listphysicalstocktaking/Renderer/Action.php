<?php
    class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Physicalstocktaking_Listphysicalstocktaking_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $html = '';
        $permission = Mage::helper('inventoryplus')->getPermission($row->getWarehouseId(),'can_physical');
        if($row->getPhysicalStatus() ==  0 && $permission ){
            $html = '<a href="'.$this->getUrl('adminhtml/inph_physicalstocktaking/edit',array('id'=>$row->getId())).'">'.Mage::helper('inventoryphysicalstocktaking')->__('Edit').'</a>';
        } else {
            $html = '<a href="'.$this->getUrl('adminhtml/inph_physicalstocktaking/edit',array('id'=>$row->getId())).'">'.Mage::helper('inventoryphysicalstocktaking')->__('View').'</a>';
        }
        return $html;
    }
}