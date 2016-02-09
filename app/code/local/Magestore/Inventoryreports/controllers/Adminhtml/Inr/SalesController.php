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
class Magestore_Inventoryreports_Adminhtml_Inr_SalesController extends Magestore_Inventoryplus_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventoryreports_Adminhtml_InventoryreportsController
     */
    public function warehouseAction()
    {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Reports'))
             ->_title($this->__('Sales reports by Warehouse'));
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->renderLayout();
        return $this;
    }
    
    public function warehousegridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function historyAction()
    {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Reports'))
             ->_title($this->__('Sales history reports'));
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->renderLayout();
        return $this;
    }
    
    
    public function historygridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function ordersAction(){
        $this->loadLayout();
        $this->renderLayout();

    }
    public function ordersGridAction(){
        $this->loadLayout();
        $this->renderLayout();

    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('inventoryreports/adminhtml_list_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('inventoryreports/adminhtml_list_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/report');
    }    

}