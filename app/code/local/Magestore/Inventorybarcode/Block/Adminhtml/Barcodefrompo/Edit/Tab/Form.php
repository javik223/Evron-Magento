<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcodefrompo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareLayout() {       
        return parent::_prepareLayout();
        
    }

    /**
     * prepare tab form's information
     *
     * @return Magestore_Inventory_Block_Adminhtml_Stocktransfering_Edit_Tab_Form
     */
    protected function _prepareForm() {        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('barcode_form', array(
            'legend' => Mage::helper('inventorybarcode')->__('Barcode Information')
                ));
        $fieldset->addField('po_id', 'select', array(
                    'label' => Mage::helper('inventorybarcode')->__('Purchase Order'),               
                    'name' => 'po_id',
                    'disabled' => false,
                    'values'    => Mage::getModel('inventorybarcode/purchaseorder')->getOptionArray()
                ));
        
        
        return parent::_prepareForm();
    }

}

