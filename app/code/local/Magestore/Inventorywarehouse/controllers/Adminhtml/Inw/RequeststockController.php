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
class Magestore_Inventorywarehouse_Adminhtml_Inw_RequeststockController extends Magestore_Inventoryplus_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Request Stock'));
        $this->loadLayout()->_setActiveMenu('inventoryplus/stock_in/requeststock');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Request Stock'));
        $this->loadLayout()->_setActiveMenu('inventoryplus');
        $this->_setActiveMenu('inventoryplus/stock_in/requeststock');
        $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Stock Requesting Manager'), Mage::helper('inventorywarehouse')->__('Stock Transfering Manager')
        );
        $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Stock Requesting News'), Mage::helper('inventorywarehouse')->__('Stock Transfering News')
        );
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
        $this->_addContent($this->getLayout()->createBlock('inventorywarehouse/adminhtml_requeststock_edit'))
                ->_addLeft($this->getLayout()->createBlock('inventorywarehouse/adminhtml_requeststock_edit_tabs'));
        $this->renderLayout();
    }

    public function editAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Request Stock'));
        $requeststock = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorywarehouse/requeststock')->load($requeststock);
        if ($model->getId() || $requeststock == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('requeststock_data', $model);

            $this->loadLayout()->_setActiveMenu('inventoryplus');
            $this->_setActiveMenu('inventoryplus/stock_in/requeststock');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Manage Stock Requests'), Mage::helper('adminhtml')->__('Manage Stock Requests')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Request Stock News'), Mage::helper('adminhtml')->__('Request Stock News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
            $this->_addContent($this->getLayout()->createBlock('inventorywarehouse/adminhtml_requeststock_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventorywarehouse/adminhtml_requeststock_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorywarehouse')->__('Requesting stock does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('requeststock.edit.tab.products');
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('requeststock_product_import'))
            Mage::getModel('admin/session')->setData('requeststock_product_import', null);
    }

    public function productsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('requeststock.edit.tab.products');
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('requeststock_product_import'))
            Mage::getModel('admin/session')->setData('requeststock_product_import', null);
    }

    public function checkproductAction() {
        $requeststock_products = $this->getRequest()->getPost('products');
        $checkProduct = 0;
        $next = false;
        if (isset($requeststock_products)) {
            $stockrequestProducts = array();
            $stockrequestProductsExplodes = explode('&', urldecode($requeststock_products));
            if (count($stockrequestProductsExplodes) <= 900) {
                parse_str(urldecode($requeststock_products), $stockrequestProducts);
            } else {
                foreach ($stockrequestProductsExplodes as $stockrequestProductsExplode) {
                    $stockrequestProduct = '';
                    parse_str($stockrequestProductsExplode, $stockrequestProduct);
                    $stockrequestProducts = $stockrequestProducts + $stockrequestProduct;
                }
            }
            if (count($stockrequestProducts)) {
                foreach ($stockrequestProducts as $pId => $enCoded) {
                    $codeArr = array();
                    parse_str(base64_decode($enCoded), $codeArr);
                    if (is_numeric($codeArr['qty_request']) && $codeArr['qty_request'] > 0) {
                        $checkProduct = 1;
                        $next = true;
                        break;
                    }
                }
            }
        }
        echo $checkProduct;
    }

    public function getImportCsvAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                Mage::getModel('admin/session')->setData('request_stock_reason', null);
                if ($this->getRequest()->getParam('reason')) {
                    Mage::getModel('admin/session')->setData('request_stock_reason', $this->getRequest()->getParam('reason'));
                }
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $requeststockProduct = array();
                $requeststockProducts = array();
                $fields = array();
                $count = 0;
                $helper = Mage::helper('inventorywarehouse');
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
                                        $requeststockProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $source = $this->getRequest()->getParam('source');
                            if ($source != 'others') {
                                $productId = Mage::getModel('catalog/product')->getIdBySku($requeststockProduct['SKU']);
                                $warehouseproduct = Mage::getModel('inventoryplus/warehouse_product')
                                        ->getCollection()
                                        ->addFieldToFilter('warehouse_id', $source)
                                        ->addFieldToFilter('product_id', $productId);
                                if ($warehouseproduct->getSize()) {
                                    $requeststockProducts[] = $requeststockProduct;
                                }
                            } else
                                $requeststockProducts[] = $requeststockProduct;
                        }
                    }
                Mage::getModel('admin/session')->setData('requeststock_product_import', null);
                Mage::getModel('admin/session')->setData('null_requeststock_product_import', null);
                if (count($requeststockProducts)) {
                    Mage::getModel('admin/session')->setData('requeststock_product_import', $requeststockProducts);
                } else {
                    Mage::getModel('admin/session')->setData('null_requeststock_product_import', 1);
                }
            } catch (Exception $e) {
                
            }
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['warehouse_source']) && $data['warehouse_source'] != 'others') {
                $data['warehouse_id_from'] = $data['warehouse_source'];
            }
            if (isset($data['warehouse_target'])) {
                $data['warehouse_id_to'] = $data['warehouse_target'];
            }
            $warehourseTarget = $warehourseSource = '';

            if (isset($data['warehouse_id_from']))
                $warehourseSource = Mage::getModel('inventoryplus/warehouse')->load($data['warehouse_id_from']);
            $warehourseTarget = Mage::getModel('inventoryplus/warehouse')->load($data['warehouse_id_to']);
            if ($warehourseSource && $warehourseSource->getWarehouseName() && $data['warehouse_source'] != 'others')
                $data['warehouse_name_from'] = $warehourseSource->getWarehouseName();
            elseif ($data['warehouse_source'] == 'others')
                $data['warehouse_name_from'] = Mage::helper('inventorywarehouse')->__('Others');
            if ($warehourseTarget->getWarehouseName())
                $data['warehouse_name_to'] = $warehourseTarget->getWarehouseName();
            $admin = Mage::getModel('admin/session')->getUser()->getUsername();
            $data['created_by'] = $admin;
            $now = now();
            $data['created_at'] = $now;
            //create send transaction data
            $transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
            $transactionSendData = array();
            $transactionSendData['type'] = '1';
            $transactionSendData['warehouse_id_from'] = $transactionSendData['warehouse_name_from'] = $transactionSendData['warehouse_id_to'] = $transactionSendData['warehouse_name_to'] = '';
            if (isset($data['warehouse_id_from']))
                $transactionSendData['warehouse_id_from'] = $data['warehouse_id_from'];
            if (isset($data['warehouse_name_from']))
                $transactionSendData['warehouse_name_from'] = $data['warehouse_name_from'];
            $transactionSendData['warehouse_id_to'] = $data['warehouse_id_to'];
            $transactionSendData['warehouse_name_to'] = $data['warehouse_name_to'];
            $transactionSendData['created_at'] = $data['created_at'];
            $transactionSendData['created_by'] = $data['created_by'];
            $transactionSendData['reason'] = $data['reason'];
            $transactionSendModel->addData($transactionSendData);
            //create receive transaction data
            $transactionReceiveData['warehouse_id_from'] = $transactionReceiveData['warehouse_name_from'] = $transactionReceiveData['warehouse_id_to'] = $transactionReceiveData['warehouse_name_to'] = '';
            $transactionReceiveModel = Mage::getModel('inventorywarehouse/transaction');
            $transactionReceiveData = array();
            $transactionReceiveData['type'] = '2';
            if (isset($data['warehouse_id_from']))
                $transactionReceiveData['warehouse_id_from'] = $data['warehouse_id_from'];
            if (isset($data['warehouse_name_from']))
                $transactionReceiveData['warehouse_name_from'] = $data['warehouse_name_from'];
            $transactionReceiveData['warehouse_id_to'] = $data['warehouse_id_to'];
            $transactionReceiveData['warehouse_name_to'] = $data['warehouse_name_to'];
            $transactionReceiveData['created_at'] = $data['created_at'];
            $transactionReceiveData['created_by'] = $data['created_by'];
            $transactionReceiveData['reason'] = $data['reason'];
            $transactionReceiveModel->addData($transactionReceiveData);
            try {
                if ($this->getRequest()->getParam('id')) {
                    $model = Mage::getModel('inventorywarehouse/requeststock')->load($this->getRequest()->getParam('id'));
                    $model->addData($data);
                    $model->save();
                } else {
                    $model = Mage::getModel('inventorywarehouse/requeststock');
                    $model->setData($data);
                    $model->save();
                }
                if (isset($data['warehouse_id_from']))
                    $transactionSendModel->setRequestStockId($model->getId())
                            ->save();
                else
                    $transactionSendModel->save();
                $transactionReceiveModel->save();
                //save product
                if (isset($data['requeststock_products'])) {
                    $requeststockProducts = array();
                    parse_str(urldecode($data['requeststock_products']), $requeststockProducts);
                    $total = array();
                    $notReceive = array();
                    $source = $target = '';
                    if (isset($data['warehouse_id_from']))
                        $source = $data['warehouse_id_from'];
                    $target = $data['warehouse_id_to'];
                    if (count($requeststockProducts)) {
                        foreach ($requeststockProducts as $pId => $enCoded) {
                            $codeArr = array();
                            $qty = 0;
                            $product = Mage::getModel('catalog/product')->load($pId);
                            parse_str(base64_decode($enCoded), $codeArr);
                            $requeststockProductsItem = Mage::getModel('inventorywarehouse/requeststock_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_requeststock_id', $model->getId())
                                    ->addFieldToFilter('product_id', $pId)
                                    ->getFirstItem();
                            if ($requeststockProductsItem->getId()) {
                                if ($codeArr['qty_receive']) {
                                    if (!is_numeric($codeArr['qty_receive']) || $codeArr['qty_receive'] < 0)
                                        continue;
                                    $qty = (int) $codeArr['qty_receive'];
                                }elseif ($codeArr['qty_transfer']) {
                                    if (!is_numeric($codeArr['qty_transfer']) || $codeArr['qty_transfer'] < 0)
                                        continue;
                                    $qty = (int) $codeArr['qty_transfer'];
                                }
                                $requeststockProductsItem
                                        // ->setProductId($pId)
                                        ->setQtyReceive($qty)
                                        ->save();
                                array_push($total, (int) $qty);
                            }else {
                                $qty = $codeArr['qty_request'];
                                if ($source) {
                                    $warehouse = Mage::getModel('inventoryplus/warehouse_product')
                                            ->getCollection()
                                            ->addFieldToFilter('warehouse_id', $source)
                                            ->addFieldToFilter('product_id', $pId)
                                            ->getFirstItem();
                                    if (!is_numeric($codeArr['qty_request']) || (int) $codeArr['qty_request'] < 0)
                                        $codeArr['qty_request'] = 0;
                                    elseif ((int) $codeArr['qty_request'] <= (int) $warehouse->getTotalQty())
                                        $qty = (int) $codeArr['qty_request'];
                                    elseif ((int) $codeArr['qty_request'] > (int) $warehouse->getTotalQty() && $data['warehouse_source'] != 'others')
                                        $qty = (int) $warehouse->getTotalQty();
                                    elseif ((int) $codeArr['qty_request'] > (int) $warehouse->getTotalQty() && $data['warehouse_source'] == 'others')
                                        $qty = $codeArr['qty_request'];
                                }
                                Mage::getModel('inventorywarehouse/requeststock_product')
                                        ->setProductId($pId)
                                        ->setWarehouseRequeststockId($model->getId())
                                        ->setProductSku($product->getSku())
                                        ->setProductName($product->getName())
                                        ->setQty($qty)
                                        ->save();
                                $warehouseProductTarget = Mage::getModel('inventoryplus/warehouse_product')
                                        ->getCollection()
                                        ->addFieldToFilter('warehouse_id', $target)
                                        ->addFieldToFilter('product_id', $pId)
                                        ->getFirstItem();
                                if ($warehouseProductTarget && $warehouseProductTarget->getId()) {
                                    $qtyTarget = $warehouseProductTarget->getTotalQty() + $qty;
                                    $qtyAvailableTarget = $warehouseProductTarget->getAvailableQty() + $qty;
                                    $warehouseProductTarget->setTotalQty($qtyTarget)
                                            ->setAvailableQty($qtyAvailableTarget)
                                            ->save();
                                } else {
                                    Mage::getModel('inventoryplus/warehouse_product')
                                            ->setWarehouseId($target)
                                            ->setProductId($pId)
                                            ->setTotalQty($qty)
                                            ->setAvailableQty($qty)
                                            ->save();
                                }
                                if ($data['warehouse_source'] != 'others') {
                                    $currentQty = (int) $warehouse->getTotalQty() - $qty;
                                    $currentQtyAvailable = (int) $warehouse->getAvailableQty() - $qty;
                                    $warehouse->setTotalQty($currentQty);
                                    $warehouse->setAvailableQty($currentQtyAvailable);
                                    $warehouse->save();
                                } else {
                                    $stock_item = Mage::getModel('cataloginventory/stock_item')
                                            ->getCollection()
                                            ->addFieldToFilter('product_id', $pId)
                                            ->getFirstItem();
                                    $stock_item_qty = $stock_item->getQty();
                                    $new_stock_qty = $stock_item_qty + $qty;
                                    $stock_item->setQty($new_stock_qty)->save();
                                }
                                //save products to transaction product table for send transaction
                                Mage::getModel('inventorywarehouse/transaction_product')
                                        ->setWarehouseTransactionId($transactionSendModel->getId())
                                        ->setProductId($pId)
                                        ->setProductSku($product->getSku())
                                        ->setProductName($product->getName())
                                        ->setQty(-$qty)
                                        ->save()
                                ;
                                //save products to transaction product table for receive transaction
                                Mage::getModel('inventorywarehouse/transaction_product')
                                        ->setWarehouseTransactionId($transactionReceiveModel->getId())
                                        ->setProductId($pId)
                                        ->setProductSku($product->getSku())
                                        ->setProductName($product->getName())
                                        ->setQty($qty)
                                        ->save()
                                ;
                                $qty_request = (int) $qty;
                                array_push($total, $qty_request);
                            }
                        }
                        $totalProducts = array_sum($total);
                        $model->setTotalProducts($totalProducts);
                        $model->save();
                        $transactionSendModel->setTotalProducts(-$totalProducts);
                        $transactionSendModel->save();
                        $transactionReceiveModel->setTotalProducts($totalProducts);
                        $transactionReceiveModel->save();
                    }
                    $store = Mage::app()->getStore();
                    try {
                        if (Mage::getStoreConfig('inventoryplus/transaction/transaction_notice', $store->getId())) {
                            $warehouseSource = Mage::getModel('inventoryplus/warehouse')->load($source);
                            if ($source && !$warehouseSource->getIsUnwarehouse()) {
                                $stockName = Mage::helper('inventorywarehouse')->__('Request Stock');
                                Mage::helper('inventorywarehouse/email')->sendSendstockEmail($warehouseSource, $model->getId(), 0, $stockName);
                            }
                        }
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_management.log');
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('inventorywarehouse')->__('Stock request was successfully created.')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('adminhtml/inw_requeststock/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('adminhtml/inw_requeststock/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorywarehouse')->__('Unable to save')
            );
            $this->_redirect('*/*/');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventorywarehouse')->__('Unable to save')
            );
            $this->_redirect('*/*/');
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function cancelAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorywarehouse/requeststock')->load($id);
        $send_warehouse = $model->getWarehouseIdTo();
        $receive_warehouse = $model->getWarehouseIdFrom(); //zend_debug::dump($receive_warehouse.','.$send_warehouse);die();
        try {
            //change status of send stock record
            $model->setStatus(2)->save();
            //create send transaction
            $transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
            $transactionSendData = array();
            $transactionSendData['type'] = '1';
            $transactionSendData['warehouse_id_from'] = $model->getWarehouseIdTo();
            $transactionSendData['warehousename_name'] = $model->getWarehouseNameTo();
            $transactionSendData['warehouse_id_to'] = $model->getWarehouseIdFrom();
            $transactionSendData['warehouse_name_to'] = $model->getWarehouseNameFrom();
            $transactionSendData['created_at'] = $model->getCreatedAt();
            $transactionSendData['created_by'] = $model->getCreatedBy();
            $transactionSendData['reason'] = Mage::helper('inventorywarehouse')->__("Cancel Stock Requesting No.'%s'" . $id);
            $transactionSendData['total_products'] = -$model->getTotalProducts();
            $transactionSendModel->addData($transactionSendData);
            $transactionSendModel->save();
            //create receive transaction
            $transactionReceiveModel = Mage::getModel('inventorywarehouse/transaction');
            $transactionReceiveData = array();
            $transactionReceiveData['type'] = '2';
            $transactionReceiveData['warehouse_id_from'] = $model->getWarehouseIdTo();
            $transactionReceiveData['warehouse_name_from'] = $model->getWarehouseNameTo();
            $transactionReceiveData['warehouse_id_to'] = $model->getWarehouseIdFrom();
            $transactionReceiveData['warehouse_name_to'] = $model->getWarehouseNameFrom();
            $transactionReceiveData['created_at'] = $model->getCreatedAt();
            $transactionReceiveData['created_by'] = $model->getCreatedBy();
            $transactionReceiveData['reason'] = Mage::helper('inventoryplus')->__("Cancel Stock Requesting No.'%s'" . $id);
            $transactionReceiveData['total_products'] = $model->getTotalProducts();
            $transactionReceiveModel->addData($transactionReceiveData);
            $transactionReceiveModel->save();

            //recalculate qty
            $requeststockProducts = Mage::getModel('inventorywarehouse/requeststock_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_requeststock_id', $id);
            foreach ($requeststockProducts as $requeststockProduct) {
                $pId = $requeststockProduct->getProductId();
                $pSku = $requeststockProduct->getProductSku();
                $pName = $requeststockProduct->getProductName();
                //get qty of product using for transaction                
                $qty = $requeststockProduct->getQty();
                //save products to transaction product table for send transaction
                Mage::getModel('inventorywarehouse/transaction_product')
                        ->setWarehouseTransactionId($transactionSendModel->getId())
                        ->setProductId($pId)
                        ->setProductSku($pSku)
                        ->setProductName($pName)
                        ->setQty(-$qty)
                        ->save()
                ;
                //save products to transaction product table for receive transaction
                Mage::getModel('inventorywarehouse/transaction_product')
                        ->setWarehouseTransactionId($transactionReceiveModel->getId())
                        ->setProductId($pId)
                        ->setProductSku($pSku)
                        ->setProductName($pName)
                        ->setQty($qty)
                        ->save()
                ;
                //recalculate product qty for warehouse send
                if ($send_warehouse != 0) {
                    $send_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                            ->getCollection()
                            ->addFieldToFilter('warehouse_id', $send_warehouse)
                            ->addFieldToFilter('product_id', $pId)
                            ->getFirstItem();
                    $newProductsQtySend = $send_warehouse_products->getTotalQty() - $qty;
                    $newProductsQtyAvailableSend = $send_warehouse_products->getAvailableQty() - $qty;
                    if ($newProductsQtySend == 0 && $newProductsQtyAvailableSend == 0) {
                        $send_warehouse_products->delete();
                    } else {
                        $send_warehouse_products
                                ->setTotalQty($newProductsQtySend)
                                ->setAvailableQty($newProductsQtyAvailableSend)
                                ->save();
                    }
                    $store = Mage::app()->getStore();
                    try {
                        if (Mage::getStoreConfig('inventoryplus/transaction/transaction_notice', $store->getId())) {
                            $warehouseTaget = Mage::getModel('inventoryplus/warehouse')->load($send_warehouse);
                            if ($send_warehouse && !$warehouseTaget->getIsUnwarehouse()) {
                                $stockName = "Cancel Request Stock #" . $id;
                                Mage::helper('inventorywarehouse/email')->sendSendstockEmail($warehouseTaget, $id, 0, $stockName);
                            }
                        }
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_management.log');
                    }
                }
                //recalculate product qty for warehouses receive
                if ($receive_warehouse) {
                    $receive_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                            ->getCollection()
                            ->addFieldToFilter('warehouse_id', $receive_warehouse)
                            ->addFieldToFilter('product_id', $pId)
                            ->getFirstItem();
                    $newProductsQtyReceive = $receive_warehouse_products->getTotalQty() + $qty;
                    $newProductsQtyAvailableReceive = $receive_warehouse_products->getAvailableQty() + $qty;
                    if (!$receive_warehouse_products->getId()) {
                        $receive_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                                ->setData('warehouse_id', $receive_warehouse)
                                ->setData('product_id', $pId)
                                ->setData('available_qty', $qty)
                                ->setData('total_qty', $qty)
                                ->save();
                    } else {
                        $receive_warehouse_products
                                ->setTotalQty($newProductsQtyReceive)
                                ->setAvailableQty($newProductsQtyAvailableReceive)
                                ->save();
                    }
                } else {
                    $stock_item = Mage::getModel('cataloginventory/stock_item')
                            ->getCollection()
                            ->addFieldToFilter('product_id', $pId)
                            ->getFirstItem();
                    $stock_item_qty = $stock_item->getQty();
                    $new_stock_qty = $stock_item_qty - $qty;
                    $stock_item->setQty($new_stock_qty)->save();
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('inventorywarehouse')->__('Stock request was successfully canceled.')
            );
            if ($this->getRequest()->getParam('warehouse_id'))
                $this->_redirect('adminhtml/inw_warehouse/edit', array('id' => $this->getRequest()->getParam('warehouse_id')));
            else
                $this->_redirect('adminhtml/inw_requeststock/index');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            if ($this->getRequest()->getParam('warehouse_id'))
                $this->_redirect('adminhtml/inw_warehouse/edit', array('id' => $this->getRequest()->getParam('warehouse_id')));
            else
                $this->_redirect('adminhtml/inw_requeststock/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventorywarehouse')->__('Unable to cancel')
        );
        if ($this->getRequest()->getParam('warehouse_id'))
            $this->_redirect('adminhtml/inw_warehouse/edit', array('id' => $this->getRequest()->getParam('warehouse_id')));
        else
            $this->_redirect('adminhtml/inw_requeststock/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'stock_request.csv';
        $content = $this->getLayout()
                ->createBlock('inventorywarehouse/adminhtml_requeststock_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'stock_request.xml';
        $content = $this->getLayout()
                ->createBlock('inventorywarehouse/adminhtml_requeststock_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/stock_in/requeststock');
    }

}
