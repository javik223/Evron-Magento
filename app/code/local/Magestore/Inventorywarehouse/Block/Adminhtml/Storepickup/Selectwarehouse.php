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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Storepickup_Selectwarehouse extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();

        $dataObj = new Varien_Object(array(
            'store_id' => '',
            'store_name_in_store' => '',
            'status_in_store' => '',
            'description_in_store' => '',
            'address_in_store' => '',
            'city_in_store' => '',
        ));

        if (Mage::getSingleton('adminhtml/session')->getStoreData()) {
            $data = Mage::getSingleton('adminhtml/session')->getStoreData();
            Mage::getSingleton('adminhtml/session')->setStoreData(null);
        } elseif (Mage::registry('store_data')) {
            $data = Mage::registry('store_data')->getData();
        }
        if (isset($data)) {
            $dataObj->addData($data);
        }
        if($dataObj->getStoreId()){
            $warehouse = Mage::getResourceModel('inventoryplus/warehouse_collection')
                                    ->addFieldToFilter('storepickup_id', $dataObj->getStoreId())
                                    ->getFirstItem();
            $dataObj->setData('warehouse_id', $warehouse->getId());
            $dataObj->setData('curr_warehouse_id', $warehouse->getId());
        }

        $this->setForm($form);
        $fieldset = $form->addFieldset('store_form', array('legend' => Mage::helper('storepickup')->__('Warehouse Information')));

        $fieldset->addField('warehouse_id', 'select', array(
            'label' => Mage::helper('storepickup')->__('Linked Warehouse'),
            'name' => 'warehouse_id',
            'values' => $this->getWarehouseOptions(),
            'onchange' => 'imStorePickup.changeWarehouse(this);',
            'note' => $this->__('If you choose a warehouse, this Store information will be imported from the warehouse after saved.'),
        ));
        
        $fieldset->addField('curr_warehouse_id', 'hidden', array(
            'name' => 'curr_warehouse_id',
        ));        

        if (Mage::getSingleton('adminhtml/session')->getStoreData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStoreData());
            Mage::getSingleton('adminhtml/session')->setStoreData(null);
        } elseif (Mage::registry('store_data')) {
            $form->setValues($dataObj->getData());
        }

        parent::_prepareForm();
    }
    
    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml() . $this->_getJs();
        }
        return '';
    }
    
    public function _getJs(){
        return $this->getLayout()->createBlock('inventorywarehouse/adminhtml_storepickup_js')->toHtml();
    }
    
    public function getWarehouseOptions() {
        $options = Mage::helper('inventoryplus')->getWarehouseOptions();
        foreach($options as $index=>$option) {
            if(isset($option['object']) && $option['object']) {
                $warehouse = $option['object'];
                if($warehouse->getStorepickupId()) {
                    $options[$index]['label'] = $warehouse->getWarehouseName() . ' ('. $this->__('linked to the Store ID #%s)', $warehouse->getStorepickupId()); 
                }
            }
        }
        return $options;
    }

}
