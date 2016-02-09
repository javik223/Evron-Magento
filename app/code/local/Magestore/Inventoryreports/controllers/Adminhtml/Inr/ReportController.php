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
class Magestore_Inventoryreports_Adminhtml_Inr_ReportController extends Magestore_Inventoryplus_Controller_Action {

    public function _initAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Reports'));
        switch ($this->getRequest()->getParam('type_id')) {
            case 'sales':
                $this->_title($this->__('Sales Reports'));
                break;
            case 'warehouse':
                $this->_title($this->__('Warehouse Reports'));
                break;
            case 'product':
                $this->_title($this->__('Product Reports'));
                break;
            case 'supplier':
                $this->_title($this->__('Supplier Reports'));
                break;
            case 'stockmovement':
                $this->_title($this->__('Stock Movement Reports'));
                break;
            case 'customer':
                $this->_title($this->__('Customer Reports'));
                break;
            case 'stockonhand':
                $this->_title($this->__('Stock On-Hand Reports'));
                break;
        }
        $this->loadLayout();
        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function dashboardAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function salesAction() {
        $this->getRequest()->setParam('type_id', 'sales');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'report_radio_select=sales_days&group_select=0&select_time=last_30_days&order_status[]=complete';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/sales_report')
                ->renderLayout();
    }

    public function productAction() {
        $this->getRequest()->setParam('type_id', 'product');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'report_radio_select=best_seller&select_time=last_30_days';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }          
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/product_report')
                ->renderLayout();
    }
    
    public function stockonhandAction() {
        $this->getRequest()->setParam('type_id','stockonhand');
        if(!$this->getRequest()->getParam('top_filter')){
            $top_filter = 'report_radio_select=most_stock_remain&select_time=last_30_days';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/stockonhand_report')
                ->renderLayout();
    }

    public function warehouseAction() {
        $this->getRequest()->setParam('type_id', 'warehouse');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'report_radio_select=sales_by_warehouse_revenue&select_time=last_30_days';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->renderLayout();
    }

    public function supplierAction() {
        $this->getRequest()->setParam('type_id', 'supplier');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'report_radio_select=purchase_order_to_supplier&select_time=last_30_days';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/supplier_report')
                ->renderLayout();
    }

    public function stockmovementAction() {
        $this->getRequest()->setParam('type_id', 'stockmovement');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'report_radio_select=stock_in&select_time=last_30_days';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/stockmovement_report')
                ->renderLayout();
    }

    public function customerAction() {
        $this->getRequest()->setParam('type_id', 'customer');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'select_time=last_30_days&report_radio_select=customer';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/customer_report')
                ->renderLayout();
    }
    
    public function purchaseorderAction() {
        $this->getRequest()->setParam('type_id', 'purchaseorder');
        if (!$this->getRequest()->getParam('top_filter')) {
            $top_filter = 'report_radio_select=po_supplier&select_time=last_30_days';
            $top_filter = base64_encode($top_filter);
            $this->getRequest()->setParam('top_filter', $top_filter);
        }
        $this->_initAction()
                ->_setActiveMenu('inventoryplus/report/purchase_order')
                ->renderLayout();
    }    

    public function reportordergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function reportmovementgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function reportcustomergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function reportinvoicegridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function reportcreditmemogridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function inventorybysuppliergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalqtyadjuststockgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function numberofproductadjuststockgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalorderbywarehousegridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function salesbywarehouserevenuegridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function salesbywarehouseitemshippedgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalstocktransfersendstockgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalstocktransferrequeststockgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function supplyneedsbywarehouseproductsgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalstockdifferentwhenphysicalstocktakingbywarehousegridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function bestsellergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function moststockremaingridAction() {
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function stockonhandmoststockremaingridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function warehousingtimelongestgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function purchaseordergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }  
    
    public function purchaseorderlistAction() {
        $this->loadLayout()
                ->renderLayout();
    }      
    
    public function timedeliverybysupplierAction() {
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/content/grid/product/grid/product/timedeliverybysupplier.phtml')->toHtml();
    }

    public function timedeliverybywarehouseAction() {
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/content/grid/product/grid/product/timedeliverybywarehouse.phtml')->toHtml();
    }

    public function timedeliverybyproductAction() {
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/content/grid/product/grid/product/timedeliverybyproduct.phtml')->toHtml();
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $report = $this->getRequest()->getParam('report');
        $type = $this->getRequest()->getParam('type_id');
        $dataBlock = null;
        $fileName = '';
        switch ($report) {
            case 'best_seller':
                $dataBlock = 'inventoryreports/adminhtml_reportcontent_reportbyproduct_grid_bestseller';
                break;
            case 'customer':
                $dataBlock = 'inventoryreports/adminhtml_reportcontent_reportbycustomer_grid';
                $fileName = 'inventoryreports_customers.csv';
                break;
            case 'po_supplier':
            case 'po_warehouse':
            case 'po_sku':
                $dataBlock = 'inventoryreports/adminhtml_reportcontent_purchaseorder_grid';
                break;
        }

        if ($type == 'sales') {
            $dataBlock = 'inventoryreports/adminhtml_reportcontent_reportbyorder_grid';
        }
        if ($type == 'stockonhand') {
            $dataBlock = 'inventoryreports/adminhtml_reportcontent_reportbystockonhand_grid_moststockremain';
             $report = 'stock-onhand';
        }        
        if (!$dataBlock) {
            return null;
        }

        $fileName = $fileName ? $fileName : 'inventoryreports_' . $type . '_' . $report . '.csv';
        $content = $this->getLayout()
                ->createBlock($dataBlock)
                ->getCsv();
        
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/report');
    }

}
