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
class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventorybarcode';
        $this->_controller = 'adminhtml_barcode';

//        $this->_updateButton('save', 'label', Mage::helper('inventorybarcode')->__('Save Item'));

        $this->_removeButton('delete');



        /* Add by Kai - print barcode */
        if ($this->getRequest()->getParam('print') == 'true') {
            $this->_removeButton('save');
            $this->_removeButton('reset');
            $this->_addButton('printbarcode1', array(
                'label' => Mage::helper('adminhtml')->__('PRINT'),
                'onclick' => 'printbarcode1()',
                'class' => 'printbarcode1',
                    ), -100);
        } else {
            /* End add by Kai - print barcode */
            if ($this->getRequest()->getParam('id')) {
                $this->_removeButton('save');
                $this->_removeButton('reset');
                $this->_addButton('print_barcode', array(
                    'label' => Mage::helper('inventorybarcode')->__('Print Barcodes'),
                    'onclick' => 'createBarcode()',
                    'class' => 'add',
                        ), 0);
                if (Mage::registry('barcode_data')->getBarcodeStatus() == 1) {
                    $this->_addButton('disable_barcode', array(
                        'label' => Mage::helper('inventorybarcode')->__('Disable'),
                        'onclick' => 'disableBarcode()',
                        'class' => 'disable',
                            ), 0);
                } else {
                    $this->_addButton('disable_barcode', array(
                        'label' => Mage::helper('inventorybarcode')->__('Enable'),
                        'onclick' => 'enableBarcode()',
                        'class' => 'disable',
                            ), 0);
                }
            } else {
                $this->_updateButton('save', 'label', Mage::helper('inventorybarcode')->__('Save Barcode'));
            }
        }

        $classBarcode = Mage::helper('inventorybarcode')->getValidateBarcode();
        $this->_formScripts[] = "
            function createBarcode(){
                var url = '" . $this->getUrl('adminhtml/inb_printbarcode/selecttemplate', array('barcode' => $this->getRequest()->getParam('id'))) . "';
                window.open(url,'_blank', 'scrollbars=yes, resizable=yes, width=750, height=500, left=80, menubar=yes');                
            }
            
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventorybarcode_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventorybarcode_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventorybarcode_content');
            }
            
            function printbarcode1()
            {   
                var productInput = $$('input[name=\"purchase_products\"]');
                if(productInput[0].value == ''){
                    alert('" . $this->__('Please choose barcodes before printing.') . "');
                    return false;
                }
                
                $('edit_form').target='_blank';
                editForm.submit('" . $this->getUrl('*/*/massprint') . "');
            }


            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function disableBarcode(){
                editForm.submit($('edit_form').action+'barcode_status/2/');
            }
            
            function enableBarcode(){
                editForm.submit($('edit_form').action+'barcode_status/1/');
            }
            
            function setBarcodeAuto (element, id){
                if(element.checked){
                    $(id).value = '';
                    $(id).readOnly = true;
                    $(id).removeClassName('" . $classBarcode . "')                    
                }else{
                    $(id).readOnly = false;
                    $(id).addClassName('" . $classBarcode . "')                    
                }
            }
            
             function fileSelected() {
                var file = document.getElementById('fileToUpload').files[0];
                if (file) {
                    var fileSize = 0;
                    if (file.size > 1024 * 1024)
                        fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
                    else
                        fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
                    document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
                    document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
                    document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
                }
            }
			
            function uploadFile() {
                if(!$('fileToUpload') || !$('fileToUpload').value){
                    alert('Please choose CSV file to import!');return false;
                }
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                var fd = new FormData();
                fd.append('fileToUpload', document.getElementById('fileToUpload').files[0]);
                fd.append('form_key', document.getElementById('form_key').value);
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', uploadProgress, false);
                xhr.addEventListener('load', uploadComplete, false);
                xhr.addEventListener('error', uploadFailed, false);
                xhr.addEventListener('abort', uploadCanceled, false);
                xhr.open('POST', '" . $this->getUrl('adminhtml/inb_barcode/getImportCsv') . "');
                xhr.send(fd);
           }

           function uploadProgress(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                    //document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
                   // document.getElementById('prog').value = percentComplete;
                }
                else {
                   // document.getElementById('progressNumber').innerHTML = 'unable to compute';
                }
           }
          
           function uploadComplete(evt) {
                var import_data = '" . Mage::getModel('admin/session')->getData('null_barcode_product_import') . "';    
                    
                if(import_data == '1'){
                     alert('No product was added');
                }
                
                $('barcode_tabs_products_section').addClassName('notloaded');
                barcode_tabsJsTabs.showTabContent($('barcode_tabs_products_section'));
                //varienGlobalEvents.attachEventHandler('showTab',function(){ sendstockproductGridJsObject.doFilter(); });
           }

           function uploadFailed(evt) {
                alert('There was an error attempting to upload the file.');
           }

           function uploadCanceled(evt) {
                alert('The upload has been canceled by the user or the browser dropped the connection.');
           }   
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        /* Edit by kai - print barcode */
        if (Mage::registry('barcode_data') && Mage::registry('barcode_data')->getId()) {
            return Mage::helper('inventorybarcode')->__("View Barcode '%s'", $this->htmlEscape(Mage::registry('barcode_data')->getBarcode())
            );
        }
        if ($this->getRequest()->getParam('print')) {
            return Mage::helper('inventorybarcode')->__('Mass Print Barcode');
        };
        return Mage::helper('inventorybarcode')->__('Create Barcode');
    }

    /* End edit by kai - print barcode */
}
