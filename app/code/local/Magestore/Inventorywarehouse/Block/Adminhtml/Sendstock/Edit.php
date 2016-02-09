<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Sendstock_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventorywarehouse';
        $this->_controller = 'adminhtml_sendstock';

        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            if ($source && $target) {
                $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl("adminhtml/inw_sendstock/new") . '\')');
                $this->removeButton('save');
                $this->_addButton('checkandsave', array(
                    'name' => 'checkandsave',
                    'label' => Mage::helper('adminhtml')->__('Save'),
                    'onclick' => 'checkandsubmit()',
                    'class' => 'save',
                ));
                $this->removeButton('delete');

                $this->_addButton('saveandcontinue', array(
                    'name' => 'saveandcontinue',
                    'label' => Mage::helper('adminhtml')->__('Save And View'),
                    'onclick' => 'saveAndContinueEditCheck()',
                    'class' => 'save',
                        ), -100);
            } else {                
                $this->removeButton('save');
                $this->removeButton('delete');
                $this->removeButton('reset');
            }
        } else {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl("adminhtml/inw_sendstock/index") . '\')');
            $this->removeButton('save');
            $this->removeButton('delete');
            $this->removeButton('reset');
            $sendStock = Mage::getModel('inventorywarehouse/sendstock')->load($id);
            $warehouseId = $sendStock->getWarehouseIdTo();
            $admin = Mage::getSingleton('admin/session')->getUser();
            if ($warehouseId && Mage::helper('inventorywarehouse')->canTransfer($admin->getId(), $warehouseId)) {
                if ($sendStock->getStatus() == 1) {
                    $day = Mage::getStoreConfig('inventoryplus/transaction/cancel_time');
                    $created_at = $sendStock->getCreatedAt();
                    $cancelDay = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($created_at)) . " +$day day"));
                    if (strtotime($cancelDay) > strtotime(now())) {
                        $this->_addButton('cancelSending', array(
                            'label' => Mage::helper('adminhtml')->__('Cancel'),
                            'onclick' => 'cancelSending()',
                            'class' => 'delete',
                                ), -110);
                    }
                }
            }
        }
        if(!Mage::helper('inventorywarehouse/sendstock')->getWarehouseByAdmin()){
            $this->removeButton('save');
            $this->removeButton('saveandcontinue');            
            $this->removeButton('cancel');            
        }
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }

            function saveAndContinueEditCheck(){
                if(confirm('".Mage::helper('inventorywarehouse')->__('Are you sure you want to create this stock sending?')."')){
                    var checkProduct = checkProductQty();
                    if((!checkProduct) || (checkProduct=='')){
                        alert('Please select product(s) and enter Qty greater than 0 to create stock sending!');
                        return false;
                    }else{
                        var parameters = {products: checkProduct};
                        var check_product_url = '" . $this->getUrl('adminhtml/inw_sendstock/checkproduct') . "';
                        var request = new Ajax.Request(check_product_url, {	
                            parameters: parameters,
                            onSuccess: function(transport) {
                                if(transport.status == 200)	{                                                                
                                    var response = transport.responseText;  
                                    if(response=='1'){
                                        var validator  = new Validation('edit_form');
                                        if(validator.validate()){
                                            var elements = document.getElementsByName('checkandsave');
                                            var elements_1 = document.getElementsByName('saveandcontinue');
                                            for (var i = 0; i < elements.length; i++) {
                                                var element = elements[i];
                                                element.disabled = true;
                                            }
                                            for (var j = 0; j < elements_1.length; j++) {
                                                var element_1 = elements_1[j];
                                                element_1.disabled = true;
                                            }
                                            if($('loading-mask')){
                                                $('loading-mask').style.display = 'block';
                                                $('loading-mask').style.height = '900px';                    
                                                $('loading-mask').style.width = '1500px';                    
                                                $('loading-mask').style.top = '0';                    
                                                $('loading-mask').style.left = '-2';                    
                                            }
                                            editForm.submit($('edit_form').action+'back/edit/');
                                        }else{
                                            sendstock_tabsJsTabs.showTabContent($('sendstock_tabs_form_section'));
                                        }
                                    }else{
                                        alert('Please select product(s) and enter Qty greater than 0 to create stock sending!');
                                        return false;
                                    }
                                }
                            },
                            onFailure: ''
                        });
                        return false;
                    }
    //                editForm.submit($('edit_form').action+'back/edit/');
                }
            }
            
            function checkProductQty()
            {
                var sendstock_products = document.getElementsByName('sendstock_products');
                if(sendstock_products && sendstock_products != '' && sendstock_products[0]){                
                    return sendstock_products[0].value;
                }else{                    
                    return false;                    
                }
            }

            function checkandsubmit(i){
                if(confirm('".Mage::helper('inventorywarehouse')->__('Are you sure you want to create this stock sending?')."')){
                    var className = $('sendstock_tabs_products_section').className;
                    if(className == 'tab-item-link ajax notloaded'){
                        alert('Please enter the stock quantity to transfer!');
                        sendstock_tabsJsTabs.showTabContent($('sendstock_tabs_products_section'));
                    }else{
                        var checkProduct = checkProductQty();
                        if((!checkProduct) || (checkProduct=='')){
                            alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                            return false;
                        }else{
                        var parameters = {products: checkProduct};
                        var check_product_url = '" . $this->getUrl('adminhtml/inw_sendstock/checkproduct') . "';
                        var request = new Ajax.Request(check_product_url, {	
                            parameters: parameters,
                            onSuccess: function(transport) {
                                if(transport.status == 200)	{                                                                
                                    var response = transport.responseText;  
                                    if(response=='1'){
                                        var formvalidator  = new Validation('edit_form');
                                        if(formvalidator.validate()){
                                            var myelements = document.getElementsByName('checkandsave');
                                            var myelements_1 = document.getElementsByName('saveandcontinue');
                                            for (var n = 0; n < myelements.length; n++) {
                                                var myelement = myelements[n];
                                                myelement.disabled = true;
                                            }
                                            for (var m = 0; m < myelements_1.length; m++) {
                                                var myelement_1 = myelements_1[m];
                                                myelement_1.disabled = true;
                                            }
                                            if($('loading-mask')){
                                                $('loading-mask').style.display = 'block';
                                                $('loading-mask').style.height = '900px';                    
                                                $('loading-mask').style.width = '1500px';                    
                                                $('loading-mask').style.top = '0';                    
                                                $('loading-mask').style.left = '-2';                    
                                            }
                                            editForm.submit();
                                        }else{
                                            sendstock_tabsJsTabs.showTabContent($('sendstock_tabs_form_section'));
                                        }
                                    }else{
                                        alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                                        return false;
                                    }
                                }
                            },
                            onFailure: ''
                        });
                        return false;
                    }
    //                editForm.submit();
                    }
                }
            }
            function cancelSending(){
                if (confirm('Are you sure you want to cancel sending stock? The Qty. sent will be instantly added back to the total Qty. in your source warehouse.')) { 
                    var url = '" . $this->getUrl('adminhtml/inw_sendstock/cancel', array('id' => $this->getRequest()->getParam('id'))) . "';
                    if($('loading-mask')){
                        $('loading-mask').style.display = 'block';
                        $('loading-mask').style.height = '900px';                    
                        $('loading-mask').style.width = '1500px';                    
                        $('loading-mask').style.top = '0';                    
                        $('loading-mask').style.left = '-2';                    
                    }
                    window.location.href = url;
                }  
            }

            function saveAndContinueEdit(){
           
                    var className = $('sendstock_tabs_products_section').className;
                    if(className == 'tab-item-link ajax notloaded'){
                        alert('Please enter the stock quantity to transfer!');
                        stocktransfering_tabsJsTabs.showTabContent($('sendstock_tabs_products_section'));
                    }else{
                        if($('loading-mask')){
                            $('loading-mask').style.display = 'block';
                            $('loading-mask').style.height = '900px';                    
                            $('loading-mask').style.width = '1500px';                    
                            $('loading-mask').style.top = '0';                    
                            $('loading-mask').style.left = '-2';                    
                        }
                        editForm.submit($('edit_form').action+'back/edit/');
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
                var reason = '';
                if($('reason') && $('reason').value)
                    var reason = $('reason').value;
                xhr.open('POST', '" . $this->getUrl('adminhtml/inw_sendstock/getImportCsv', array('source' => $source, 'target' => $target))."reason/'+reason);
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
                var import_data = '" . Mage::getModel('admin/session')->getData('null_sendstock_product_import') . "';           
                if(import_data == '1'){
                    alert('No product was added');
                }
                $('sendstock_tabs_products_section').addClassName('notloaded');
                sendstock_tabsJsTabs.showTabContent($('sendstock_tabs_products_section'));
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
        if (Mage::registry('sendstock_data') && Mage::registry('sendstock_data')->getId()
        ) {
            if (Mage::registry('sendstock_data')->getStatus() == 2) {
                return Mage::helper('inventoryplus')->__("View Stock Sending No.'%s'", $this->htmlEscape(Mage::helper('inventorywarehouse')->getIncrementId(Mage::registry('sendstock_data')))
                );
            } else {
                return Mage::helper('inventoryplus')->__("Edit Stock Sending No.'%s'", $this->htmlEscape(Mage::helper('inventorywarehouse')->getIncrementId(Mage::registry('sendstock_data')))
                );
            }
        }
        return Mage::helper('inventoryplus')->__('New Stock Sending');
    }

}

?>
