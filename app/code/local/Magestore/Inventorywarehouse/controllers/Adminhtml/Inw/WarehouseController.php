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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Adminhtml_Inw_WarehouseController extends Magestore_Inventoryplus_Controller_Action {

    /**
     * Show dashboard of warehouse
     * 
     */
    public function dashboardAction() {
        $warehouseId = $this->getRequest()->getParam('id');
        $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
        Mage::register('warehouse', $warehouse);
        $this->loadLayout();
        $this->_setActiveMenu('inventoryplus/warehouse');
        $this->renderLayout();        
    }

    public function storeAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function storegridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('inventorywarehouse/adminhtml_warehouse_edit_tab_store')
                ->toHtml()
        );
    }
    
    public function transactionAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function transactiongridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }    
  
    public function viewtransactionAction() {
        $transactionId = $this->getRequest()->getParam('transaction_id');
        $model = Mage::getModel('inventorywarehouse/transaction')->load($transactionId);

        if ($model->getId() || $transactionId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('transaction_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('inventoryplus/warehouse');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('View Transaction'), Mage::helper('adminhtml')->__('View Transaction')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Transaction'), Mage::helper('adminhtml')->__('Transaction')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
            $this->_addContent($this->getLayout()->createBlock('inventorywarehouse/adminhtml_warehouse_transaction_view'))
                    ->_addLeft($this->getLayout()->createBlock('inventorywarehouse/adminhtml_warehouse_transaction_view_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryplus')->__('Warehouse transaction does not exist!')
            );
            $this->_redirect('*/*/');
        }
    }
	
	public function getImportCsvAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $warehouseProduct = array();
                $warehouseProducts = array();
                $fields = array();
                $count = 0;
                $helper = Mage::helper('inventorywarehouse/warehouse');
                
                if (count($dataFile))
                    foreach ($dataFile as $col => $row) {                   
                        if ($col == 0) {
                            if (count($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {
                           
                            if (count($row))
                                foreach ($row as $index => $cell) {
                               
                                    if (isset($fields[$index])) {
                                        $warehouseProduct[$fields[$index]] = $cell;
                                    }                                    
                                }
                            $source = $this->getRequest()->getParam('source');
                            $productId = Mage::getModel('catalog/product')->getIdBySku($warehouseProduct['SKU']);
                            $warehouseproduct = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id', $source)
                                    ->addFieldToFilter('product_id', $productId);
                            if (!$warehouseproduct->getSize()) {
                                $warehouseProducts[] = $warehouseProduct;
                            }
                        }
                    }                  
                $helper->importProduct($warehouseProducts);
            } catch (Exception $e) {
                
            }
        }
    }
    public function transactionproductViewAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('transaction.edit.tab.products');                
        $this->renderLayout();
    }

    /**
     * export associated stores grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'warehouse_stores.csv';
        $content = $this->getLayout()
            ->createBlock('inventorywarehouse/adminhtml_warehouse_edit_tab_store')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export associated stores grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'warehouse_stores.xml';
        $content = $this->getLayout()
            ->createBlock('inventorywarehouse/adminhtml_warehouse_edit_tab_store')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
}