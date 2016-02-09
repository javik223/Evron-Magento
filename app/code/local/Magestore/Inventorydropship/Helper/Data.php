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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';
    
    public function getDropshipStatus()
    {
        return array(
            1    => Mage::helper('inventorydropship')->__('Awaiting supplier\'s confirmation'),
            2    => Mage::helper('inventorydropship')->__('Awaiting approval'),
            3    => Mage::helper('inventorydropship')->__('Awaiting supplier\'s shipment'),
            4    => Mage::helper('inventorydropship')->__('Partially shipped'),
            5    => Mage::helper('inventorydropship')->__('Canceled'),
            6    => Mage::helper('inventorydropship')->__('Complete'),
        );
    }
    
    public function getDropshipForSupplier()
    {
        return array(
            1    => Mage::helper('inventorydropship')->__('Awaiting confirmation'),
            2    => Mage::helper('inventorydropship')->__('Awaiting admin\'s approval'),
            3    => Mage::helper('inventorydropship')->__('Awaiting shipment'),
            4    => Mage::helper('inventorydropship')->__('Partially shipped'),
            5    => Mage::helper('inventorydropship')->__('Canceled'),
            6    => Mage::helper('inventorydropship')->__('Complete'),
        );
    }
    
    public function sendEmailApproveDropShipToSupplier($dropshipId)
    {
        if(!Mage::getStoreConfig('inventoryplus/dropship/sendemail'))
            return;
        
        $supplierNeedToConfirmProvide = false;
        if(Mage::getStoreConfig('inventoryplus/dropship/supplier_confirm_provide'))
            $supplierNeedToConfirmProvide = true;        
        $adminNeedToApprove = false;
        if(Mage::getStoreConfig('inventoryplus/dropship/admin_approve'))
            $adminNeedToApprove = true;
        $supplierNeedToConfirmShipped = false;        
        if(Mage::getStoreConfig('inventoryplus/dropship/supplier_confirm_shipped'))
            $supplierNeedToConfirmShipped = true;            
        if($supplierNeedToConfirmProvide){
            if(!$adminNeedToApprove && !$supplierNeedToConfirmShipped){
                $supplierNeedToConfirmProvide = false;
            }
        }
            
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_approve_to_customer');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $admin = Mage::getModel('admin/session')->getUser();  
        if($admin){
            $from_email = $admin->getEmail(); //fetch sender email Admin
            $from_name = $admin->getName(); //fetch sender name Admin
        }else{
            $from_email = $dropship->getAdminEmail(); 
            $from_name = $dropship->getAdminName();
        }
        $sender = array('email' => $from_email, 'name' => $from_name);        
        $receipientEmail = $supplier->getSupplierEmail(); 
        $receipientName = $supplier->getContactName().'('.$supplier->getSupplierName().')';
        $websites = Mage::app()->getWebsites();
        $code = $websites[1]->getDefaultStore()->getCode();
        $store_url = Mage::getUrl('inventorydropship/supplier/login' , array('_store' => $code));
        $mailTemplate
            ->setTemplateSubject('Approve Drop Ship')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'receipient_name' => $receipientName,
                    'supplier_need_to_confirm_provide' => $supplierNeedToConfirmProvide,
                    'store_url' => $store_url
                )
        );
        $translate->setTranslateInline(true);        
    }
    
    public function sendEmailOfferToSupplier($dropshipId)
    {
        if(!Mage::getStoreConfig('inventoryplus/dropship/sendemail'))
            return;
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_request_to_supplier');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $admin = Mage::getModel('admin/session')->getUser();  
        $from_email = $admin->getEmail(); //fetch sender email Admin
        $from_name = $admin->getName(); //fetch sender name Admin
        $sender = array('email' => $from_email, 'name' => $from_name);        
        $receipientEmail = $supplier->getSupplierEmail(); 
        $receipientName = $supplier->getContactName().'('.$supplier->getSupplierName().')';
        $websites = Mage::app()->getWebsites();
        $code = $websites[1]->getDefaultStore()->getCode();
        $store_url = Mage::getUrl('inventorydropship/supplier/login' , array('_store' => $code));
        $mailTemplate
            ->setTemplateSubject('Email Request Product To Supplier')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'receipient_name' => $receipientName,           
                    'store_url'     => $store_url
                )
        );
        $translate->setTranslateInline(true);
    }
    
    public function sendPasswordResetConfirmationEmail($supplier,$newPassword)
    {        
        try{
            $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_reset_password');
            $mailTemplate = Mage::getModel('core/email_template');
            $translate = Mage::getSingleton('core/translate');                
            $sender = Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_IDENTITY);
            $receipientEmail = $supplier->getSupplierEmail(); 
            $receipientName = $supplier->getContactName().'('.$supplier->getSupplierName().')';
            $websites = Mage::app()->getWebsites();
            $code = $websites[1]->getDefaultStore()->getCode();
            $store_url = Mage::getUrl('inventorydropship/supplier/login' , array('_store' => $code));
            $mailTemplate
                ->setTemplateSubject('Reset Supplier Password')
                ->sendTransactional(
                    $templateId, $sender, $receipientEmail, $receipientName, array(
                        'supplier' => $supplier,
                        'password' => $newPassword,
                        'store_url' => $store_url                           
                    )
            );
            $translate->setTranslateInline(true);
        }catch(Exception $e){
            
        }
    }
    
    public function sendEmailConfirmDropShipToAdmin($dropshipId)
    {        
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_confirm_to_admin');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $admin = Mage::getModel('admin/session')->getUser();  
        $from_email = $supplier->getSupplierEmail();
        $from_name = $supplier->getContactName().'('.$supplier->getSupplierName().')';
        $sender = array('email' => $from_email, 'name' => $from_name);        
        $receipientEmail = $dropship->getAdminEmail(); 
        $receipientName = $dropship->getAdminName();
        $mailTemplate
            ->setTemplateSubject('Confirm Drop Ship')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'receipient_name' => $receipientName,                
                    'supplier' => $supplier,                
                )
        );
        $translate->setTranslateInline(true);
    }
    
    public function sendEmailCancelDropShipToAdmin($dropshipId)
    {        
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_cancel_to_admin');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $admin = Mage::getModel('admin/session')->getUser();  
        $from_email = $supplier->getSupplierEmail();
        $from_name = $supplier->getContactName().'('.$supplier->getSupplierName().')';
        $sender = array('email' => $from_email, 'name' => $from_name);        
        $receipientEmail = $dropship->getAdminEmail(); 
        $receipientName = $dropship->getAdminName();
        $mailTemplate
            ->setTemplateSubject('Cancel Drop Ship')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'receipient_name' => $receipientName,                
                    'supplier' => $supplier,                
                )
        );
        $translate->setTranslateInline(true);
    }
    
    public function sendEmailConfirmShippedToAdmin($dropshipId,$shipped)
    {
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_confirm_shipped_to_admin');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $admin = Mage::getModel('admin/session')->getUser();  
        $from_email = $supplier->getSupplierEmail();
        $from_name = $supplier->getContactName().'('.$supplier->getSupplierName().')';
        $sender = array('email' => $from_email, 'name' => $from_name);        
        $receipientEmail = $dropship->getAdminEmail(); 
        $receipientName = $dropship->getAdminName();
        $mailTemplate
            ->setTemplateSubject('Confirm Drop Ship')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'receipient_name' => $receipientName,                
                    'supplier' => $supplier,  
                    'items' => $shipped,
                )
        );
        $translate->setTranslateInline(true);
    }
    
    public function sendEmailRefundToSupplier($supplierReId,$items)
    {
        if(!Mage::getStoreConfig('inventoryplus/dropship/sendemail'))
            return;
        try{            
            $supplierId = $supplierReId;
            $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
            $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_refund_to_supplier');
            $mailTemplate = Mage::getModel('core/email_template');
            $translate = Mage::getSingleton('core/translate');
            $admin = Mage::getModel('admin/session')->getUser();  
            $from_email = $admin->getEmail(); //fetch sender email Admin
            $from_name = $admin->getName(); //fetch sender name Admin
            $sender = array('email' => $from_email, 'name' => $from_name);        
            $receipientEmail = $supplier->getSupplierEmail(); 
            $receipientName = $supplier->getContactName().'('.$supplier->getSupplierName().')';
            $mailTemplate
                ->setTemplateSubject('Email Refund Product To Supplier')
                ->sendTransactional(
                    $templateId, $sender, $receipientEmail, $receipientName, array(
                        'items' => $items,
                        'receipient_name' => $receipientName,                
                    )
            );
            $translate->setTranslateInline(true);
        }catch(Exception $e){
            
        }
    }
    
    public function sendEmailCancelDropShipToSupplier($dropshipId)
    {
        if(!Mage::getStoreConfig('inventoryplus/dropship/sendemail'))
            return;
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $templateId = Mage::getStoreConfig('inventoryplus/dropship/email_cancel_to_customer');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $admin = Mage::getModel('admin/session')->getUser();  
        $from_email = $admin->getEmail(); //fetch sender email Admin
        $from_name = $admin->getName(); //fetch sender name Admin
        $sender = array('email' => $from_email, 'name' => $from_name);        
        $receipientEmail = $supplier->getSupplierEmail(); 
        $receipientName = $supplier->getContactName().'('.$supplier->getSupplierName().')';
        $websites = Mage::app()->getWebsites();
        $code = $websites[1]->getDefaultStore()->getCode();
        $store_url = Mage::getUrl('inventorydropship/supplier/login' , array('_store' => $code));
        $mailTemplate
            ->setTemplateSubject('Cancel Drop Ship')
            ->sendTransactional(
                $templateId, $sender, $receipientEmail, $receipientName, array(
                    'dropship' => $dropship,
                    'receipient_name' => $receipientName,      
                    'store_url'     => $store_url
                )
        );
        $translate->setTranslateInline(true);
    }
    
    public function useDropship($product_id , $order_id){
        $resource = Mage::getModel('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $sql = 'SELECT * FROM '.$resource->getTableName('inventorydropship/inventorydropship').' as `dropship`'
                                            .' INNER JOIN '.$resource->getTableName('inventorydropship/inventorydropship_product').' as `dropship_product`'
                                            .' ON dropship.dropship_id = dropship_product.dropship_id'
                                            .' AND dropship.order_id = '.$order_id
                                            .' AND dropship_product.product_id = '.$product_id;        
        $result = $readConnection->fetchAll($sql);              
        if($result != null){
            return 1;
        } else return 0;
    }
}