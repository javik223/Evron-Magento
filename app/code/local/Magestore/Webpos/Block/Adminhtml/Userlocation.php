<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 10:20 SA
 */
class Magestore_Webpos_Block_Adminhtml_Userlocation extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_userlocation';
        $this->_blockGroup = 'webpos';
        $this->_headerText = Mage::helper('webpos')->__('Sales Locations');
        $this->_addButtonLabel = Mage::helper('webpos')->__('Add Location');
        parent::__construct();
    }
}