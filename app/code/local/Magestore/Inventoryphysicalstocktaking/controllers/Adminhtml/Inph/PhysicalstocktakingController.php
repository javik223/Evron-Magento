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
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Adminhtml_Inph_PhysicalstocktakingController extends Magestore_Inventoryplus_Controller_Action {

    protected function _initAction() {

        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/stock_onhand/physical_stock')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Physical Stocktaking'), Mage::helper('adminhtml')->__('Manage Physical Stocktaking')
        );
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Manage Physical Stocktaking'));
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
                $physicalstocktakingProduct = array();
                $physicalstocktakingProducts = array();
                $fields = array();
                $count = 0;
                $physicalstocktakingHelper = Mage::helper('inventoryphysicalstocktaking');
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
                                        $physicalstocktakingProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $physicalstocktakingProducts[] = $physicalstocktakingProduct;
                        }
                    }
                $physicalstocktakingHelper->importProduct($physicalstocktakingProducts);
            } catch (Exception $e) {
                
            }
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventory_listphysicalstocktaking_grid');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Physical Stocktaking'));
        if (!Mage::helper('inventoryplus')->isWarehouseEnabled()) {
            $this->_redirect('*/*/prepare');
        } else {
            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/physical_stock');
            $this->renderLayout();
        }
    }

    public function editAction() {

        $adjustStockId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking')->load($adjustStockId);

        if (!$adjustStockId) {
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Add New Physical Stocktaking'));
        } else {
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Edit Physical Stocktaking'));
        }

        if ($model->getId() || $adjustStockId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('physicalstocktaking_data', $model);

            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/physical_stock');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
            $this->_addContent($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryphysicalstocktaking')->__('Adjust Stock does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function prepareAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Physical Stocktaking'));
        if ($data = $this->getRequest()->getPost() || !Mage::helper('inventoryplus')->isWarehouseEnabled() || $this->getRequest()->getParam('warehouse_id')) {
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
                        $adjustStockProduct = array();
                        $adjustStockProducts = array();
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
                            $adjustStockProducts['warehouse_id'] = $this->getRequest()->getPost('warehouse_id');
                            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', $adjustStockProducts);
                            $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/physical_stock');

                            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                                    ->removeItem('js', 'mage/adminhtml/grid.js')
                                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
                            $this->_addContent($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit')->setPhysicalstocktakingProducts($adjustStockProducts))
                                    ->_addLeft($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit_tabs')->setPhysicalstocktakingProducts($adjustStockProducts));
                            $this->renderLayout();
                        } else {
                            Mage::getSingleton('adminhtml/session')->addError(
                                    Mage::helper('inventoryphysicalstocktaking')->__('Unable to find item to save')
                            );
                            $this->_redirect('*/*/new');
                        }
                    } catch (Exception $e) {
                        
                    }
                } catch (Exception $e) {
                    
                }
            } else {
                if ($this->getRequest()->getPost('warehouse_id'))
                    $adjustStockProducts['warehouse_id'] = $this->getRequest()->getPost('warehouse_id');

                if (!Mage::helper('inventoryplus')->isWarehouseEnabled()) {
                    $adjustStockProducts['warehouse_id'] = Mage::getModel('inventoryplus/warehouse')->getCollection()
                                    ->getFirstItem()->getId();
                    $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
                    $canPhysicalAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                            ->addFieldToFilter('warehouse_id', $adjustStockProducts['warehouse_id'])
                            ->addFieldToFilter('admin_id', $adminId)
                            ->addFieldToFilter('can_physical', 1);
                    if (!$canPhysicalAdmins->getSize()) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryphysicalstocktaking')->__('You have not permission to physical stocktaking!'));
                        session_write_close();
                        $this->_redirect('*/*/');
                    }
                } else {

                    if (!$this->getRequest()->getPost('warehouse_id') && $this->getRequest()->getParam('warehouse_id'))
                        $adjustStockProducts['warehouse_id'] = $this->getRequest()->getParam('warehouse_id');
                }

                Mage::getModel('admin/session')->setData('physicalstocktaking_product_warehouse', $adjustStockProducts);
                $this->loadLayout()->_setActiveMenu('inventoryplus/stock_onhand/physical_stock');

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                        ->removeItem('js', 'mage/adminhtml/grid.js')
                        ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
                $this->_addContent($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit')->setPhysicalstocktakingProducts($adjustStockProducts))
                        ->_addLeft($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit_tabs')->setPhysicalstocktakingProducts($adjustStockProducts));
                $this->renderLayout();
            }
        }else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryphysicalstocktaking')->__('Unable to find item to save')
            );
            $this->_redirect('*/*/new');
        }
    }

    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryphysicalstocktaking.physicalstocktaking.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('physicalstocktaking_products', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('physicalstocktaking_product_import')) {
            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', null);
        }
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryphysicalstocktaking.physicalstocktaking.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('physicalstocktaking_products', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('physicalstocktaking_product_import')) {
            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', null);
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $warehouse_id = $data['warehouse_id'];
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouse_id);
            try {
                if (!isset($data['physicalstocktaking_products']) || empty($data['physicalstocktaking_products'])) {
                    Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('inventoryphysicalstocktaking')->__('Cannot save physical stocktaking with no product')
                    );
                    if (!$this->getRequest()->getParam('id')) {
                        $this->_redirect('adminhtml/inph_physicalstocktaking/new');
                        return;
                    } else {
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
                $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                $data['created_by'] = $admin;
                $model = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking');
                $model->addData($data);
                //create new
                if ($this->getRequest()->getParam('id')) {
                    $model = $model->load($this->getRequest()->getParam('id'));
                    $model->setData('physicalstocktaking_products', $data['physicalstocktaking_products']);
                    $model->setData('reason', $data['reason']);
                } else {
                    $model->create($data, $warehouse);
                }
                //cancel
                if ($this->getRequest()->getParam('cancel')) {
                    $model->cancel();
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('inventoryplus')->__('The physical stocktaking has been successfully canceled.')
                    );
                    return $this->_redirect('*/*/');
                }
                /* update products in physical stock-taking */
                $model->saveProducts();

                if ($this->getRequest()->getParam('confirm') || $this->getRequest()->getParam('confirmadjust')) {
                    /* confirm */
                    $model->confirm($data);
                    $adjustModel = $model->confirmAdjust();
                    /* do adjust */
                    if ($this->getRequest()->getParam('confirmadjust')) {
                        $adjustModel->doAdjust($data);
                        /* continue do adjust stock? */
                        if ($adjustModel->getStatus() == Magestore_Inventoryplus_Model_Adjuststock::STATUS_PROCESSING) {
                            return $this->_redirect('adminhtml/inp_adjuststock/run', array('id' => $adjustModel->getId(), 'resum' => 1));
                        }
                        $url = Mage::helper('adminhtml')->getUrl('adminhtml/inp_adjuststock/edit', array('id' => $adjustModel->getId()));
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                                Mage::helper('inventoryphysicalstocktaking')->__('The physical stocktaking has been confirmed.')
                        );
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                                Mage::helper('inventoryphysicalstocktaking')->__('A stock adjustment (Id %s) has already created and been completed automatically. You can <a href="%s"/>Click here</a> to view stock adjustment.', $adjustModel->getId(), $url)
                        );
                    } else {
                        $url = Mage::helper('adminhtml')->getUrl('adminhtml/inp_adjuststock/edit', array('id' => $adjustModel->getId()));
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                                Mage::helper('inventoryphysicalstocktaking')->__('The physical stocktaking has been confirmed. A pending stock adjustment (Id %s) has been successfully created. Now you can <a href="%s"/>Click here</a> to update stock of the warehouse.', $adjustModel->getId(), $url)
                        );
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('inventoryphysicalstocktaking')->__('The physical stocktaking has been saved successfully.')
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
                Mage::helper('inventoryphysicalstocktaking')->__('Unable to find physical stocktaking to save.')
        );
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/stock_onhand/physical_stock');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'physicalstocktaking.csv';
        $content = $this->getLayout()
                ->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_listphysicalstocktaking_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'physicalstocktaking.xml';
        $content = $this->getLayout()
                ->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_listphysicalstocktaking_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

}
