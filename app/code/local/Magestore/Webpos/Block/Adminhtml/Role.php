<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 10:20 SA
 */
class Magestore_Webpos_Block_Adminhtml_Role extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_role';
        $this->_blockGroup = 'webpos';
        $this->_headerText = Mage::helper('webpos')->__('Role Manager');
        $this->_addButtonLabel = Mage::helper('webpos')->__('Add Role');
        parent::__construct();
    }
}