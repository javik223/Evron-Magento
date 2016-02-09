<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 9:58 SA
 */
class Magestore_Webpos_Block_Adminhtml_Userlocation_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('userlocation_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('webpos')->__('Location Information'));
    }


    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('webpos')->__('Location Information'),
            'title'     => Mage::helper('webpos')->__('Location Information'),
            'content'   => $this->getLayout()
                ->createBlock('webpos/adminhtml_userlocation_edit_tab_form')
                ->toHtml(),
        ));
        $this->addTab('user_section', array(
            'label' => Mage::helper('webpos')->__('User List'),
            'title' => Mage::helper('webpos')->__('User List'),
            'url' => $this->getUrl('*/*/user', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
            'class' => 'ajax'
        ));
        return parent::_beforeToHtml();
    }
}