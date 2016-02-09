<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Requeststock extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {        
        $this->_controller = 'adminhtml_requeststock';
        $this->_blockGroup = 'inventorywarehouse';
        $this->_headerText = Mage::helper('inventorywarehouse')->__('Manage Stock Requests');
        parent::__construct();
        // $this->_removeButton('add');
        $this->_updateButton('add', 'label', Mage::helper('inventorywarehouse')->__('Create Stock Request'));
        $this->_updateButton('add', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/inw_requeststock/new') . '\')');
    }

}
