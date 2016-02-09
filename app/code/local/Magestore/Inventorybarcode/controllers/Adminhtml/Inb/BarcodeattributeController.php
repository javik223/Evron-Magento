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
 * Inventorybarcode Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Adminhtml_Inb_BarcodeattributeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorybarcode_Adminhtml_Inb_BarcodeattributeController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Manage Barcode Attributes'),
                Mage::helper('adminhtml')->__('Manage Barcode Attributes')
            );
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Manage Barcode Attributes'));
        return $this;
    }
 
    /**
     * index action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction()
    {
        $inventorybarcodeId     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('inventorybarcode/barcodeattribute')->load($inventorybarcodeId);
        if(!$inventorybarcodeId){
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Add New Barcode Attribute'));
        }else{
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Edit Barcode Attribute'));
        }
        if ($model->getId() || $inventorybarcodeId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('barcodeattribute_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('inventoryplus');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item News'),
                Mage::helper('adminhtml')->__('Item News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcodeattribute_edit'))
                ->_addLeft($this->getLayout()->createBlock('inventorybarcode/adminhtml_barcodeattribute_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventorybarcode')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }
 
    public function newAction()
    {
        $this->_forward('edit');
    }
 
    /**
     * save item action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            
            $data['attribute_code'] =  $data['attribute_type'] .'_'.$data['attribute_code'];
            
            $checkExists = Mage::getModel('inventorybarcode/barcodeattribute')->load($data['attribute_code'],'attribute_code');
            if(!$this->getRequest()->getParam('id') && $checkExists->getId()){
                 Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorybarcode')->__('An attribute with the same code has been already existed.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }           
            
            if($checkExists->getId() && $checkExists->getId()!=$this->getRequest()->getParam('id')){
                 Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorybarcode')->__('An attribute with the same code has been already existed.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
           
            $model = Mage::getModel('inventorybarcode/barcodeattribute');        
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            
            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('inventorybarcode')->__('The barcode attribute has been successfully created.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('inventorybarcode')->__('Unable to find Barcode attribute to save')
        );
        $this->_redirect('*/*/');
    }
 
    /**
     * delete item action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('inventorybarcode/barcodeattribute');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction()
    {
        $inventorybarcodeIds = $this->getRequest()->getParam('inventorybarcode');
        if (!is_array($inventorybarcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($inventorybarcodeIds as $inventorybarcodeId) {
                    $inventorybarcode = Mage::getModel('inventorybarcode/barcodeattribute')->load($inventorybarcodeId);
                    $inventorybarcode->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted',
                    count($inventorybarcodeIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * mass delete item(s) action
     */
    public function massDisplaybarcodelistAction()
    {       
        $inventorybarcodeIds = $this->getRequest()->getParam('inventorybarcode');
        if (!is_array($inventorybarcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($inventorybarcodeIds as $inventorybarcodeId) {
                    Mage::getSingleton('inventorybarcode/barcodeattribute')
                        ->load($inventorybarcodeId)
                        ->setAttributeDisplay($this->getRequest()->getParam('barcodelist'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($inventorybarcodeIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * mass change status for item(s) action
     */
    public function massStatusAction()
    {
        $inventorybarcodeIds = $this->getRequest()->getParam('inventorybarcode');
       
        if (!is_array($inventorybarcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($inventorybarcodeIds as $inventorybarcodeId) {                     
                    Mage::getSingleton('inventorybarcode/barcodeattribute')
                        ->load($inventorybarcodeId)
                        ->setAttributeStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($inventorybarcodeIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * change attribute code in form
     */
    public function changeattributetypeAction(){
        $result = array();
        $attributeType = $this->getRequest()->getParam('attribute_type');
        $html = Mage::helper('inventorybarcode/attribute')->listBarcodeAttribute($attributeType);
        $result['html'] = $html;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction()
    {
        $fileName   = 'inventorybarcode.csv';
        $content    = $this->getLayout()
                           ->createBlock('inventorybarcode/adminhtml_barcodeattribute_grid')
                           ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction()
    {
        $fileName   = 'inventorybarcode.xml';
        $content    = $this->getLayout()
                           ->createBlock('inventorybarcode/adminhtml_barcodeattribute_grid')
                           ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus');
    }
}