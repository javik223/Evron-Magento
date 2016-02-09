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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorysupplyneeds Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Adminhtml_Insu_InventorysupplyneedsController extends Magestore_Inventoryplus_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorysupplyneeds_Adminhtml_Insu_InventorysupplyneedsController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/stock_onhand/supplyneeds')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Supply Needs'), Mage::helper('adminhtml')->__('Manage Supply Needs')
        );
        return $this;
    }

    protected function _hasSupplier() {
        $colection = Mage::getModel('inventorypurchasing/supplier')->getCollection();
        if ($colection->getSize() > 0)
            return true;
        return false;
    }

    /**
     * index action
     */
    public function indexAction() {
        if (!$this->_hasSupplier()) {
            Mage::getSingleton('adminhtml/session')->addNotice($this->__('You need to add supplier before using the Supply Needs feature.'));
            return $this->_redirect('adminhtml/inpu_supplier/new');
        }
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Manage Supply Needs'));
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('adminhtml_inventorysupplyneeds.grid')
                ->setProducts($this->getRequest()->getPost('purchase_products', null));
        $this->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'supplyneeds.csv';
        $content = $this->getLayout()
                ->createBlock('inventorysupplyneeds/adminhtml_inventorysupplyneeds_gridexport')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'supplyneeds.xml';
        $content = $this->getLayout()
                ->createBlock('inventorysupplyneeds/adminhtml_inventorysupplyneeds_gridexport')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function viewpoAction() {
        $this->_title($this->__('Draft Purchase Orders'));
        $this->_initAction()->_setActiveMenu('inventoryplus/stock_onhand/supplyneeds');
        $id = $this->getRequest()->getParam('id');
        $draftPO = Mage::getModel('inventorysupplyneeds/draftpo')->load($id);
        Mage::register('draftpo', $draftPO);
        $this->renderLayout();
    }

    /**
     * Create a draff purchase order
     * 
     */
    public function createpoAction() {
        $filter = $this->getRequest()->getParam('top_filter');
        $helperClass = Mage::helper('inventorysupplyneeds');
        if ($helperClass->getDraftPO()->getId()) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                    $helperClass->__('There was an existed draft purchase order. Please process it before creating new one'));
            return $this->_redirect('*/*/index', array('top_filter' => $filter));
        }
        $helperClass->setTopFilter($filter);
        $data = $helperClass->prepareDataForDraftPO();
        try {
            if (!isset($data['product_data']) || !count($data['product_data'])) {
                throw new Exception($helperClass->__('There is no product needed to purchase.'));
            }
            $model = Mage::getModel('inventorysupplyneeds/draftpo')
                    ->addData($data);
            $model->setCreatedAt(now())
                    ->setCreatedBy($this->_getUser()->getUsername());
            $model->create();
            Mage::getSingleton('adminhtml/session')
                    ->addSuccess($helperClass->__('The supply needs have been saved successfully as draft purchase order(s).'));
            return $this->_redirect('*/*/viewpo', array('id' => $model->getId()));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')
                    ->addError($helperClass->__('There is error while creating new draft purchase order.'));
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            return $this->_redirect('*/*/index', array('top_filter' => $filter));
        }
    }

    /**
     * Update draff purchase order
     * 
     */
    public function updatepoAction() {
        $model = Mage::getModel('inventorysupplyneeds/draftpo')
                ->load($this->getRequest()->getParam('id'));
        $field = $this->getRequest()->getParam('field');
        if (!$field) {
            return $this->getResponse()->setBody(json_encode(array('success' => 0)));
        }
        $value = $this->getRequest()->getParam('value');
        $updateData = Mage::helper('inventorysupplyneeds')->prepareUpdateData($field, $value);
        try {
            $returnObject = $model->update($updateData);
            $return = $returnObject->getData();
            $return['success'] = 1;
            return $this->getResponse()->setBody(json_encode($return));
        } catch (Exception $ex) {
            var_dump($ex->getMessage());die();
            return $this->getResponse()->setBody(json_encode(array('success' => 0)));
        }
    }

    /**
     * Save draft purchase order
     * 
     */
    public function saveDraftPOAction() {
        if (!$data = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }
        $productData = $this->_prepareDataForDraftPO($data);
        $model = Mage::getModel('inventorysupplyneeds/draftpo')
                ->load($this->getRequest()->getParam('id'))
                ->setData('product_data', $productData);
        try {
            $model->save();
            Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('inventorysupplyneeds')->__('Data has been saved.'));
            return $this->_redirect('*/*/viewpo', array('id' => $model->getId()));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            return $this->_redirect('*/*/viewpo', array('id' => $model->getId()));
        }
    }

    /**
     * Prepare data for saving draft purchase order
     * 
     * @param array
     * @return array
     */
    protected function _prepareDataForDraftPO($postData) {
        $supplierProducts = $postData['supplierproduct'];
        $products = array();
        if (isset($postData['submit_products']) && $postData['submit_products']) {
            $productData = explode('&', urldecode($postData['submit_products']));
            foreach ($productData as $stringData) {
                $explodedData = explode('=', $stringData);
                $fieldData = array();
                parse_str(base64_decode($explodedData[1]), $fieldData);
                $productId = (int) $explodedData[0];
                $products[$productId] = $fieldData;
                $products[$productId]['supplier_id'] = isset($supplierProducts[$productId]) ? $supplierProducts[$productId] : null;
            }
        }
        return $products;
    }

    /**
     * Create real purchase orders
     * 
     */
    public function submitpoAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorysupplyneeds/draftpo')->load($id);
        try {
            $model->createPurchaseOrders();
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->helper()->__('New purchase orders has been added.'));
            $this->_redirect('adminhtml/inpu_purchaseorders');
        } catch (Exception $e) {
            $model->rollBackPO();
            Mage::getSingleton('adminhtml/session')->addError($this->helper()->__('An error has been occurred when adding new purchase orders. Please try again.'));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/viewpo', array('id' => $id));
        }
    }

    /**
     * Delete a draft purchase order
     * 
     */
    public function deletepoAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorysupplyneeds/draftpo')->setId($id);
        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->helper()->__('The draft purchase order(s) has been deleted successfully.'));
            $this->_redirect('*/*/index');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->helper()->__('Unable to delete draft purchase order(s).'));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/viewpo', array('id' => $id));
        }
    }

    /**
     * Mass change suppliers of products in purchase order
     * 
     */
    public function masschangesupplierAction() {
        $id = $this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type');
        try {
            $this->helper()->massChangeSupplier($id, $type);
            Mage::getSingleton('adminhtml/session')->addSuccess($this->helper()->__('The supplier(s) has been changed successully.'));
            $this->_redirect('*/*/viewpo', array('id' => $id));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->helper()->__('An error has been occurred when changing suppliers. Please try again.'));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/viewpo', array('id' => $id));
        }
    }

    /**
     * Add product to draft purchase order
     * 
     */
    public function addproducttopoAction() {
        $id = $this->getRequest()->getParam('id');
        $sku = $this->getRequest()->getParam('sku');
        $model = Mage::getModel('inventorysupplyneeds/draftpo')->load($id);
        try {
            $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
            if (!$productId) {
                throw new Exception($this->helper()->__('Not found sku: %s', "<i>$sku</i>"));
            }
            $model->addProduct($productId);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->helper()->__('Product %s has been added.', "<i>$sku</i>")
            );
            $this->_redirect('*/*/viewpo', array('id' => $id));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->helper()->__('There is error while adding product.'));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/viewpo', array('id' => $id));
        }
    }

    /**
     * Remove product from draft purchase order
     * 
     */
    public function removeproductAction() {
        $id = $this->getRequest()->getParam('id');
        $poProductId = $this->getRequest()->getParam('poproductid');
        $model = Mage::getModel('inventorysupplyneeds/draftpo_product')->load($poProductId);
        $product = Mage::getResourceModel('catalog/product_collection')
                ->addFieldToFilter('entity_id', $model->getProductId())
                ->getFirstItem();
        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->helper()->__('Product %s has been removed.', '<i>' . $product->getSku() . '</i>')
            );
            $this->_redirect('*/*/viewpo', array('id' => $id));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->helper()->__('There is error while removing product.'));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/viewpo', array('id' => $id));
        }
    }

    public function helper() {
        return Mage::helper('inventorysupplyneeds');
    }

    /**
     * Get logged-in user
     * 
     * @return Varien_Object
     */
    protected function _getUser() {
        return Mage::getSingleton('admin/session')->getUser();
    }

    public function viewpogridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/stock_onhand/supplyneeds');
    }

}
