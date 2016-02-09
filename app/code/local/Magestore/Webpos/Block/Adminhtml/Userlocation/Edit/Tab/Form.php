<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 9:57 SA
 */

class Magestore_Webpos_Block_Adminhtml_Userlocation_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getUserlocationData()) {
            $data = Mage::getSingleton('adminhtml/session')->getUserlocationData();
            Mage::getSingleton('adminhtml/session')->getUserlocationData(null);
        } elseif (Mage::registry('userlocation_data')) {
            $data = Mage::registry('userlocation_data')->getData();
        }
        $fieldset = $form->addFieldset('userlocation_form', array(
            'legend'=>Mage::helper('webpos')->__('Location information')
        ));

        $fieldset->addField('display_name', 'text', array(
            'label'        => Mage::helper('webpos')->__('Display Name'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'display_name',
        ));

        $fieldset->addField('address', 'textarea', array(
            'label'        => Mage::helper('webpos')->__('Address'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'address',
        ));

        $fieldset->addField('description', 'textarea', array(
            'label'        => Mage::helper('webpos')->__('Description'),
            'name'        => 'description',
        ));



        $form->setValues($data);
        return parent::_prepareForm();
    }
}