<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareLayout() {
        $this->setChild('continue_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Continue'),
                            'onclick' => 'continueTransfer()',
                            'class' => 'save'
                        ))
        );
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
        $data = '';
        if (Mage::registry('barcode_data')) {
            $data = Mage::registry('barcode_data')->getData();
        }

        $dataObject = new Varien_Object($data);
        $fieldset = $form->addFieldset('barcode_form', array(
            'legend' => Mage::helper('inventorybarcode')->__('Barcode Information')
        ));
        $fieldset->addType('warehouses', 'Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Renderer_Label');
        $fieldset->addType('datetime', 'Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Renderer_Datetime');

        $fields = Mage::helper('inventorybarcode/attribute')->getBarcodeProductFields();
        $barcodeAttributes = Mage::getModel('inventorybarcode/barcodeattribute')->getCollection()
                ->addFieldToFilter('attribute_status', 1)
                ->addFieldToFilter('attribute_display', 1);

        $attributeShow = array();

        foreach ($barcodeAttributes as $barcodeAttribute) {
            $attributeShow[] = $barcodeAttribute->getAttributeCode();
        }
        foreach ($fields as $field) {
            if ($field != 'barcode_status' && $field != 'barcode' && $field != 'qty' && !in_array($field, $attributeShow))
                continue;
            $values = explode('_', $field);
            if ($field != 'barcode_status' && $field != 'barcode' && $field != 'qty') {
                $label = Mage::getModel('inventorybarcode/barcodeattribute')->load($field, 'attribute_code')->getAttributeName();
            } elseif ($field == 'barcode') {
                $label = Mage::helper('inventorybarcode')->__('Barcode');
            } elseif ($field == 'qty') {
                $label = Mage::helper('inventorybarcode')->__('Qty');
            } else {
                $label = Mage::helper('inventorybarcode')->__('Barcode Status');
            }
            if ($field == 'barcode_status') {
                $fieldset->addField($field, 'select', array(
                    'label' => $label,
                    'name' => $field,
                    'disabled' => false,
                    'values' => Mage::getSingleton('catalog/product_status')->getOptionArray()
                ));
            } else {
                $keys = explode('_', $field);
                if ($keys[0] != 'warehouse') {
                    $fieldset->addField($field, 'label', array(
                        'label' => $label,
                        'name' => $field,
                        'bold' => true
                    ));
                } else {
                    $fieldset->addField($field, 'warehouses', array(
                        'label' => $label,
                        'name' => $field,
                        'bold' => true
                    ));
                }
            }
        }
     
        $fieldset->addField('created_date', 'datetime', array(
            'label' => Mage::helper('inventorybarcode')->__('Created Date'),
            'name' => 'created_date',
            'bold' => true
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
