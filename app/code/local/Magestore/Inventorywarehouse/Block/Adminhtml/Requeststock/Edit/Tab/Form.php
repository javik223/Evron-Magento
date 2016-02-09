<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Requeststock_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

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
        if (Mage::getSingleton('adminhtml/session')->getRequestStockData()) {
            $data = Mage::getSingleton('adminhtml/session')->getRequestStockData();
            Mage::getSingleton('adminhtml/session')->getRequestStockData(null);
        } elseif (Mage::registry('requeststock_data')) {
            $data = Mage::registry('requeststock_data')->getData();
        }
        $dataObject = new Varien_Object($data);
        $fieldset = $form->addFieldset('requeststock_form', array(
            'legend' => Mage::helper('inventorywarehouse')->__('Stock Request Information')
                ));
        $id = $this->getRequest()->getParam('id');
        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        $disabled = false;
        $disabledEdit = false;
        if ($id) {
            if ($data['warehouse_id_from'])
                $source = $data['warehouse_id_from'];
            elseif ($data['warehouse_name_from'] == 'Others') {
                $source = 'others';
                $data['warehouse_id_from'] = 'others';
            }
            $target = $data['warehouse_id_to'];
            $disabledEdit = true;
        }
        if ($source && $target)
            $disabled = true;
        //add Field to form
        if($this->getRequest()->getParam('id')){
            $allWarehouses = array();
            $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection()->addFieldToFilter('status',1);
            foreach($warehouses as $warehouse)
                $allWarehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
            $sourceOptions = $targetOptions = $allWarehouses  ;          
        }else{
            $sourceOptions = Mage::helper('inventorywarehouse')->getWarehouseByAdmin();
            $targetOptions = Mage::helper('inventorywarehouse')->getAllWarehouseRequeststock();
        }
        if (count($sourceOptions) == 0) {
            $fieldset->addField('continue_button', 'note', array(
                'label' => Mage::helper('inventoryplus')->__('Destination'),
                'class' => 'required-entry',
                'text' => Mage::helper('inventorywarehouse')->__('You have no permission to create stock sending'),
            ));
            return parent::_prepareForm();
        }
        if ($source && $target) {
            $fieldset->addField('warehouse_id_from', 'select', array(
                'label' => Mage::helper('inventorywarehouse')->__('Source'),
                'class' => 'required-entry',
                'name' => 'warehouse_id_from',
                'disabled' => $disabled,
                'values' => $targetOptions
            ));
            $fieldset->addField('warehouse_id_to', 'select', array(
                'label' => Mage::helper('inventorywarehouse')->__('Destination'),
                'class' => 'required-entry',
                'name' => 'warehouse_id_to',
                'disabled' => $disabled,
                'values' => $sourceOptions,
                'after_element_html' => '
                        <input type="hidden" name="warehouse_source" value="' . $source . '" />
                        <input type="hidden" name="warehouse_target" value="' . $target . '" />
                        <script type="text/javascript">
                            if("' . $source . '" =="others")
                                $("warehouse_id_from").value="others";
                            else
                                $("warehouse_id_from").value=' . $source . ';
                            $("warehouse_id_to").value=' . $target . ';
                            function continueTransfer(){
                                if($("warehouse_id_from").value != $("warehouse_id_to").value){
                                    var url = "' . $this->getUrl('adminhtml/inw_requeststock/new') . 'source/"+$("warehouse_id_from").value+"/target/"+$("warehouse_id_to").value;
                                    window.location.href = url;
                                }else{
                                    alert("Please select a different source to request stock!");
                                }
                            }
                        </script>'
            ));
            $fieldset->addField('reason', 'editor', array(
                'name' => 'reason',
                'label' => Mage::helper('inventorywarehouse')->__('Reason(s) for requesting stock'),
                'title' => Mage::helper('inventorywarehouse')->__('Reason(s) for requesting stock'),
                'style' => 'width:274px; height:200px;',
                'class' => 'required-entry',
                'required' => true,
                'disabled' => $disabledEdit,
                'wysiwyg' => false,
            ));
        } else {
            if(!isset($data['warehouse_id_to']) || !$data['warehouse_id_to']){
                $data['warehouse_id_to'] = $this->getRequest()->getParam('warehouse_id');
            }
            $fieldset->addField('warehouse_id_from', 'select', array(
                'label' => Mage::helper('inventorywarehouse')->__('Source'),
                'class' => 'required-entry',
                'name' => 'warehouse_id_from',
                'disabled' => $disabled,
                'values' => $targetOptions
            ));
            $warehouseId = $this->getRequest()->getParam('warehouse_id');
            $fieldset->addField('warehouse_id_to', 'select', array(
                'label' => Mage::helper('inventorywarehouse')->__('Destination'),
                'class' => 'required-entry',
                'name' => 'warehouse_id_to',
                'disabled' => $disabled,
                'values' => $sourceOptions,
                'after_element_html' => '
                        <input type="hidden" name="warehouse_source" value="' . $source . '" />
                        <input type="hidden" name="warehouse_target" value="' . $target . '" />
                        <script type="text/javascript">
                            if("' . $warehouseId . '"){
                                $("warehouse_id_to").value="' . $warehouseId . '";
                            }
                            function continueTransfer(){
                                if($("warehouse_id_from").value != $("warehouse_id_to").value){
                                    var url = "' . $this->getUrl('adminhtml/inw_requeststock/new') . 'source/"+$("warehouse_id_from").value+"/target/"+$("warehouse_id_to").value;
                                    window.location.href = url;
                                }else{
                                    alert("Please select a different source to request stock!");
                                }
                            }
                        </script>'
            ));

            $fieldset->addField('continue_button', 'note', array(
                'text' => $this->getChildHtml('continue_button'),
            ));
        }
        $form->setValues($data);
        return parent::_prepareForm();
    }

}

