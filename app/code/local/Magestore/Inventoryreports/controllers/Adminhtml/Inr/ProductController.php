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
 * @package     Magestore_Inventoryreports
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
class Magestore_Inventoryreports_Adminhtml_Inr_ProductController extends Magestore_Inventoryplus_Controller_Action
{
    /**
     * Render product chart (sales order, purchase order, on-hand qty)
     */
    public function chartAction(){
        $this->loadLayout();
        $output = $this->getLayout()->getOutput();
        $this->getResponse()->setBody($output);
    }
    
    protected function bestsellersAction()
    {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Reports'))
             ->_title($this->__('Product Bestsellers'));
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->renderLayout();        
    }
    
    public function bestsellersgridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function detailsAction(){
        $this->loadLayout();
        $this->renderLayout();

    }
    public function detailsGridAction(){
        $this->loadLayout();
        $this->renderLayout();

    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'products.csv';
        $grid       = $this->getLayout()->createBlock('inventoryreports/adminhtml_list_product_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'products.xml';
        $grid       = $this->getLayout()->createBlock('inventoryreports/adminhtml_list_product_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/report');
    }    
}