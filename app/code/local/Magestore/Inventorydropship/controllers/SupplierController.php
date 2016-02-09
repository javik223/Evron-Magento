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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */

class Magestore_Inventorydropship_SupplierController extends Mage_Core_Controller_Front_Action {

    protected function _getSession() {
        return Mage::getSingleton('inventorydropship/session');
    }

    public function indexAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('inventorydropship/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit Supplier Information'));
        $this->renderLayout();
    }

    public function loginAction() {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('inventorydropship/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Supplier Log In'));
        $this->renderLayout();
    }

    public function loginpostAction() {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();
        if ($data = $this->getRequest()->getPost()) {
            if ($session->login($data['username'], $data['password'])) {
                $session->addSuccess($this->__('You have successfully logged in!'));
                $this->_redirect('*/*/dropship');
                return;
            }
        }
        $session->addError($this->__('Invalid login or password.'));
        $this->_redirect('*/*/login');
        return;
    }

    public function logoutAction() {
        $session = $this->_getSession();
        $session->setSupplier(null);
        $session->addSuccess($this->__('You have successfully logged out!'));
        $this->_redirect('*/*/login');
        return;
    }

    public function forgotpasswordAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('inventorydropship/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Supplier Forgot Password'));
        $this->renderLayout();
    }

    public function forgotpasswordpostAction() {
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            $supplier = Mage::getModel('inventorypurchasing/supplier')
                    ->getCollection()
                    ->addFieldToFilter('supplier_email', $email)
                    ->getFirstItem();

            if ($supplier->getId()) {
                try {
                    $newPassword = Mage::getModel('customer/customer')->generatePassword();
                    $newPasswordHash = md5($newPassword);
                    $supplier->setPasswordHash($newPasswordHash)->save();
                    Mage::helper('inventorydropship')->sendPasswordResetConfirmationEmail($supplier, $newPassword);
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            }
            $this->_getSession()
                    ->addSuccess(Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to get your new password.', Mage::helper('inventorydropship')->htmlEscape($email)));
            $this->_redirect('*/*/');
            return;
        } else {
            $this->_getSession()->addError($this->__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }

    public function editpostAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }
        $session = $this->_getSession();
        if ($data = $this->getRequest()->getPost()) {
            try {
                if (isset($data['state_id'])) {
                    $data['state'] = $data['state_id'];
                }
                if($data['name']){
                    $data['supplier_name'] = $data['name'];
                }
                $model = Mage::getModel('inventorypurchasing/supplier')->load($session->getSupplier()->getId());
                $data['created_by'] = $model->getData('created_by');
                $model->addData($data);

                $oldData = Mage::getModel('inventorypurchasing/supplier')->load($session->getSupplier()->getId());
                $changeArray = array();
                $changeData = 0;
                foreach ($data as $key => $value) {
                    if (!in_array($key, $this->getFiledSaveHistory()))
                        continue;
                    if ($oldData->getData($key) != $value) {
                        $changeArray[$key]['old'] = $oldData->getData($key);
                        $changeArray[$key]['new'] = $value;
                        $changeData = 1;
                    }
                }
                if (isset($data['change_password'])) {
                    $currentPassword = $data['current_password'];
                    if (md5($currentPassword) == $model->getPasswordHash()) {
                        $data['password_hash'] = md5($data['password']);
                    } else {
                        $this->_getSession()->addError($this->__('You cannot change your password because Invalid your current password!'));
                    }
                }
                $model->addData($data);
                $model->save();

                if ($changeData == 1) {
                    $supplierHistory = Mage::getModel('inventorypurchasing/supplier_history');
                    $supplierHistory->setData('supplier_id', $model->getId())
                            ->setData('time_stamp', now())
                            ->setData('created_by', $this->__('Supplier: ') . $model->getContactName())
                            ->save();
                    $supplierHistoryId = $supplierHistory->getId();
                    if ($changeData == 1) {
                        foreach ($changeArray as $field => $filedValue) {
                            $fileTitle = $this->getTitleByField($field);
                            if ($field == 'status') {
                                $statusArray = Mage::getSingleton('inventoryplus/status')->getOptionHash();
                                $filedValue['old'] = $statusArray[$filedValue['old']];
                                $filedValue['new'] = $statusArray[$filedValue['new']];
                            } elseif ($field == 'country_id') {
                                $countryArray = array();
                                $countryArrays = Mage::helper('inventoryplus')->getCountryListHash();
                                foreach ($countryArrays as $country) {
                                    $countryArray[$country['value']] = $country['label'];
                                }
                                $filedValue['old'] = $countryArray[$filedValue['old']];
                                $filedValue['new'] = $countryArray[$filedValue['new']];
                            } elseif ($field == 'state') {
                                $oldRegion = Mage::getModel('directory/region')->load($filedValue['old']);
                                $oldRegionName = $oldRegion->getName();
                                if (!$oldRegionName || $oldRegionName == '') {
                                    $oldRegionName = $filedValue['old'];
                                }
                                $newRegion = Mage::getModel('directory/region')->load($filedValue['new']);
                                $newRegionName = $newRegion->getName();
                                if (!$newRegionName || $newRegionName == '') {
                                    $newRegionName = $filedValue['new'];
                                }
                                $filedValue['old'] = $oldRegionName;
                                $filedValue['new'] = $newRegionName;
                            }

                            $supplierHistoryContent = Mage::getModel('inventorypurchasing/supplier_historycontent');
                            $supplierHistoryContent->setData('supplier_history_id', $supplierHistoryId)
                                    ->setData('field_name', $fileTitle)
                                    ->setData('old_value', $filedValue['old'])
                                    ->setData('new_value', $filedValue['new'])
                                    ->save();
                        }
                    }
                }
                $this->_getSession()->addSuccess($this->__('Supplier was successfully saved!'));
                Mage::getModel('inventorydropship/session')->setSupplier($model);
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
        }
    }

    public function dropshipAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }
        $this->loadLayout();
        $listDropshipBlock = $this->getLayout()->getBlock('supplier_dropship');
        $listDropship = $listDropshipBlock->listDropships();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'inventorydropship.supplier.dropship.pager')
                ->setCollection($listDropship);
        $listDropshipBlock->setChild('pager', $pager);
        $this->_initLayoutMessages('inventorydropship/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Supplier Drop Ship'));
        $this->renderLayout();
    }

    public function viewdropshipAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('inventorydropship/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('View Drop Ship'));
        $this->renderLayout();
    }

    public function confirmpostAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }

        if ($data = $this->getRequest()->getPost()) {
            $dropshipId = $data['dropship_id'];

            $supplierNotNeedToConfirmProvide = true;
            if (Mage::getStoreConfig('inventoryplus/dropship/supplier_confirm_provide'))
                $supplierNotNeedToConfirmProvide = false;
            $adminNotNeedToApprove = true;
            if (Mage::getStoreConfig('inventoryplus/dropship/admin_approve'))
                $adminNotNeedToApprove = false;
			//zend_debug::dump($adminNotNeedToApprove);die();
            $supplierNotNeedToConfirmShipped = true;
            if (Mage::getStoreConfig('inventoryplus/dropship/supplier_confirm_shipped'))
                $supplierNotNeedToConfirmShipped = false;


            $supplier = $this->_getSession()->getSupplier();
            $dropship = Mage::getModel('inventorydropship/inventorydropship')
                    ->getCollection()
                    ->addFieldToFilter('dropship_id', $dropshipId)
                    ->addFieldToFilter('supplier_id', $supplier->getId())
                    ->getFirstItem();
					
            if ($dropship->getId()) {
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $readConnection = $resource->getConnection('core_read');

                $itemIds = $data['item'];
                try {
                    foreach ($itemIds as $itemId => $key) {
                        if (!is_numeric($key) || $key < 0)
                            $key = 0;
                        $dropshipProduct = Mage::getModel('inventorydropship/inventorydropship_product')
                                ->getCollection()
                                ->addFieldToFilter('dropship_id', $dropshipId)
                                ->addFieldToFilter('item_id', $itemId)
                                ->getFirstItem();
                        if ($dropshipProduct->getId()) {
                            if ($key > $dropshipProduct->getQtyRequest())
                                $key = $dropshipProduct->getQtyRequest();
                            $dropshipProduct->setData('qty_offer', $key);
                            if ($adminNotNeedToApprove) {
                                $dropshipProduct->setData('qty_approve', $key);
                            }
                            $dropshipProduct->save();
                        }
//                        $sqlUpdateConfirm = 'UPDATE ' . $resource->getTableName("erp_inventory_dropship_product") . ' SET qty_offer = ' . $key . ' WHERE (dropship_id = ' . $dropshipId . ') AND (item_id = '.$itemId.')';
//                        $writeConnection->query($sqlUpdateConfirm);
                    }
                } catch (Exception $e) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
                    return;
                }
                if ($adminNotNeedToApprove) { //admin does not need to approve qty product to supplier ships
                    $statusDropship = 3; //Awaiting shipment                    
                } else {
                    $statusDropship = 2; //Awaiting admin's approval
                }
                $dropship->setStatus($statusDropship)->save();
                $this->_getSession()->addSuccess($this->__('Drop ship was successfully confirmed!'));
                if ($adminNotNeedToApprove) {
                    $this->_getSession()->addNotice($this->__('Awaiting shipment'));
                } else {
                    $this->_getSession()->addNotice($this->__('Awaiting admin\'s approval'));
                }

                if (!$adminNotNeedToApprove) {
                    Mage::helper('inventorydropship')->sendEmailConfirmDropShipToAdmin($dropshipId);
                }
                $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
                return;
            }
            $this->_getSession()->addError($this->__('You cannot confirm this drop ship!'));
            $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
            return;
        }
    }

    public function cancelAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }
        $dropshipId = $this->getRequest()->getParam('id');
        $supplier = $this->_getSession()->getSupplier();
        $dropship = Mage::getModel('inventorydropship/inventorydropship')
                ->getCollection()
                ->addFieldToFilter('dropship_id', $dropshipId)
                ->addFieldToFilter('supplier_id', $supplier->getId())
                ->getFirstItem();
        if ($dropship->getId()) {
            try {
                $dropship->setStatus(5)->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($exception->getMessage());
                $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
                return;
            }
            $this->_getSession()->addSuccess($this->__('Drop ship was successfully canceled!'));
            Mage::helper('inventorydropship')->sendEmailCancelDropShipToAdmin($dropshipId);
            $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
            return;
        }
        $this->_getSession()->addError($this->__('You cannot cancel this drop ship!'));
        $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
        return;
    }

    public function shipproductAction() {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/login');
            return;
        }

        if ($data = $this->getRequest()->getPost()) {
            $dropshipId = $data['dropship_id'];

            $supplier = $this->_getSession()->getSupplier();
            $dropship = Mage::getModel('inventorydropship/inventorydropship')
                    ->getCollection()
                    ->addFieldToFilter('dropship_id', $dropshipId)
                    ->addFieldToFilter('supplier_id', $supplier->getId())
                    ->getFirstItem();
            $success = false;
            if ($dropship->getId()) {

                $supplierNotNeedToConfirmProvide = true;
                if (Mage::getStoreConfig('inventoryplus/dropship/supplier_confirm_provide'))
                    $supplierNotNeedToConfirmProvide = false;
                $adminNotNeedToApprove = true;
                if (Mage::getStoreConfig('inventoryplus/dropship/admin_approve'))
                    $adminNotNeedToApprove = false;
                $supplierNotNeedToConfirmShipped = true;
                if (Mage::getStoreConfig('inventoryplus/dropship/supplier_confirm_shipped'))
                    $supplierNotNeedToConfirmShipped = false;

                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $readConnection = $resource->getConnection('core_read');
                $itemIds = $data['item'];
                $shipped = array();
                $savedQtys = array();
                $productIds = array();
                $qtyNeedRequest = null;
                try {
                    foreach ($itemIds as $itemId => $key) {
                        if (!is_numeric($key) || $key < 0)
                            $key = 0;
                        $dropshipProduct = Mage::getModel('inventorydropship/inventorydropship_product')
                                ->getCollection()
                                ->addFieldToFilter('dropship_id', $dropshipId)
                                ->addFieldToFilter('item_id', $itemId)
                                ->getFirstItem();
                        if ($dropshipProduct->getId()) {
                            $success = true;
                            if ($key > $dropshipProduct->getQtyApprove() - $dropshipProduct->getQtyShipped())
                                $key = $dropshipProduct->getQtyApprove() - $dropshipProduct->getQtyShipped();
                            $shipped[$itemId] = $key;                            
                            if ($key > 0) {
                                $savedQtys[$dropshipProduct->getItemId()] = $key;
                                $productIds[$dropshipProduct->getItemId()] = $dropshipProduct->getProductId();
                            }

                            if (($dropshipProduct->getQtyOffer()) == 0 && $adminNotNeedToApprove && $supplierNotNeedToConfirmShipped) {
                                $dropshipProduct->setData('qty_approve', $dropshipProduct->getQtyShipped() + $key);
                            }

                            $dropshipProduct->setData('qty_shipped', $dropshipProduct->getQtyShipped() + $key)->save();

                            $orderItem  = Mage::getModel('sales/order_item')->load($itemId);
                            if ($orderItem->getProductType() == 'configurable') {
                                $itemData = unserialize($orderItem->getData('product_options'));
                                $productSku = $itemData['simple_sku'];
                                $productId = Mage::getModel('catalog/product')->getIdBySku($productSku);
                            } else {
                                $productId = $orderItem->getProductId();
                            }
                         
                            /* return avaiable qty for warehouse */
                            $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                                    ->addFieldToFilter('order_id', $orderItem->getOrderId())
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getFirstItem();
                            $warehouseId = $warehouseOrder->getWarehouseId();
                            
                            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                                            ->addFieldToFilter('warehouse_id', $warehouseId)
                                            ->addFieldToFilter('product_id', $productId)
                                            ->getFirstItem();
                            $availableQty = $warehouseProduct->getAvailableQty();
                            try {                                
                                $minToChangeStatus = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
                                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);                                
                                $stockItem->setQty($stockItem->getQty() + $key)
                                        ->save();
                                if($stockItem->getQty() > $minToChangeStatus){
                                    $stockItem->setIsInStock(1)
                                        ->save();
                                }
                                $new_onhold_qty = $warehouseOrder->getQty() - $key;                                
                                $warehouseOrder->setQty($new_onhold_qty)
                                        ->save();
                                $warehouseProduct->setAvailableQty($availableQty + $key)
                                        ->save();
                            } catch (Exception $e) {
                                
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
                    return;
                }
                if ($success) {
                    $partial = false;
                    $dropshipProducts = Mage::getModel('inventorydropship/inventorydropship_product')
                            ->getCollection()
                            ->addFieldToFilter('dropship_id', $dropshipId);
                    foreach ($dropshipProducts as $dropshipProduct) {
                        if ($dropshipProduct->getQtyShipped() != $dropshipProduct->getQtyApprove()) {
                            $partial = true;
                            break;
                        }
                    }
                    if ($partial)
                        $dropship->setStatus('4');
                    else
                        $dropship->setStatus('6');
                    try {
                        
                        $dropship->save();
                        //create shipment when supplier approved
                        if ($savedQtys) {

                            Mage::getModel('admin/session')->setData('break_shipment_event_dropship', true);
                            Mage::getModel('core/session')->setData('break_shipment_event_dropship', true);

                            $order = Mage::getModel('sales/order')->load($dropship->getOrderId());
                            $transaction = Mage::getModel('core/resource_transaction')
                                    ->addObject($order);
                            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

                            $shipment->register();
                            $shipment->getOrder()->setIsInProcess(true);
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($shipment)
                                    ->addObject($shipment->getOrder())
                                    ->save();
                        }
                    } catch (Exception $e) {
                         Zend_debug::dump($e->getTraceAsString());
                        die('====');
                    }

                    $this->_getSession()->addSuccess($this->__('Drop ship was successfully shipped!'));
                    Mage::helper('inventorydropship')->sendEmailConfirmShippedToAdmin($dropshipId, $shipped);
                    $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
                    return;
                }
            }
            $this->_getSession()->addError($this->__('Please enter Qty To Ship greater than 0 to ship this dropship!'));
            $this->_redirect('*/*/viewdropship', array('id' => $dropshipId));
            return;
        }
    }

    public function getFiledSaveHistory() {
        return array('name', 'contact_name', 'email', 'telephone', 'fax', 'street', 'city', 'country_id', 'state'/* ,'stateEl','state_id' */, 'postcode', 'website', 'description', 'status');
    }

    public function getTitleByField($field) {
        $fieldArray = array(
            'name' => Mage::helper('inventoryplus')->__('Supplier Name '),
            'contact_name' => Mage::helper('inventoryplus')->__('Contact Person'),
            'email' => Mage::helper('inventoryplus')->__('Email'),
            'telephone' => Mage::helper('inventoryplus')->__('Telephone'),
            'fax' => Mage::helper('inventoryplus')->__('Fax'),
            'street' => Mage::helper('inventoryplus')->__('Street'),
            'city' => Mage::helper('inventoryplus')->__('City'),
            'country_id' => Mage::helper('inventoryplus')->__('Country'),
            'state' => Mage::helper('inventoryplus')->__('State/Province'),
            'postcode' => Mage::helper('inventoryplus')->__('Zip/Postal Code'),
            'website' => Mage::helper('inventoryplus')->__('Website'),
            'description' => Mage::helper('inventoryplus')->__('Description'),
            'status' => Mage::helper('inventoryplus')->__('Status')
        );
        if (!$fieldArray[$field])
            return $field;
        return $fieldArray[$field];
    }

}
