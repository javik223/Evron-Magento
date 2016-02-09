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
 * Inventoryreports Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Adminhtml_Inr_SupplierController extends Magestore_Inventoryplus_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventoryreports_Adminhtml_InventoryreportsController
     */
    protected function productAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Reports'))
                ->_title($this->__('Report Time Inventory'));
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus')
                ->renderLayout();
    }

    public function productgridAction() {
        $this->loadLayout();
//        $this->getLayout()->getBlock('inventory_listadjuststock_grid');
        $this->renderLayout();
    }

    //show time delivery by supplier
    public function timedeliverybysupplierAction() {
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/supplier/product/timedeliverybysupplier.phtml')->toHtml();
    }

    //show time delivery by warehouse
    public function timedeliverybywarehouseAction() {
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/supplier/product/timedeliverybywarehouse.phtml')->toHtml();
    }

    //show time delivery by product
    public function timedeliverybyproductAction() {
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/supplier/product/timedeliverybyproduct.phtml')->toHtml();
    }

    //inventory reports by supplier
    public function inventorybysupplierAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Reports'))
                ->_title($this->__('Inventory Reports by Supplier'));
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus')
                ->renderLayout();
    }

    public function inventorybysuppliergridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function purchaseordersAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function purchaseordersGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'purchaseorders.csv';
        $grid = $this->getLayout()->createBlock('inventoryreports/adminhtml_supplier_purchaseorder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName = 'purchaseorders.xml';
        $grid = $this->getLayout()->createBlock('inventoryreports/adminhtml_product_purchaseorder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/report');
    }    

}
