<?php
class Magestore_Webpos_Block_System_Config_Form_Field_Survey extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('value', array(
            'label' => Mage::helper('webpos')->__('Label'),
            'style' => 'width:250px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('webpos')->__('Add label');
        parent::__construct();
    }
}