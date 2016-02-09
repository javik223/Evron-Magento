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
 * Inventorybarcode Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Block_Adminhtml_Barcodeattribute_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventorybarcode';
        $this->_controller = 'adminhtml_barcodeattribute';
        
        $this->_updateButton('save', 'label', Mage::helper('inventorybarcode')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('inventorybarcode')->__('Delete Item'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventorybarcode_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventorybarcode_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventorybarcode_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function changeAttributeType(){
                var type = $('attribute_type').value;
                var id = '".$this->getRequest()->getParam('id')."';
                var parrentNode = $('attribute_code').parentElement;
                var parameters = {attribute_type: type,id:id};	
                var url = '".Mage::helper('adminhtml')->getUrl('adminhtml/inb_barcodeattribute/changeattributetype')."';
                var request = new Ajax.Request(url, {
                    method: 'post',
                    parameters: parameters,
                    onFailure: '',
                    onSuccess: function(transport) {
                        if(transport.status == 200){
                           var result = transport.responseText.evalJSON();
                           parrentNode.innerHTML = result.html;
                        }
                    }
                });	
            }
            Event.observe(window,'load',function(){changeAttributeType();});
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('barcodeattribute_data')
            && Mage::registry('barcodeattribute_data')->getId()
        ) {
            return Mage::helper('inventorybarcode')->__("Edit Barcode Attribute '%s'",
                                                $this->htmlEscape(Mage::registry('barcodeattribute_data')->getAttributeName())
            );
        }
        return Mage::helper('inventorybarcode')->__('Add Barcode Attribute');
    }
}