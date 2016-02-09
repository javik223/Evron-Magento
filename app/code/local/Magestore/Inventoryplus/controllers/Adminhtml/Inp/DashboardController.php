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
class Magestore_Inventoryplus_Adminhtml_Inp_DashboardController extends Magestore_Inventoryplus_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventory_Adminhtml_InventoryController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/dashboard')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Dashboard'), Mage::helper('adminhtml')->__('Dashboard')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $check = Mage::helper('inventoryplus')->isDataInstalled();
        if ($check == 0) {
            $installModel = Mage::getModel('inventoryplus/install')->getCollection()->getFirstItem();
            try {
                if (!Mage::helper('inventoryplus/install')->isWarehouseExist()) {
                    // Create warehouse default
                    Mage::helper('inventoryplus/install')->createDefaultWarehouse();
                    // Insert admins permission for Default warehouse
                    Mage::helper('inventoryplus/install')->addDefaultWarehousePermission();
                }

                Mage::dispatchEvent('inventoryplus_is_installing');

                if ($installModel->getStatus() == Magestore_Inventoryplus_Model_Install::STATUS_PROCESSING) {
                    return $this->_redirect('*/*/run', array('resume' => 1));
                }
            } catch (Exception $e) {
                throw $e;
            }
        }
        $this->_initAction();
        $this->_title($this->__('Dashboard'));
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/dashboard');
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
        $installModel = Mage::getModel('inventoryplus/install')
                ->getCollection()
                ->getFirstItem();
        $type = $this->getDataType();
        try {
            $installFlag = $installModel->getInstallFlag();
            if ($installFlag == 0) {
                $isInstalling = 0;
            } else {
                $isInstalling = 1;
            }
            if ($isInstalling == 1) {
                switch ($type) {
                    case 'Import Product':
                        $remain = count($installModel->getNeedImportProducts());
                        break;
                    case 'Import Shipping Progress':
                        $remain = count($installModel->getNeedImportShippingProgress());
                        break;
                    case 'Finished':
                        $remain = 0;
                        break;
                    default:
                        $remain = 0;
                        break;
                }
                if (strcmp($type, 'Finished') != 0) {
                    $response = array('count' => 1,
                        'remain' => $remain,
                        'msgs' => array(
                            'finish' => $this->helper()->__('Finished %s process.', $type))
                    );
                }
            } else {
                $installModel->setInstallFlag(1);
                try {
                    $installModel->save();
                } catch (Exception $ex) {
                    Mage::log('setFlag = 1 false');
                }
                $installModel->doInstall($type);
                switch ($type) {
                    case 'Import Product':
                        $remain = count($installModel->getNeedImportProducts());
                        break;
                    case 'Import Shipping Progress':
                        $remain = count($installModel->getNeedImportShippingProgress());
                        break;
                    case 'Finished':
                        $remain = 0;
                        break;
                    default:
                        $remain = 0;
                        break;
                }
                if (strcmp($type, 'Finished') != 0) {
                    $response = array('count' => 1,
                        'remain' => $remain,
                        'msgs' => array(
                            'finish' => $this->helper()->__('Finished %s process.', $type))
                    );
                }
                $installModel->setInstallFlag(0);
                try {
                    $installModel->save();
                } catch (Exception $ex) {
                    Mage::log('setFlag = 0 false');
                }
            }
        } catch (Exception $e) {
            $response = array('error' => 1,
                'msgs' => array('error' => $this->__('Something wrong! We\'ll try again.'))
            );
        }
        return $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Get process data list
     * 
     */
    public function processDataListAction() {
        $list = array('Import Product', 'Import Shipping Progress');
        $this->_resetDataCount($list);
        return $this->getResponse()->setBody(json_encode(array('list' => $list)));
    }

    /**
     * Get total data items
     * 
     */
    public function countDataAction() {
        $type = $this->getDataType();
        $installModel = Mage::getModel('inventoryplus/install')
            ->getCollection()
            ->getFirstItem();
        switch ($type) {
            case 'Import Product':
                $total = count($installModel->getNeedImportProducts());
                break;
            case 'Import Shipping Progress':
                $total = count($installModel->getNeedImportShippingProgress());
                break;
            default:
                $total = 0;
                break;
        }
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
