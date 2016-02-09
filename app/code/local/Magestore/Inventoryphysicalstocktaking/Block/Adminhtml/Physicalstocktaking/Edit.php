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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adjust Stock Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Physicalstocktaking_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryphysicalstocktaking';
        $this->_controller = 'adminhtml_physicalstocktaking';
        $this->_addButton('cancel', array('label' => Mage::helper('inventoryphysicalstocktaking')->__('Cancel'), 'class' => 'cancel', 'onclick' => 'cancelAction()'));
        $this->_addButton('confirm', array('label' => Mage::helper('inventoryphysicalstocktaking')->__('Confirm'), 'style' => 'comfirm', 'onclick' => 'confirmAction()'));

        $this->_addButton('confirm_adjust', array('label' => Mage::helper('inventoryphysicalstocktaking')->__('Confirm and Adjust Stock'), 'style' => 'comfirm', 'onclick' => 'confirmAdjustAction()'));

        $this->_updateButton('reset', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/inph_physicalstocktaking/new') . '\')');
        if ($warehouseBack = $this->getRequest()->getParam('warehouseback_id'))
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/inph_warehouse/edit/id/' . $warehouseBack) . '\')');
        $this->removeButton('delete');
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        
        if ($this->getRequest()->getParam('id')) {
            $admin = Mage::getSingleton('admin/session')->getUser();
            $adminId = $admin->getId();
            $permission = Mage::getSingleton('adminhtml/session')->getData('inventory_permission');
            $this->removeButton('reset');
            $model = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking')->load($this->getRequest()->getParam('id'));
            $warehouseId = $model->getWarehouseId();
            $canPhysical = Mage::helper('inventoryplus')->getPermission($warehouseId, 'can_physical');
            $canAdjust = Mage::helper('inventoryplus')->getPermission($warehouseId, 'can_adjust');
            if ($model->getStatus() == 1 || $model->getStatus() == 2 || !$canPhysical) {
                $this->removeButton('cancel');
                $this->removeButton('saveandcontinue');
                $this->removeButton('save');
            }
            if ($model->getStatus() == 1 || $model->getStatus() == 2 || !$canAdjust) {
                $this->removeButton('confirm');
                $this->removeButton('confirm_adjust');
            }
        } else {
            if ($prepareData = Mage::getModel('admin/session')->getData('physicalstocktaking_product_warehouse')) {
                $warehouseId = $prepareData['warehouse_id'];
            }
            $canPhysical = Mage::helper('inventoryplus')->getPermission($warehouseId, 'can_physical');
            $canAdjust = Mage::helper('inventoryplus')->getPermission($warehouseId, 'can_adjust');
            if (!$canPhysical) {
                $this->removeButton('cancel');
                $this->removeButton('saveandcontinue');
                $this->removeButton('save');
            }
            if (!$canAdjust) {
                $this->removeButton('confirm');
                $this->removeButton('confirm_adjust');
            }
        }



        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }
            
            function cancelAction(){
                var r=confirm('" . Mage::helper('inventoryplus')->__('Are you sure you want to cancel this physical stocktaking?') . "');  
                if (r==true){
                    editForm.submit($('edit_form').action+'cancel/1/');
                }
            }

            function isNumeric(n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            }

            function validateQty(){
                var adjustFields = document.getElementsByName('adjust_qty');
                for(var i = 0;i<adjustFields.length;i++){
                    var el = adjustFields[i];
                    if(!el.disabled && el.tagName == 'INPUT'){
                        if(!el.value || el.value < 0 || !isNumeric(el.value)){
                            var messageDiv = document.getElementById('messages');
                            messageDiv.innerHTML = '';
                            var ulMessage = document.createElement('UL');
                            ulMessage.className = 'messages';
                            var liMessage = document.createElement('LI');
                            liMessage.className = 'error-msg';
                            var textnode = document.createTextNode('" . Mage::helper('inventoryphysicalstocktaking')->__("Physical Qty. does not accept negative values or blank. Please enter a valid value.") . "');
                            liMessage.appendChild(textnode);
                            ulMessage.appendChild(liMessage);
                            messageDiv.appendChild(ulMessage);
                            var body = document.getElementsByTagName('BODY')[0];
                            body.scrollTop = 50;
                            return false;
                        } 
                    } 
                }
                return true;
            }

             function saveAndContinueEdit(){
                var validate = validateQty();
                if(validate){
                    var r=confirm('Are you sure you want to save this physical stocktaking?');
                    if (r==true){
                        editForm.submit($('edit_form').action+'back/edit/');
                    }
                }
            }
            
            function saveAction(){
                var validate = validateQty();
                if(validate){
                    var r=confirm('Are you sure?');
                    if (r==true){
                        editForm.submit();
                    }
                }
            }
            
            function confirmAction(){
                var validate = validateQty();
                if(validate){
                    var r=confirm('" . Mage::helper('inventoryphysicalstocktaking')->__('Are you sure you want to confirm this physical stocktaking? The comfirmation will complete the stocktaking process and create a pending stock adjustment.?') . "');
                    if (r==true){
                      editForm.submit($('edit_form').action+'confirm/physicalstocktaking');
                    }
                }
            }
            
            function confirmAdjustAction(){
                var validate = validateQty();
                if(validate){
                    var r=confirm('" . Mage::helper('inventoryphysicalstocktaking')->__('Are you sure you want to confirm this physical stocktaking and its stock adjustment? The comfirmation will update Qty of products. instantly based on their stocktake Qty. here.') . "');
                    if (r==true){
                      editForm.submit($('edit_form').action+'confirm/physicalstocktaking/confirmadjust/physicalstocktaking');
                    }
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
        var reason = '';
            function uploadFile() {
                if(!$('fileToUpload') || !$('fileToUpload').value){
                    alert('Please choose CSV file to import!');return false;
                }
                reason = $('reason').value;
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
                xhr.open('POST', '" . $this->getUrl('adminhtml/inph_physicalstocktaking/importproduct') . "');
                xhr.send(fd);
            }

            function uploadProgress(evt) {

            }

            function uploadComplete(evt) {
                              
                reason = $('reason').value;
                $('physicalstocktaking_tabs_form_section').addClassName('notloaded');
                physicalstocktaking_tabsJsTabs.showTabContent($('physicalstocktaking_tabs_form_section'));   
               
                setTimeout(function(){
                     $('reason').value = reason;
                },1500);
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
        if (Mage::registry('physicalstocktaking_data') && Mage::registry('physicalstocktaking_data')->getId()
        ) {
            return Mage::helper('inventoryphysicalstocktaking')->__("View Physical Stocktaking No. '%s'", $this->htmlEscape(Mage::registry('physicalstocktaking_data')->getId())
            );
        }
        return Mage::helper('inventoryphysicalstocktaking')->__('New Physical Stocktaking');
    }

}
