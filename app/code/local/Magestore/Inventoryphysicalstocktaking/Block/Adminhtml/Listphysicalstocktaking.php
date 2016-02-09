<?php

class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Listphysicalstocktaking extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_physicalstocktaking_listphysicalstocktaking';
        $this->_blockGroup = 'inventoryphysicalstocktaking';
        $this->_headerText = Mage::helper('inventoryphysicalstocktaking')->__('Manage Physical Stocktaking');
        $this->_addButtonLabel = Mage::helper('inventoryphysicalstocktaking')->__('Add New Physical Stocktaking');
        parent::__construct();
        if(!Mage::helper('inventoryphysicalstocktaking')->getPhysicalWarehouseByAdmin()){
            $this->_removeButton('add');
        }
    }
}