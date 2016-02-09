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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Block_Adminhtml_Webpos_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     *
     * @return Magestore_Webpos_Block_Adminhtml_Webpos_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        if (Mage::getSingleton('adminhtml/session')->getWebposData()) {
            $data = Mage::getSingleton('adminhtml/session')->getWebposData();
            Mage::getSingleton('adminhtml/session')->setWebposData(null);
        } elseif (Mage::registry('webpos_data')) {
            $data = Mage::registry('webpos_data')->getData();
        }
        $fieldset = $form->addFieldset('webpos_form', array(
            'legend'=>Mage::helper('webpos')->__('Item information')
        ));

        $fieldset->addField('title', 'text', array(
            'label'        => Mage::helper('webpos')->__('Title'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'title',
        ));

        $fieldset->addField('filename', 'file', array(
            'label'        => Mage::helper('webpos')->__('File'),
            'required'    => false,
            'name'        => 'filename',
        ));

        $fieldset->addField('status', 'select', array(
            'label'        => Mage::helper('webpos')->__('Status'),
            'name'        => 'status',
            'values'    => Mage::getSingleton('webpos/status')->getOptionHash(),
        ));

        $fieldset->addField('content', 'editor', array(
            'name'        => 'content',
            'label'        => Mage::helper('webpos')->__('Content'),
            'title'        => Mage::helper('webpos')->__('Content'),
            'style'        => 'width:700px; height:500px;',
            'wysiwyg'    => false,
            'required'    => true,
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }
}