<?php

class Magestore_Webpos_Model_Source_Adminhtml_Status extends Varien_Object {

    static public function getOptionArray()
    {
        return array(
            '1'    => Mage::helper('webpos')->__('Active'),
            '2'    => Mage::helper('webpos')->__('Inactive'),
        );
    }
    static public function toOptionArray() {
        $options = array(
            array('value' => '1', 'label' => Mage::helper('webpos')->__('Active')),
            array('value' => '2', 'label' => Mage::helper('webpos')->__('Inactive')),
        );
        return $options;
    }

}
