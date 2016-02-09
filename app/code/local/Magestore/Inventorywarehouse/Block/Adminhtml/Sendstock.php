<?php
    class Magestore_Inventorywarehouse_Block_Adminhtml_Sendstock extends Mage_Adminhtml_Block_Widget_Grid_Container{
        public function __construct(){
            $this->_controller = 'adminhtml_sendstock';
            $this->_blockGroup = 'inventorywarehouse';
            $this->_headerText = Mage::helper('inventorywarehouse')->__('Manage Stock Sending');
            $this->_addButtonLabel = Mage::helper('inventorywarehouse')->__('Create Stock Sending');
            parent::__construct();          
        }
    }
