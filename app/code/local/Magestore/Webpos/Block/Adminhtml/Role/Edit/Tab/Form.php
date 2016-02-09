<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 9:57 SA
 */

class Magestore_Webpos_Block_Adminhtml_Role_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getRoleData()) {
            $data = Mage::getSingleton('adminhtml/session')->getRoleData();
            Mage::getSingleton('adminhtml/session')->getRoleData(null);
        } elseif (Mage::registry('role_data')) {
            $data = Mage::registry('role_data')->getData();
        }
        $fieldset = $form->addFieldset('role_form', array(
            'legend'=>Mage::helper('webpos')->__('Role Information')
        ));

        $fieldset->addField('display_name', 'text', array(
            'label'        => Mage::helper('webpos')->__('Role Name'),
            'name'        => 'display_name',
            'class' => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('permission_ids', 'multiselect', array(
            'label'        => Mage::helper('webpos')->__('Permissions'),
            'name'        => 'permission_ids',
            'class' => 'required-entry',
            'required' => true,
            'values' => Mage::getSingleton('webpos/source_adminhtml_permission')->getStoreValuesForForm()
        ));

        $fieldset->addField('description', 'textarea', array(
            'label'        => Mage::helper('webpos')->__('Description'),
            'name'        => 'description',
        ));

        $fieldset->addField('active', 'select', array(
            'label'        => Mage::helper('webpos')->__('Status'),
            'name'        => 'active',
            'values' => Mage::getSingleton('webpos/source_adminhtml_status')->toOptionArray()
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }
}