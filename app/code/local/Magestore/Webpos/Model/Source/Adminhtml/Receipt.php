<?php

class Magestore_Webpos_Model_Source_Adminhtml_Receipt extends Varien_Object {

    static public function getOptionArray()
    {
        return array(
            'Letter'    => Mage::helper('webpos')->__('Letter'),
            'A4'    => Mage::helper('webpos')->__('A4'),
            'A5'    => Mage::helper('webpos')->__('A5'),
            'A6'    => Mage::helper('webpos')->__('A6'),
            'A7'    => Mage::helper('webpos')->__('A7'),
        );
    }
    static public function toOptionArray() {
        $options = array(
            array('value' => 'Letter', 'label' => Mage::helper('webpos')->__('Letter')),
            array('value' => 'A4', 'label' => Mage::helper('webpos')->__('A4')),
            array('value' => 'A5', 'label' => Mage::helper('webpos')->__('A5')),
            array('value' => 'A6', 'label' => Mage::helper('webpos')->__('A6')),
            array('value' => 'A7', 'label' => Mage::helper('webpos')->__('A7')),
        );
        return $options;
    }

}
