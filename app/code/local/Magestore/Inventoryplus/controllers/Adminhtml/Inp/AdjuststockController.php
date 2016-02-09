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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Adminhtml_Inp_AdjuststockController extends Magestore_Inventoryplus_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/stock_onhand/adjust_stock')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Adjust stock Manager'), Mage::helper('adminhtml')->__('Adjust stock Manager')
        );
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Manage Adjust Stocks'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function importproductAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $adjuststockProduct = array();
                $adjuststockProducts = array();
                $fields = array();
                $count = 0;
                $adjuststockHelper = Mage::helper('inventoryplus/adjuststock');
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
                                        $adjuststockProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $adjuststockProducts[] = $adjuststockProduct;
                        }
                    }
                $adjuststockHelper->importProduct($adjuststockProducts);
            } catch (Exception $e) {
                
            }
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventory_listadjuststock_grid');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Adjust Stock'));
        if (!Mage::helper('inventoryplus')->isWarehouseEnabled()) {
            $this->_redirect('*/*/prepare');
        } else {
            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/adjust_stock');
            $this->renderLayout();
        }
    }

    public function editAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Edit New Adjust Stock'));

        $adjustStockId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventoryplus/adjuststock')->load($adjustStockId);
        if ($model->getId() || $adjustStockId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('adjuststock_data', $model);

            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/adjust_stock');
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
            $this->_addContent($this->getLayout()->createBlock('inventoryplus/adminhtml_adjuststock_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventoryplus/adminhtml_adjuststock_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryplus')->__('Adjust Stock does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function prepareAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Adjust Stock'));

        if ($data = $this->getRequest()->getPost() || !Mage::helper('inventoryplus')->isWarehouseEnabled() || $this->getRequest()->getParam('warehouse_id')) {
            $adjustStockProduct = array();
            $adjustStockProducts = array();
            if ($this->getRequest()->getPost('warehouse_id'))
                $adjustStockProducts['warehouse_id'] = $this->getRequest()->getPost('warehouse_id');

            if (!Mage::helper('inventoryplus')->isWarehouseEnabled()) {
                $adjustStockProducts['warehouse_id'] = Mage::getModel('inventoryplus/warehouse')->getCollection()
                                ->getFirstItem()->getId();

                $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
                $canPhysicalAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                        ->addFieldToFilter('warehouse_id', $adjustStockProducts['warehouse_id'])
                        ->addFieldToFilter('admin_id', $adminId)
                        ->addFieldToFilter('can_adjust', 1);
                if (!$canPhysicalAdmins->getSize()) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('You have not permission to adjust stock!'));
                    session_write_close();
                    $this->_redirect('*/*/');
                }
            } else {

                if (!$this->getRequest()->getPost('warehouse_id') && $this->getRequest()->getParam('warehouse_id'))
                    $adjustStockProducts['warehouse_id'] = $this->getRequest()->getParam('warehouse_id');
            }

            if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {

                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('fileToUpload');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('csv'));
                    $uploader->setAllowRenameFiles(false);

                    $uploader->setFilesDispersion(false);

                    try {
                        $fileName = $_FILES['fileToUpload']['tmp_name'];
                        $Object = new Varien_File_Csv();
                        $dataFile = $Object->getData($fileName);

                        $fields = array();
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
                                                $adjustStockProduct[$fields[$index]] = $cell;
                                            }
                                        }
                                    $adjustStockProducts[] = $adjustStockProduct;
                                }
                            }
                        if (count($adjustStockProducts)) {

                            Mage::getModel('admin/session')->setData('adjuststock_product_import', $adjustStockProducts);
                            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/adjust_stock');
                            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                                    ->removeItem('js', 'mage/adminhtml/grid.js')
                                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
                            $this->_addContent($this->getLayout()->createBlock('inventoryplus/adminhtml_adjuststock_edit')->setAdjustStockProducts($adjustStockProducts))
                                    ->_addLeft($this->getLayout()->createBlock('inventoryplus/adminhtml_adjuststock_edit_tabs')->setAdjustStockProducts($adjustStockProducts));
                            $this->renderLayout();
                        } else {
                            Mage::getSingleton('adminhtml/session')->addError(
                                    Mage::helper('inventoryplus')->__('Unable to find item to save')
                            );
                            $this->_redirect('*/*/new');
                        }
                    } catch (Exception $e) {
                        
                    }
                } catch (Exception $e) {
                    
                }
            } else {

                Mage::getModel('admin/session')->setData('adjuststock_product_warehouse', $adjustStockProducts);
                $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/adjust_stock');
                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                        ->removeItem('js', 'mage/adminhtml/grid.js')
                        ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
                $this->_addContent($this->getLayout()->createBlock('inventoryplus/adminhtml_adjuststock_edit')->setAdjustStockProducts($adjustStockProducts))
                        ->_addLeft($this->getLayout()->createBlock('inventoryplus/adminhtml_adjuststock_edit_tabs')->setAdjustStockProducts($adjustStockProducts))
                        ;
                $this->renderLayout();
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryplus')->__('Unable to find item to save')
            );
            $this->_redirect('*/*/index');
        }
    }

    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryplus.adjuststock.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('adjuststock_products', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('adjuststock_product_import')) {
            Mage::getModel('admin/session')->setData('adjuststock_product_import', null);
        }
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryplus.adjuststock.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('adjuststock_products', null));
        $this->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('inventoryplus/adjuststock');
            $admin = Mage::getModel('admin/session')->getUser()->getUsername();
            $data['created_by'] = $admin;
            $model->addData($data);
            $warehouse_id = $data['warehouse_id'];
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouse_id);
            $model->setWarehouse($warehouse);
            try {
                if ($this->getRequest()->getParam('id')) {
                    $model->setId($this->getRequest()->getParam('id'));
                    /* Cancel an adjuststock */
                    if ($this->getRequest()->getParam('cancel')) {
                        Mage::helper('inventoryplus/adjuststock')->cancelAdjuststock($model);
                        return $this->_redirect('*/*/');
                    }
                }
                /* validate post adjust stock data */
                if (!isset($data['adjuststock_products']) || empty($data['adjuststock_products'])) {
                    Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('inventoryplus')->__('Can\'t save stock adjustment without product')
                    );
                    if (!$this->getRequest()->getParam('id')) {
                        return $this->_redirect('adminhtml/inp_adjuststock/new');
                    } elseif ($this->getRequest()->getParam('back')) {
                        return $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    }
                    return $this->_redirect('*/*/');                 
                }
                /* create a new adjuststock */
                if (!$this->getRequest()->getParam('id')) {
                    $model->create($data, $warehouse);
                } else {
                    $model->setData('stock_data', $data['adjuststock_products']);
                }
                /* update products in adjust stock */
                $model->saveProducts();
                /* do adjust */
                if($this->getRequest()->getParam('confirm')){
                    $model->doAdjust($data);
                }
                /* continue do adjust stock? */
                if($model->getStatus() == Magestore_Inventoryplus_Model_Adjuststock::STATUS_PROCESSING){
                    return $this->_redirect('*/*/run', array('id' => $model->getId(), 'resum' => 1));
                }
                /* prepare messages */
                if ($this->getRequest()->getParam('confirm')) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('inventoryplus')->__('The stock adjustment has been confirmed successfully.')
                    );
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('inventoryplus')->__('The stock adjustment has been saved successfully.')
                    );
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }
                return $this->_redirect('*/*/');                    
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryplus')->__('Unable to find adjust stock to create')
        );
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/stock_onhand/adjust_stock');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'adjustment.csv';
        $content = $this->getLayout()
                ->createBlock('inventoryplus/adminhtml_adjuststock_listadjuststock_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'adjustment.xml';
        $content = $this->getLayout()
                ->createBlock('inventoryplus/adminhtml_adjuststock_listadjuststock_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Run process in popup
     * 
     */
    public function runAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Do steps in process
     * 
     */
    public function doProcessAction() {
        $type = $this->getDataType();
        try {
            $adjustId = $this->getRequest()->getParam('id');
            $adjustStock = Mage::getModel('inventoryplus/adjuststock')->load($adjustId);
            $arr = array();
            $adjustStock->doAdjust($arr);
            $remain = count($adjustStock->getNeedUpdateStocks());
            if (strcmp($type, 'Finished') != 0) {
                $response = array('count' => $count,
                    'remain' => $remain,
                    'msgs' => array(
                        'finish' => $this->helper()->__('Finished %s process.', $type))
                );
            }
        } catch (Exception $e) {
            $response = array('error'=>1,
                'msgs' => array('error' => $e->getMessage())
                );
        }
        return $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Get process data list
     * 
     */
    public function processDataListAction() {
        $list = array('Stock Adjusment');
        $this->_resetDataCount($list);
        return $this->getResponse()->setBody(json_encode(array('list' => $list)));
    }

    /**
     * Get total data items
     * 
     */
    public function countDataAction() {
        $adjustId = $this->getRequest()->getParam('id');
        $type = $this->getDataType();
        $adjustStock = Mage::getModel('inventoryplus/adjuststock')->load($adjustId);
        $total = count($adjustStock->getNeedUpdateStocks());
        $response = array('total' => $total,
            'msgs' => array('start' => $this->helper()->__('Starting %s.', $type),
                'total' => $this->helper()->__('Found %s record(s).', $total),
                'finish' => $this->helper()->__('Finished %s process.', $type))
        );
        return $this->getResponse()->setBody(json_encode($response));
    }

    public function helper() {
        return Mage::helper('inventoryplus');
    }

    public function getDataType() {
        return $this->getRequest()->getPost('type');
    }

    public function getTypeKey($type) {
        return str_replace(' ', '_', $type);
    }

    protected function _resetDataCount($types) {
        foreach ($types as $type) {
            Mage::getSingleton('adminhtml/session')->unsetData('IMProcess_count' . $this->getTypeKey($type));
        }
    }

}
