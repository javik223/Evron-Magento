<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 9:58 SA
 */
class Magestore_Webpos_Block_Adminhtml_Role_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('role_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('webpos')->__('Role Information'));
    }

    /**
     * prepare before render block to html
     *
     * @return Magestore_Webpos_Block_Adminhtml_Webpos_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('webpos')->__('Role Information'),
            'title'     => Mage::helper('webpos')->__('Role Information'),
            'content'   => $this->getLayout()
                ->createBlock('webpos/adminhtml_role_edit_tab_form')
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