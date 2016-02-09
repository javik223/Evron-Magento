<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Sendstock_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

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
        if (Mage::getSingleton('adminhtml/session')->getSendStockData()) {
            $data = Mage::getSingleton('adminhtml/session')->getSendStockData();
            Mage::getSingleton('adminhtml/session')->setSendStockData(null);
        } elseif (Mage::registry('sendstock_data')) {
            $data = Mage::registry('sendstock_data')->getData();
        }
        $dataObject = new Varien_Object($data);
        $fieldset = $form->addFieldset('sendstock_form', array(
            'legend' => Mage::helper('inventorywarehouse')->__('Stock Sending Information')
        ));
        $id = $this->getRequest()->getParam('id');
        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        $disabled = false;
        if ($id || $source && $target)
            $disabled = true;
        //add Field to form

        $sourceOptions = Mage::helper('inventorywarehouse')->getWarehouseByAdmin();
        $targetOptions = Mage::helper('inventorywarehouse')->getAllWarehouseSendstockforDestination();

        if (count($sourceOptions) == 0) {
            $fieldset->addField('continue_button', 'note', array(
                'label' => Mage::helper('inventoryplus')->__('Source Warehouse'),
                'class' => 'required-entry',
                'text' => Mage::helper('inventorywarehouse')->__('You have no permission to create stock request'),
            ));
            return parent::_prepareForm();
        }

        if (!$this->getRequest()->getParam('id')) {
            $warehouseId = $this->getRequest()->getParam('source');

            if ($this->getRequest()->getParam('source')) {
                $sourceOption = Mage::helper('inventorywarehouse')->getAllWarehouseSendstockWithId($warehouseId);
                $fieldset->addField('warehouse_id_from', 'select', array(
                    'label' => Mage::helper('inventoryplus')->__('Source'),
                    'class' => 'required-entry',
                    'name' => 'warehouse_id_from',
                    'disabled' => $disabled,
                    'values' => $sourceOption,
                    'after_element_html' => '<script type="text/javascript">
                        $("warehouse_id_from").value="' . $warehouseId . '";
                    </script>',
                ));
            } else {
                $fieldset->addField('warehouse_id_from', 'select', array(
                    'label' => Mage::helper('inventoryplus')->__('Source'),
                    'class' => 'required-entry',
                    'name' => 'warehouse_id_from',
                    'disabled' => $disabled,
                    'values' => $sourceOptions,
                ));
            }

            if (!$source && !$target) {
                if (!isset($data['warehouse_id_from']) || !$data['warehouse_id_from']) {
                    $data['warehouse_id_from'] = $this->getRequest()->getParam('warehouse_id');
                }
                $fieldset->addField('warehouse_id_to', 'select', array(
                    'label' => Mage::helper('inventorywarehouse')->__('Destination'),
                    'class' => 'required-entry',
                    'name' => 'warehouse_id_to',
                    'disabled' => $disabled,
                    'values' => $targetOptions,
                    'after_element_html' => '<script type="text/javascript">
                function continueTransfer(){
                        if($("warehouse_id_from").value != $("warehouse_id_to").value){
                            var url = "' . $this->getUrl('adminhtml/inw_sendstock/new') . 'source/"+$("warehouse_id_from").value+"/target/"+$("warehouse_id_to").value;
                            window.location.href = url;
                        }else{
                            alert("Please select a different destination to send stock!");
                        }
                    }
                </script>'
                ));

                $fieldset->addField('continue_button', 'note', array(
                    'text' => $this->getChildHtml('continue_button'),
                ));
            }
        }

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
