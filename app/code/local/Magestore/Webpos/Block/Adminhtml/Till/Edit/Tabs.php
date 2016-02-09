<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Block_Adminhtml_Till_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('till_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('webpos')->__('Cash Drawer Information'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('webpos')->__('Cash Drawer Information'),
            'title'     => Mage::helper('webpos')->__('Cash Drawer Information'),
            'content'   => $this->getLayout()
                                ->createBlock('webpos/adminhtml_till_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
