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
class Magestore_Inventorywarehouse_Adminhtml_Inw_SendstockController extends Magestore_Inventoryplus_Controller_Action
{
    public function indexAction() {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Manage Send Stock'));
        $this->loadLayout()->_setActiveMenu('inventoryplus/stock_out/sendstock');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Send Stock'));
        $this->loadLayout()->_setActiveMenu('inventoryplus/stock_out/sendstock');
        
        $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Manage Stock Sending'), Mage::helper('adminhtml')->__('Manage Stock Sending')
        );
        $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Stock Sending News'), Mage::helper('adminhtml')->__('Stock Sending News')
        );
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('inventorywarehouse/adminhtml_sendstock_edit'))
                ->_addLeft($this->getLayout()->createBlock('inventorywarehouse/adminhtml_sendstock_edit_tabs'));
        $this->renderLayout();
    }

    public function editAction() {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Send Stock'));
        $sendstock = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorywarehouse/sendstock')->load($sendstock);

        if ($model->getId() || $sendstock == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('sendstock_data', $model);

            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_out/sendstock');
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Manage Stock Sending'), Mage::helper('adminhtml')->__('Manage Stock Sending')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Stock Sending News'), Mage::helper('adminhtml')->__('Stock Sending News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('inventorywarehouse/adminhtml_sendstock_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventorywarehouse/adminhtml_sendstock_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryplus')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function productsAction() {
        $this->loadLayout();
        
        $this->getLayout()->getBlock('sendstock.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('oproducts', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('sendstock_product_import'))
            Mage::getModel('admin/session')->setData('sendstock_product_import', null);
    }

    public function productsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('sendstock.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('oproducts', null));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction() {
        $data = $this->getRequest()->getPost();
        if ($data) {
            //save send stock information
            $model = Mage::getModel('inventorywarehouse/sendstock')->load($this->getRequest()->getParam('id'));
            if (isset($data['warehouse_source'])) {
                $data['warehouse_id_from'] = $data['warehouse_source'];
            }
            if (isset($data['warehouse_target'])) {
                $data['warehouse_id_to'] = $data['warehouse_target'];
            }
            $warehourseSource = Mage::getModel('inventoryplus/warehouse')->load($data['warehouse_id_from']);
            if ($data['warehouse_id_to'] != 'others') {
                $warehourseTarget = Mage::getModel('inventoryplus/warehouse')->load($data['warehouse_id_to']);
                if ($warehourseTarget->getWarehouseName())
                    $data['warehouse_name_to'] = $warehourseTarget->getWarehouseName();
            }else if ($data['warehouse_id_to'] == 'others') {
                $data['warehouse_id_to'] = '';
                $data['warehouse_name_to'] = 'Others';
            }
            if ($warehourseSource->getWarehouseName())
                $data['warehouse_name_from'] = $warehourseSource->getWarehouseName();
            $createdAt = date('Y-m-d', strtotime(now()));
            $data['created_at'] = $createdAt;
            $admin = Mage::getModel('admin/session')->getUser()->getUsername();
            if ($this->getRequest()->getParam('id')) {
                $data['created_by'] = $model->getData('created_by');
            } else {
                $data['created_by'] = $admin;
            }
            $data['status'] = 1;
            $model->addData($data);

            //create send transaction data
            $transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
            $transactionSendData = array();
            $transactionSendData['type'] = '1';
            $transactionSendData['warehouse_id_from'] = $data['warehouse_id_from'];
            $transactionSendData['warehouse_name_from'] = $data['warehouse_name_from'];
            $transactionSendData['warehouse_id_to'] = $data['warehouse_id_to'];
            $transactionSendData['warehouse_name_to'] = $data['warehouse_name_to'];
            $transactionSendData['created_at'] = $data['created_at'];
            $transactionSendData['created_by'] = $data['created_by'];
            $transactionSendData['reason'] = $data['reason'];
            $transactionSendModel->addData($transactionSendData);

            //create receive transaction data
            $transactionReceiveModel = Mage::getModel('inventorywarehouse/transaction');
            if ($data['warehouse_id_to'] != '') {
                $transactionReceiveData = array();
                $transactionReceiveData['type'] = '2';
                $transactionReceiveData['warehouse_id_from'] = $data['warehouse_id_from'];
                $transactionReceiveData['warehouse_name_from'] = $data['warehouse_name_from'];
                $transactionReceiveData['warehouse_id_to'] = $data['warehouse_id_to'];
                $transactionReceiveData['warehouse_name_to'] = $data['warehouse_name_to'];
                $transactionReceiveData['created_at'] = $data['created_at'];
                $transactionReceiveData['created_by'] = $data['created_by'];
                $transactionReceiveData['reason'] = $data['reason'];
                $transactionReceiveModel->addData($transactionReceiveData);
            }

            try {
                //save data
                $model->save();
                $transactionSendModel->save();
                $transactionReceiveModel->save();
                //save products
                if (isset($data['sendstock_products'])) {
                    $sendstockProducts = array();
                    $total = array();
                    parse_str(urldecode($data['sendstock_products']), $sendstockProducts);
                    if (count($sendstockProducts)) {
                        foreach ($sendstockProducts as $pId => $enCoded) {
                            $product = Mage::getModel('catalog/product')->load($pId);
                            $codeArr = array();
                            $qty = 0;
                            parse_str(base64_decode($enCoded), $codeArr);
                            $send_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id', $data['warehouse_id_from'])
                                    ->addFieldToFilter('product_id', $pId)
                                    ->getFirstItem();
                            
                            if (!empty($codeArr['qty'])) {
                                if ((int) $codeArr['qty'] > (int) $send_warehouse_products->getTotalQty()) {
                                    $qty = $send_warehouse_products->getTotalQty();
                                } else {
                                    $qty = $codeArr['qty'];
                                }
                                $total[] = $qty;                              
                            }
                            //save products to sendstock product table
                            Mage::getModel('inventorywarehouse/sendstock_product')
                                    ->setWarehouseSendstockId($model->getId())
                                    ->setProductId($pId)
                                    ->setProductSku($product->getSku())
                                    ->setProductName($product->getName())
                                    ->setQty((-$qty))
                                    ->save()
                            ;
                            //save products to transaction product table for send transaction
                            
                            Mage::getModel('inventorywarehouse/transaction_product')
                                    ->setWarehouseTransactionId($transactionSendModel->getId())
                                    ->setProductId($pId)
                                    ->setProductSku($product->getSku())
                                    ->setProductName($product->getName())
                                    ->setQty(-$qty)
                                    ->save();
                            //save products to transaction product table for receive transaction
                            if ($transactionReceiveModel->getId()) {
                                Mage::getModel('inventorywarehouse/transaction_product')
                                        ->setWarehouseTransactionId($transactionReceiveModel->getId())
                                        ->setProductId($pId)
                                        ->setProductSku($product->getSku())
                                        ->setProductName($product->getName())
                                        ->setQty($qty)
                                        ->save()
                                ;
                            }
                            //Recalculate products for sending warehouse
                            $new_send_warehouse_qty = $send_warehouse_products->getTotalQty() - $qty;
                            $new_send_warehouse_qty_available = $send_warehouse_products->getAvailableQty() - $qty;
                            $send_warehouse_products->setTotalQty($new_send_warehouse_qty)
                                    ->setAvailableQty($new_send_warehouse_qty_available)
                                    ->save();
                            //Recalculate products for receiving warehouse
                            if ($data['warehouse_id_to'] != '') {
                                $receive_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                                        ->getCollection()
                                        ->addFieldToFilter('warehouse_id', $data['warehouse_id_to'])
                                        ->addFieldToFilter('product_id', $pId)
                                        ->getFirstItem();
                                if ($receive_warehouse_products->getId()) {
                                    $new_receive_warehouse_qty = $receive_warehouse_products->getTotalQty() + $qty;
                                    $new_receive_warehouse_qty_available = $receive_warehouse_products->getAvailableQty() + $qty;
                                    $receive_warehouse_products
                                            ->setTotalQty($new_receive_warehouse_qty)
                                            ->setAvailableQty($new_receive_warehouse_qty_available)
                                            ->save();
                                } else {
                                    Mage::getModel('inventoryplus/warehouse_product')
                                            ->setWarehouseId($data['warehouse_id_to'])
                                            ->setProductId($pId)
                                            ->setTotalQty($qty)
                                            ->setAvailableQty($qty)
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
                    }
                }
                //save total products for sendstock                
                $totalProducts = array_sum($total);
                $model->setTotalProducts(-$totalProducts);
                $model->save();
                
                //save total products and send_stock id for transaction                  
                $transactionSendModel
                        ->setTotalProducts(-$totalProducts)
                        ->setWarehouseSendstockId($model->getId());
                $transactionSendModel->save();
                
                
                
                if ($data['warehouse_id_to'] != '') {
                    $transactionReceiveModel
                            ->setWarehouseSendstockId($model->getId())
                            ->setTotalProducts($totalProducts);
                    $transactionReceiveModel->save();
                }
                
                //send email to admin of receive warehouse
                if (Mage::getStoreConfig('inventoryplus/transaction/transaction_notice') == 1) {
                    $stockName = "Send stock No." . $model->getId();
                    if ($data['warehouse_id_to'] != '' || $data['warehouse_id_to'] != '1') {
                        $warehouseTarget = Mage::getModel('inventoryplus/warehouse')->load($data['warehouse_id_to']);
                        try{
                            if ($warehouseTarget) {
                                Mage::helper('inventorywarehouse/email')->sendSendstockEmail($warehouseTarget, $model->getId(), 1, $stockName);
                            }
                        }  catch (Exception $e){
                            Mage::log($e->getMessage(),null,'inventory_management.log');
                        }
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('inventoryplus')->__('Stock sending was successfully created.')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('adminhtml/inw_sendstock/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('adminhtml/inw_sendstock/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }

            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryplus')->__('Unable to save')
            );
            $this->_redirect('*/*/');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryplus')->__('Unable to save')
            );
            $this->_redirect('*/*/');
        }
    }

    public function checkproductAction() {
        $sendstock_products = $this->getRequest()->getPost('products');
        $checkProduct = 0;
        $next = false;
        if (isset($sendstock_products)) {
            $sendstockProducts = array();
            $sendstockProductsExplodes = explode('&', urldecode($sendstock_products));
            if (count($sendstockProductsExplodes) <= 900) {
                parse_str(urldecode($sendstock_products), $sendstockProducts);
            } else {
                foreach ($sendstockProductsExplodes as $sendstockProductsExplode) {
                    $sendstockProduct = '';
                    parse_str($sendstockProductsExplode, $sendstockProduct);
                    $sendstockProducts = $sendstockProducts + $sendstockProduct;
                }
            }
            if (count($sendstockProducts)) {
                foreach ($sendstockProducts as $pId => $enCoded) {
                    $codeArr = array();
                    parse_str(base64_decode($enCoded), $codeArr);
                    if (is_numeric($codeArr['qty']) && $codeArr['qty'] > 0) {
                        $checkProduct = 1;
                        $next = true;
                        break;
                    }
                }
            }
        }
        echo $checkProduct;
    }

    public function cancelAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorywarehouse/sendstock')->load($id);
        $send_warehouse = $model->getWarehouseIdTo();
        $receive_warehouse = $model->getWarehouseIdFrom();
        try {
            //change status of send stock record
            $model->setStatus(2)->save();
            //create send transaction
            $transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
            $transactionSendData = array();
            $transactionSendData['type'] = '1';
            $transactionSendData['warehouse_id_from'] = $model->getWarehouseIdTo();
            $transactionSendData['warehouse_name_from'] = $model->getWarehouseNameTo();
            $transactionSendData['warehouse_id_to'] = $model->getWarehouseIdFrom();
            $transactionSendData['warehouse_name_to'] = $model->getWarehouseNameFrom();
            $transactionSendData['created_at'] = $model->getCreatedAt();
            $transactionSendData['created_by'] = $model->getCreatedBy();
            $transactionSendData['reason'] = Mage::helper('inventorywarehouse')->__("Cancel Stock Sending No.'%s'", $id);
            $transactionSendData['total_products'] = $model->getTotalProducts();
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
            $transactionReceiveData['reason'] = Mage::helper('inventorywarehouse')->__("Cancel Stock Sending No.'%s'", $id);
            $transactionReceiveData['total_products'] = -$model->getTotalProducts();
            $transactionReceiveModel->addData($transactionReceiveData);
            $transactionReceiveModel->save();

            //recalculate qty
            $sendstockProducts = Mage::getModel('inventorywarehouse/sendstock_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_sendstock_id', $id);
            foreach ($sendstockProducts as $sendstockproduct) {
                $pId = $sendstockproduct->getProductId();
                $pSku = $sendstockproduct->getProductSku();
                $pName = $sendstockproduct->getProductName();
                //get qty of product using for transaction
                //qty is negative
                $qty = $sendstockproduct->getQty();
                //save products to transaction product table for send transaction
                Mage::getModel('inventorywarehouse/transaction_product')
                        ->setWarehouseTransactionId($transactionSendModel->getId())
                        ->setProductId($pId)
                        ->setProductSku($pSku)
                        ->setProductName($pName)
                        ->setQty($qty)
                        ->save()
                ;
                //save products to transaction product table for receive transaction
                Mage::getModel('inventorywarehouse/transaction_product')
                        ->setWarehouseTransactionId($transactionReceiveModel->getId())
                        ->setProductId($pId)
                        ->setProductSku($pSku)
                        ->setProductName($pName)
                        ->setQty(-$qty)
                        ->save()
                ;
                //recalculate product qty for warehouse send
                if ($send_warehouse != 0) {
                    $send_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                            ->getCollection()
                            ->addFieldToFilter('warehouse_id', $send_warehouse)
                            ->addFieldToFilter('product_id', $pId)
                            ->getFirstItem();
                    $newProductsQtySend = $send_warehouse_products->getTotalQty() + $qty;
                    $newProductsQtyAvaSend = $send_warehouse_products->getAvailableQty() + $qty;
                    if($newProductsQtyAvaSend == 0 && $newProductsQtySend == 0){
                        $send_warehouse_products->delete();
                    } else {
                        $send_warehouse_products
                            ->setTotalQty($newProductsQtySend)
                            ->setAvailableQty($newProductsQtyAvaSend)
                            ->save();
                    }                    
                } else {
                    //recalculate product qty for system if other
                    $stock_item = Mage::getModel('cataloginventory/stock_item')
                            ->getCollection()
                            ->addFieldToFilter('product_id', $pId)
                            ->getFirstItem();
                    $stock_item_qty = $stock_item->getQty();
                    $new_stock_qty = $stock_item_qty - $qty;
                    $stock_item->setQty($new_stock_qty)->save();
                }
                //recalculate product qty for warehouses receive
                $receive_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                        ->getCollection()
                        ->addFieldToFilter('warehouse_id', $receive_warehouse)
                        ->addFieldToFilter('product_id', $pId)
                        ->getFirstItem();
                if(!$receive_warehouse_products->getId()){
                    $receive_warehouse_products = Mage::getModel('inventoryplus/warehouse_product')
                            ->setData('product_id',$pId)
                            ->setData('warehouse_id',$receive_warehouse)
                            ->setData('total_qty',- $qty)
                            ->setData('available_qty',- $qty)
                            ->save();
                } else {
                    $newProductsQtyReceive = $receive_warehouse_products->getTotalQty() - $qty;
                    $newProductsQtyAvaReceive = $receive_warehouse_products->getAvailableQty() - $qty;
                    $receive_warehouse_products
                            ->setTotalQty($newProductsQtyReceive)
                            ->setAvailableQty($newProductsQtyAvaReceive)
                            ->save();
                }                
            }

            //send email to admin of receive warehouse
            if (Mage::getStoreConfig('inventoryplus/transaction/transaction_notice') == 1) {
                if ($receive_warehouse) {
                    $warehouseTarget = Mage::getModel('inventoryplus/warehouse')->load($receive_warehouse);
                    if ($warehouseTarget && !$warehouseTarget->getIsUnwarehouse()) {
                        $stockName = "Cancel send stock No." . $model->getId();
                        Mage::helper('inventorywarehouse/email')->sendSendstockEmail($warehouseTarget, $model->getId(), 1, $stockName);
                    }
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('inventorywarehouse')->__('Stock Sending was successfully canceled.')
            );
            if ($this->getRequest()->getParam('warehouse_id')) {
                $this->_redirect('adminhtml/inw_warehouse/edit', array('id' => $this->getRequest()->getParam('warehouse_id')));
            } else {
                $this->_redirect('adminhtml/inw_sendstock/index');
            }
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('adminhtml/inw_sendstock/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryplus')->__('Unable to cancel')
        );
        if ($this->getRequest()->getParam('warehouse_id')) {
            $this->_redirect('adminhtml/inw_warehouse/edit', array('id' => $this->getRequest()->getParam('warehouse_id')));
        } else {
            $this->_redirect('adminhtml/inw_sendstock/index');
        }
    }

    public function getImportCsvAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                Mage::getModel('admin/session')->setData('send_stock_reason',null);
                if($this->getRequest()->getParam('reason')){
                    Mage::getModel('admin/session')->setData('send_stock_reason',$this->getRequest()->getParam('reason'));
                }
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $sendstockProduct = array();
                $sendstockProducts = array();
                $fields = array();
                $count = 0;
                $helper = Mage::helper('inventorywarehouse/sendstock');
                if (count($dataFile)){
                    foreach ($dataFile as $col => $row) {
                        if ($col == 0) {
                            if (count($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {
                            if (count($row))
                                foreach ($row as $index => $cell) {

                                    if (isset($fields[$index])) {
                                        $sendstockProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $source = $this->getRequest()->getParam('source');
                            $productId = Mage::getModel('catalog/product')->getIdBySku($sendstockProduct['SKU']);
                            $warehouseproduct = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id', $source)
                                    ->addFieldToFilter('product_id', $productId);
                            if ($warehouseproduct->getSize()) {
                                $sendstockProducts[] = $sendstockProduct;
                            }
                        }
                    }
                }
                $helper->importProduct($sendstockProducts);
            } catch (Exception $e) {
                
            }
        }
    }

    public function exportCsvAction() {
        $fileName = 'send_stock.csv';
        $content = $this->getLayout()
                ->createBlock('inventorywarehouse/adminhtml_sendstock_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'send_stock.xml';
        $content = $this->getLayout()
                ->createBlock('inventorywarehouse/adminhtml_sendstock_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/stock_out/sendstock');
    } 
    
}