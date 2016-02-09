<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Model_Source_Adminhtml_Dateformat extends Varien_Object {

    static public function getOptionArray() {
        return array(
            'm/d/Y'    => Mage::helper('webpos')->__('m/d/Y ('.date('m/d/Y').')'),
            'd/m/Y'    => Mage::helper('webpos')->__('d/m/Y ('.date('d/m/Y').')'),
            'Y/m/d'    => Mage::helper('webpos')->__('Y/m/d ('.date('Y/m/d').')'),
            'Y/d/m'    => Mage::helper('webpos')->__('Y/d/m ('.date('Y/d/m').')'),
            'M d Y'    => Mage::helper('webpos')->__('M d Y ('.date('M d Y').')'),
            'M D Y'    => Mage::helper('webpos')->__('M D Y ('.date('M D Y').')'),
            'm D Y'    => Mage::helper('webpos')->__('m D Y ('.date('m D Y').')')
        );
    }

    static public function toOptionArray() {
        $options = array(
            array('value' => 'm/d/Y', 'label' => Mage::helper('webpos')->__('m/d/Y ('.date('m/d/Y').')')),
            array('value' => 'd/m/Y', 'label' => Mage::helper('webpos')->__('d/m/Y ('.date('d/m/Y').')')),
            array('value' => 'Y/m/d', 'label' => Mage::helper('webpos')->__('Y/m/d ('.date('Y/m/d').')')),
            array('value' => 'Y/d/m', 'label' => Mage::helper('webpos')->__('Y/d/m ('.date('Y/d/m').')')),
            array('value' => 'M d Y', 'label' => Mage::helper('webpos')->__('M d Y ('.date('M d Y').')')),
            array('value' => 'M D Y', 'label' => Mage::helper('webpos')->__('M D Y ('.date('M D Y').')')),
            array('value' => 'm D Y', 'label' => Mage::helper('webpos')->__('m D Y ('.date('m D Y').')'))
        );
        return $options;
    }
}
