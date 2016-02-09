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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Warehouse_Edit_Tab_Salesstaff extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getWarehouseData()) {
            $data = Mage::getSingleton('adminhtml/session')->getWarehouseData();
            Mage::getSingleton('adminhtml/session')->setWarehouseData(null);
        } elseif (Mage::registry('warehouse_data')) {
            $data = Mage::registry('warehouse_data')->getData();
        }

        $fieldset = $form->addFieldset('warehouse_form', array(
            'legend' => Mage::helper('inventoryplus')->__('Sales Staffs')
        ));

        $users = Mage::getModel('admin/user')->getCollection();
        $listUser = array();
        foreach ($users as $user) {
            $listUser[] = array('value' => $user->getUserId(), 'label' => $user->getEmail());
        }

        $fieldset->addField('usersale', 'multiselect', array(
            'label' => Mage::helper('inventoryplus')->__('Sales Staffs'),
            'name' => 'usersale',
            'required' => false,
            'style' => 'height:400px;',
            'values' => $listUser
        ));

        $data['usersale'] = unserialize($data['usersale']);
        $form->setValues($data);
        return parent::_prepareForm();
    }

}
