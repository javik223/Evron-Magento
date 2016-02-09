<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Requeststock_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventorywarehouse';
        $this->_controller = 'adminhtml_requeststock';

        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        $id = $this->getRequest()->getParam('id');
        if ($source && $target) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl("adminhtml/inw_requeststock/new") . '\')');
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
                'label' => Mage::helper('adminhtml')->__('Save and View'),
                'onclick' => 'saveAndContinueEditCheck()',
                'class' => 'save',
                    ), -100);
        } else {
            $this->removeButton('save');
            $this->removeButton('delete');
            $this->removeButton('reset');
        }
        if ($id) {
            if($this->checkCancelRequeststock($id)) {
            $this->_addButton('cancelRequesting', array(
                'label' => Mage::helper('adminhtml')->__('Cancel'),
                'onclick' => 'cancelRequesting()',
                'class' => 'delete',
                    ), -110);
            }
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }

            function saveAndContinueEditCheck(){
                var r=confirm('Are you sure you want to create this stock requesting?');
                if (r==true){
                }else{
                    return false;
                }
                var checkProduct = checkProductQty();
                if((!checkProduct) || (checkProduct=='')){
                    alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                    requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                    return false;
                }else{
                    var parameters = {products: checkProduct};
                    var check_product_url = '" . $this->getUrl('adminhtml/inw_requeststock/checkproduct') . "';
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
                                        editForm.submit($('edit_form').action+'back/edit/');
                                    }else{
                                        requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_form_section'));
                                    }
                                }else{
                                    alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                                    requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                                    return false;
                                }
                            }
                        },
                        onFailure: ''
                    });
                    return false;
                }    
//                if($('loading-mask')){
//                    $('loading-mask').style.display = 'block';
//                    $('loading-mask').style.height = '900px';                    
//                    $('loading-mask').style.width = '1500px';                    
//                    $('loading-mask').style.top = '0';                    
//                    $('loading-mask').style.left = '-2';                    
//                }
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function checkProductQty()
            {
                var requeststock_products = document.getElementsByName('requeststock_products');
                if(requeststock_products && requeststock_products != '' && requeststock_products[0]){                
                    return requeststock_products[0].value;
                }else{                    
                    return false;                    
                }
            }

            function checkandsubmit(i){
                var r=confirm('Are you sure you want to create this stock requesting?');
                if (r==true){
                }else{
                    return false;
                }
                var className = $('requeststock_tabs_products_section').className;
                if(className == 'tab-item-link ajax notloaded'){
                    alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                    requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                }else{
                    var checkProduct = checkProductQty();
                    if((!checkProduct) || (checkProduct=='')){
                        alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                        requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                        return false;
                    }else{
                        var parameters = {products: checkProduct};
                        var check_product_url = '" . $this->getUrl('adminhtml/inw_requeststock/checkproduct') . "';
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
//                                            if($('loading-mask')){
//                                                $('loading-mask').style.display = 'block';
//                                                $('loading-mask').style.height = '900px';                    
//                                                $('loading-mask').style.width = '1500px';                    
//                                                $('loading-mask').style.top = '0';                    
//                                                $('loading-mask').style.left = '-2';                    
//                                            }
                                            editForm.submit();
                                        }else{
                                            requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_form_section'));
                                        }
                                    }else{
                                        alert('Please select product(s) and enter Qty greater than 0 to transfer stock!');
                                        requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                                        return false;
                                    }
                                }
                            },
                            onFailure: ''
                        });
                        return false;
                    }
//                    if($('loading-mask')){
//                        $('loading-mask').style.display = 'block';
//                        $('loading-mask').style.height = '900px';                    
//                        $('loading-mask').style.width = '1500px';                    
//                        $('loading-mask').style.top = '0';                    
//                        $('loading-mask').style.left = '-2';                    
//                    }
                    editForm.submit();                    
                }
            }
            function cancelRequesting(){
                if (confirm('Are you sure you want to cancel requesting stock? The Qty. requested will be instantly subtracted from the total Qty. in your destination warehouse.')) {
                    var url = '" . $this->getUrl('adminhtml/inw_requeststock/cancel', array('id' => $this->getRequest()->getParam('id'))) . "';
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
                var className = $('requeststock_tabs_products_section').className;
                if(className == 'tab-item-link ajax notloaded'){
                    alert('Please enter the stock quantity to transfer!');
                    stocktransfering_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                }else{
                    var r=confirm('Are you sure you want to create this stock requesting?');                    
                    if (r==true){
//                        if($('loading-mask')){
//                            $('loading-mask').style.display = 'block';
//                            $('loading-mask').style.height = '900px';                    
//                            $('loading-mask').style.width = '1500px';                    
//                            $('loading-mask').style.top = '0';                    
//                            $('loading-mask').style.left = '-2';                    
//                        }
                        editForm.submit($('edit_form').action+'back/edit/');
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
                var url = '".$this->getUrl('adminhtml/inw_requeststock/getImportCsv', array('source' => $source, 'target' => $target))."'+'reason/'+reason;
                xhr.open('POST', url);
                xhr.send(fd);
           }

           function uploadProgress(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                    if(document.getElementById('progressNumber'))
                        document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
                    if(document.getElementById('prog'))
                        document.getElementById('prog').value = percentComplete;
                }
                else {
                    document.getElementById('progressNumber').innerHTML = 'unable to compute';
                }
           }

           function uploadComplete(evt) {
                var import_data = '" . Mage::getModel('admin/session')->getData('null_requeststock_product_import') . "';           
                if(import_data == '1'){
                    alert('No product was added');
                }
                $('requeststock_tabs_products_section').addClassName('notloaded');
                requeststock_tabsJsTabs.showTabContent($('requeststock_tabs_products_section'));
                //varienGlobalEvents.attachEventHandler('showTab',function(){ requeststockproductGridJsObject.doFilter(); });
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
        if (Mage::registry('requeststock_data') && Mage::registry('requeststock_data')->getId()
        ) {
            if (Mage::registry('requeststock_data')->getStatus() == 2) {
                return Mage::helper('inventorywarehouse')->__("View Stock Request No.'%s'", Mage::registry('requeststock_data')->getId()
                );
            } else {
                return Mage::helper('inventorywarehouse')->__("Edit Stock Request No.'%s'", Mage::registry('requeststock_data')->getId()
                );
            }
        }
        return Mage::helper('inventorywarehouse')->__('New Stock Request');
    }
    
    public function checkCancelRequeststock($id)
    {
        $store = Mage::app()->getStore();
        $days = 24*60*60*Mage::getStoreConfig('inventoryplus/transaction/cancel_time', $store->getId());
        $requestStock = Mage::getModel('inventorywarehouse/requeststock')->load($id);
        $createdAt = strtotime($requestStock->getCreatedAt())+$days;
        $now = strtotime(date("y-m-d"));
        $warehouseId = $requestStock->getWarehouseIdFrom();
        $admin = Mage::getSingleton('admin/session')->getUser();
        if($warehouseId && Mage::helper('inventorywarehouse')->canTransfer($admin->getId(), $warehouseId)){
            if(($requestStock->getStatus() == 1)&&($createdAt > $now))
                return true;
        }
        return false;
    }

}

?>
