<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Adminhtml_Till extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_till';
        $this->_blockGroup = 'webpos';
        $this->_headerText = Mage::helper('webpos')->__('POS Cash Drawer');
        $this->_addButtonLabel = Mage::helper('webpos')->__('Add Cash Drawer');
        parent::__construct();
    }
}