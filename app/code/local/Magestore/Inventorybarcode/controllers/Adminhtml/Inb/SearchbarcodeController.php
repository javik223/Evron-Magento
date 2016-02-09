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
class Magestore_Inventorybarcode_Adminhtml_Inb_SearchbarcodeController extends Magestore_Inventoryplus_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorybarcode_Adminhtml_Inb_SearchbarcodeController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/settings/barcode/search_barcode');
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Search Barcodes'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * search action
     */
    public function searchAction() {
        $items = array();

        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('barcode_query', '');
        $barcode = Mage::getModel('inventorybarcode/barcode')->load($query, 'barcode');
        if ($barcode->getId()) {
            $result = array();
            $result['barcode_id'] = $barcode->getId();
            $result['show'] = true;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $searchInstance = new Magestore_Inventorybarcode_Model_Search_Barcode();
            $results = $searchInstance->setStart($start)
                    ->setLimit($limit)
                    ->setQuery($query)
                    ->load()
                    ->getResults();
            $items = array_merge_recursive($items, $results);

            $totalCount = sizeof($items);


            $block = $this->getLayout()->createBlock('adminhtml/template')
                    ->setTemplate('inventorybarcode/search/autocomplete.phtml')
                    ->assign('items', $items);

            $this->getResponse()->setBody($block->toHtml());
        }
    }

    /**
     * showinformation action
     */
    public function showinformationAction() {
        $barcodeId = $this->getRequest()->getParam('barcode_id');
        $barcodeModel = Mage::getModel('inventorybarcode/barcode')->load($barcodeId);
        $information = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/search/information/information.phtml')
                ->assign('barcode', $barcodeModel);
        $barcode = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/search/information/barcode.phtml')
                ->assign('barcode', $barcodeModel);

        $productHtml = '';
        if ($barcodeModel->getProductEntityId()) {
            $productModel = Mage::getModel('catalog/product')->load($barcodeModel->getProductEntityId());


            $product = $this->getLayout()->createBlock('adminhtml/template')
                    ->setTemplate('inventorybarcode/search/information/product.phtml')
                    ->assign('qty', $barcodeModel->getQty())
                    ->assign('product', $productModel);

            $productHtml = $product->toHtml();
        }

        $warehouseHtml = '';

        if ($barcodeModel->getWarehouseWarehouseId()) {
            $warehouseIds = explode(',', $barcodeModel->getWarehouseWarehouseId());

            foreach ($warehouseIds as $key => $id) {
                $warehouseModel = Mage::getModel('inventoryplus/warehouse')->load($id);

                $warehouse = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/information/warehouse.phtml')
                        ->assign('warehouse', $warehouseModel);

                $warehouseHtml .= $warehouse->toHtml();
            }
        }

        $supplierHtml = '';
        $purchaseorderHtml = '';

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {

            if ($barcodeModel->getSupplierSupplierId()) {
                $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($barcodeModel->getSupplierSupplierId());


                $supplier = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/information/supplier.phtml')
                        ->assign('supplier', $supplierModel);

                $supplierHtml = $supplier->toHtml();
            }


            if ($barcodeModel->getPurchaseorderPurchaseOrderId()) {
                $purchaseorderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($barcodeModel->getPurchaseorderPurchaseOrderId());


                $purchaseorder = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/information/purchaseorder.phtml')
                        ->assign('purchaseorder', $purchaseorderModel);

                $purchaseorderHtml = $purchaseorder->toHtml();
            }
        }

        $result = array();

        $result['general'] = $information->toHtml();
        $result['barcode'] = $barcode->toHtml();
        $result['product'] = $productHtml;
        $result['warehouse'] = $warehouseHtml;
        $result['supplier'] = $supplierHtml;
        $result['purchaseorder'] = $purchaseorderHtml;
        $result['productid'] = $productModel->getId();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getdataforshipmentAction() {
        $barcodeId = $this->getRequest()->getParam('barcode_id');
        $orderId = $this->getRequest()->getParam('order_id');

        $barcodeModel = Mage::getModel('inventorybarcode/barcode')->load($barcodeId);

        if ($barcodeModel->getProductEntityId()) {
            $productModel = Mage::getModel('catalog/product')->load($barcodeModel->getProductEntityId());
        }

        $orderItemCollection = Mage::getModel("sales/order_item")->getCollection()
                ->addFieldToFilter('product_id', $productModel->getId())
                ->addFieldToFilter('order_id', $orderId);

        if (is_null($orderItemCollection->getFirstItem()->getParentItemId())) {
            $orderItemId = $orderItemCollection->getFirstItem()->getId();
        } else {
            $orderItemId = $orderItemCollection->getFirstItem()->getParentItemId();
        }
        $orderItemQty = $orderItemCollection->getFirstItem()->getQtyOrdered() * 1;

        if ($barcodeModel->getWarehouseWarehouseId()) {
            $warehouseId = $barcodeModel->getWarehouseWarehouseId();
            $warehouseIds = explode(',', $barcodeModel->getWarehouseWarehouseId());
            foreach ($warehouseIds as $key => $id) {
                $warehouseModel = Mage::getModel('inventoryplus/warehouse')->load($id);
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {

            if ($barcodeModel->getSupplierSupplierId()) {
                $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($barcodeModel->getSupplierSupplierId());
            }


            if ($barcodeModel->getPurchaseorderPurchaseOrderId()) {
                $purchaseorderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($barcodeModel->getPurchaseorderPurchaseOrderId());
            }
        }

        $totalQty = Mage::getModel("inventoryplus/warehouse_product")->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $productModel->getId())
                ->getFirstItem()
                ->getTotalQty();

        $selectWareHouseHtml = Mage::helper('inventoryplus/warehouse')->selectboxWarehouseShipmentByPidAndWarehouseId($productModel->getId(), $orderItemQty, $orderItemId, $orderId, $warehouseId);

        $result = array();

        $result['productid'] = $productModel->getId();
        $result['orderitemid'] = $orderItemId;
        $result['selectwarehousehtml'] = $selectWareHouseHtml;
        $result['orderitemqty'] = $orderItemQty;
        $result['totalqty'] = $totalQty;
        $result['barcode'] = $barcodeModel->getBarcode();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/settings/barcode/search_barcode');
    }    

}
