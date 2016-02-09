<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Block_Adminhtml_Barcodeattribute_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Inventorybarcode_Edit_Tab_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getInventorybarcodeData()) {
            $data = Mage::getSingleton('adminhtml/session')->getInventorybarcodeData();
            Mage::getSingleton('adminhtml/session')->setInventorybarcodeData(null);
        } elseif (Mage::registry('barcodeattribute_data')) {
            $data = Mage::registry('barcodeattribute_data')->getData();
        }
        $fieldset = $form->addFieldset('barcodeattribute_form', array(
            'legend' => Mage::helper('inventorybarcode')->__('Item information')
        ));

        $fieldset->addField('attribute_name', 'text', array(
            'label' => Mage::helper('inventorybarcode')->__('Attribute Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'attribute_name',
        ));
        
        $fieldset->addField('attribute_type', 'select', array(
            'label' => Mage::helper('inventorybarcode')->__('Attribute Type'),
            'name' => 'attribute_type',
            'class' => 'validate-select',
            'required' => true,
            'onchange'  => 'changeAttributeType();',
            'values' => Mage::getSingleton('inventorybarcode/attributetype')->getOptionHash(),
        ));

        $fieldset->addField('attribute_code', 'text', array(
            'label' => Mage::helper('inventorybarcode')->__('Attribute Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'attribute_code',
        ));

        $afterElementHtml = '<p class="note"><span>'. Mage::helper('inventorybarcode')->__('If yes, the attribute and its value will be shown in a new column on the "Manage Barcodes" grid.').'</span></p>';

        $fieldset->addField('attribute_display', 'select', array(
            'label' => Mage::helper('inventorybarcode')->__('Show On Barcode List'),
            'name' => 'attribute_display',
            'values' => Mage::getSingleton('inventorybarcode/show')->getOptionHash(),
            'after_element_html' => $afterElementHtml
        ));

//        $fieldset->addField('attribute_unique', 'select', array(
//            'label' => Mage::helper('inventorybarcode')->__('Attribute Unique'),
//            'name' => 'attribute_unique',
//            'values' => Mage::getSingleton('inventorybarcode/show')->getOptionHash(),
//        ));
//
//        $fieldset->addField('attribute_require', 'select', array(
//            'label' => Mage::helper('inventorybarcode')->__('Attribute Require'),
//            'name' => 'attribute_require',
//            'values' => Mage::getSingleton('inventorybarcode/show')->getOptionHash(),
//        ));

        $fieldset->addField('attribute_status', 'select', array(
            'label' => Mage::helper('inventorybarcode')->__('Status'),
            'name' => 'attribute_status',
            'values' => Mage::getSingleton('inventorybarcode/status')->getOptionHash(),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
