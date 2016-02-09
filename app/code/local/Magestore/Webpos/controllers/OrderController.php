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
     * @package     Magestore_Webpos
     * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
     * @license     http://www.magestore.com/license-agreement.html
     */

    /**
     * Webpos Index Controller
     * 
     * @category    Magestore
     * @package     Magestore_Webpos
     * @author      Magestore Developer
     */
    class Magestore_Webpos_OrderController extends Magestore_Webpos_Controller_Action {

        public function getOnepage() {
            return Mage::getSingleton('checkout/type_onepage');
        }

        public function getSession() {
            return Mage::getSingleton('checkout/session');
        }

        public function isVirtual() {
            return $this->getOnepage()->getQuote()->isVirtual();
        }

        public function viewOrderAction() {
            if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
                return;
            }
            $this->loadLayout(false);
            $this->renderLayout();
        }

        public function printAction() {
            if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
                return;
            }
            $this->loadLayout('print');
            $this->renderLayout();
        }

        protected function _getWebposSession() {
            return Mage::getSingleton('webpos/session');
        }

        public function emptyCartAction() {
            if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
                return;
            }
            try {
                Mage::helper('webpos')->emptyPOSdata();
            } catch (Exception $e) {
                $this->getSession()->setData('webpos_error', $e->getMessage());
            }
            $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                    ->setTemplate('webpos/webpos/review/totals.phtml')
                    ->toHtml();
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function saveOrderAction() {
            $error = false;
            $helper = Mage::helper('webpos');
            $helperPayment = Mage::helper('webpos/payment');
            $onepage = $this->getOnepage();
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isCreateOrder = Mage::helper('webpos/permission')->isCreateOrder($userId);
            if (!$isCreateOrder) {
                $result['error'] = $this->__('You do not have permission to create order.');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return $this;
            }
            $cashin = $this->getRequest()->getParam('cashin');
            Mage::getSingleton('webpos/session')->setWebposCash($cashin);
            $isManage = Mage::helper('webpos/permission')->manageByThisUser($userId);
            $checkoutSession = Mage::getSingleton('checkout/session');
            $checkoutCart = Mage::getSingleton('checkout/cart');
            $posSession = Mage::getSingleton('webpos/session');
            $customerSession = Mage::getSingleton('customer/session');
            $helper->getDefaultCustomer();
            $quote = $onepage->getQuote();
            $isVirtual = $this->isVirtual();
            $customerComment = $this->getRequest()->getParam('comment');
            $checkoutSession->setData('customer_comment', $customerComment);
            $allowGuestCheckout = Mage::helper('checkout')->isAllowedGuestCheckout($quote);
            $customer = Mage::getModel('webpos/posorder')->getCustomer();
            if (!$allowGuestCheckout && !$customerSession->isLoggedIn())
                $customerSession->setCustomerAsLoggedIn($customer);
            if (!$quote->getCustomer()) {
                $quote->setCustomer($customer);
            }
            if (!$isVirtual) {
                $shipping_method = $this->getRequest()->getPost('shipping_method', '');
                if ((!isset($shipping_method) || $shipping_method == '') && $helperPayment->isWebposShippingEnabled())
                    $shipping_method = 'webpos_shipping_free';
                try {
                    /*
                      $shippingAddress = $quote->getShippingAddress()->addData();
                      $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                      ->setShippingMethod($shipping_method);
                     */
                    $onepage->saveShippingMethod($shipping_method);
                } catch (Exception $e) {
                    $error = true;
                    $result['error'] = $e->getMessage();
                }
            }
            $paymentRedirect = false;
            try {
                $result = array();
                $payment = $this->getRequest()->getPost('payment', array());
                $payment_method = $this->getRequest()->getPost('payment_method', false);
                if ((empty($payment_method) || $payment_method == '') && $helperPayment->isCashPaymentEnabled())
                    $payment_method = 'cashforpos';
                $payment['method'] = $payment_method;

                if ($payment) {
                    $this->getOnepage()->getQuote()->getPayment()->importData($payment);
                }
                $paymentRedirect = $quote->getPayment()->getCheckoutRedirectUrl();
            } catch (Mage_Payment_Exception $e) {
                $error = true;
                if ($e->getFields()) {
                    $result['fields'] = $e->getFields();
                }
                $result['error'] = $e->getMessage();
            } catch (Mage_Core_Exception $e) {
                $error = true;
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $error = true;
                Mage::logException($e);
                $result['error'] = $this->__('Unable to set Payment Method.');
            }

            if (isset($result['error'])) {
                $this->getResponse()->setBody(Zend_Json::encode($result));
            }

            if ($paymentRedirect && $paymentRedirect != '') {
                $result['paymentRedirect'] = $paymentRedirect;
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return $this;
            }

            if (!$error) {
                try {
                    $quote->collectTotals();
                    if (!Mage::helper('webpos/customer')->isEnableAutoSendEmail('order')) {
                        Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED, "0");
                    } else {
                        $template_order = Mage::helper('webpos/customer')->getWebposEmailTemplate('order');
                        if (isset($template_order['guest']) && $template_order['guest'] != '') {
                            Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_order['guest']);
                        }
                        if (isset($template_order['customer']) && $template_order['customer'] != '') {
                            Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $template_order['customer']);
                        }
                    }
                    $onepage->saveOrder();
                    $redirectUrl = $onepage->getCheckout()->getRedirectUrl();
                } catch (Mage_Core_Exception $e) {
                    Mage::logException($e);
                    $result['error'] = $e->getMessage();
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                } catch (Exception $e) {
                    Mage::logException($e);
                    $result['error'] = $e->getMessage();
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
                try {
                    $quote->save();
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
                if ($redirectUrl) {
                    try {
                        $lastOrderId = $onepage->getCheckout()->getLastOrderId();
                        $order = Mage::getModel('sales/order')->load($lastOrderId);
                        $user = $posSession->getUser();
                        if ($user->getId() != '') {
                            $order->setWebposAdminId($user->getId())
                                    ->setWebposAdminName($user->getDisplayName());
                        }
                        $this->saveOrderData($order);
                        $order->save();
                    } catch (Exception $e) {

                        Mage::logException($e);
                        $result['error'] = $e->getMessage();
                        $this->getResponse()->setBody(Zend_Json::encode($result));
                        return;
                    }
                    $redirect = $redirectUrl;
                    $result['redirect'] = $redirect;
                } else {
                    $result['success'] = true;
                    $result['orderId'] = $onepage->getCheckout()->getLastOrderId();
                    $lastOrderId = $result['orderId'];
                    $order = Mage::getModel('sales/order')->load($lastOrderId);
                    $result['successMessage'] = $this->__('Place order successfully! Order: #' . $order->getIncrementId());
                    $result['orderIncrementId'] = $order->getIncrementId();
                    $printLink = Mage::getUrl('webpos/order/print', array('order_id' => $lastOrderId, '_secure' => true));
                    $result['printLink'] = $printLink;
                    $result['isManage'] = $isManage;
                    $paidFullAmount = false;
                    $grandTotal = $order->getGrandTotal();
                    $totalPaid = $this->getRequest()->getParam('cashin');
                    $totalRefunded = $this->getRequest()->getParam('remain');
                    $comment = $this->getRequest()->getParam('comment');
                    if (isset($comment) && $comment != '') {
                        $order->addStatusHistoryComment($comment);
                    }
                    if ($totalRefunded <= 0) {
                        $totalRefunded = 0 - $totalRefunded;
                    } else {
                        $totalRefunded = 0;
                    }
                    if (!isset($totalPaid)) {
                        $totalPaid = 0;
                    }

                    $create_invoice = Mage::getStoreConfig('webpos/general/auto_create_invoice', Mage::app()->getStore()->getId());
                    $create_shipment = Mage::getStoreConfig('webpos/general/auto_create_shipment', Mage::app()->getStore()->getId());

                    $totalDue = $grandTotal - $totalPaid;
                    if ($totalDue <= 0) {
                        $totalRefunded = 0 - $totalDue;
                        $paidFullAmount = true;
                        $create_invoice = true;
                    } elseif ($payment_method == 'ccforpos') {
                        $order->setData('webpos_ccforpos', $totalDue);
                        $order->setData('webpos_base_ccforpos', $totalDue);
                    } elseif ($payment_method == 'cp1forpos') {
                        $order->setData('webpos_cp1forpos', $totalDue);
                        $order->setData('webpos_base_cp1forpos', $totalDue);
                    } elseif ($payment_method == 'cp2forpos') {
                        $order->setData('webpos_cp2forpos', $totalDue);
                        $order->setData('webpos_base_cp2forpos', $totalDue);
                    } elseif ($payment_method == 'cashforpos' && $totalPaid == 0) {
                        $order->setData('webpos_cash', $order->getGrandTotal());
                        $order->setData('webpos_base_cash', $order->getBaseGrandTotal());
                    }

                    $user = $posSession->getUser();
                    if ($user->getId() != '') {
                        $order->setWebposAdminId($user->getId())
                                ->setWebposAdminName($user->getDisplayName());
                        $userLocationId = $user->getLocationId();
                        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
                            $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                                    ->addFieldToFilter('user_id', $user->getId());
                            $userLocationId = $userCollection->getfirstItem()->getWarehouseId();
                        }
                        $order->setLocationId($userLocationId);
                        $till = $posSession->getTill();
                        if ($till->getTillId()) {
                            $order->setTillId($till->getTillId());
                        } else {
                            $till_id = $this->getRequest()->getParam('till_id');
                            if (isset($till_id)) {
                                $order->setTillId($till_id);
                            }
                        }
                    }
                    /*
                      if ($totalPaid > 0 && $totalDue > 0)
                      $order->setTotalPaid($totalPaid)
                      ->setBaseTotalPaid($totalPaid)
                      ->setTotalRefunded($totalRefunded)
                      ->setBaseTotalRefunded($totalRefunded);
                     */
                    $order->save();
                    $this->createShipmentAndInvoice($lastOrderId, $order, $create_shipment, $create_invoice);

                    $holded_key = $checkoutSession->getData('holded_key');
                    if ($holded_key) {
                        $result['holded_key'] = $holded_key;
                    }
                    if ($order->getId() != '') {
                        $this->saveOrderData($order);
                        $checkoutSession->clear();
                        if ($customerSession->isLoggedIn()) {
                            $newcustomer = Mage::getModel('customer/customer')->load(0);
                            $customerSession->setCustomerAsLoggedIn($newcustomer);
                        }
                        $posSession->setData('webpos_customerid', null);
                        $result['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
                        $result['customerEmail'] = $order->getCustomerEmail();
                        $result['paidFullAmount'] = ($create_invoice == true) ? true : $paidFullAmount;
                        $result['shipped'] = ($create_shipment == true) ? true : $false;
                        if ($isVirtual)
                            $result['isVirtual'] = true;
                        $auto_print = Mage::getStoreConfig('webpos/receipt/auto_print', Mage::app()->getStore()->getId());
                        if ($auto_print == true) {
                            $result['auto_print'] = true;
                        }
                    }
                }
                $this->getResponse()->setBody(Zend_Json::encode($result));
            } else {
                $this->getResponse()->setBody(Zend_Json::encode($result));
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function saveOrderData($order) {
            $session = Mage::getModel('webpos/session');
            $user = $session->getUser();
            $userId = $user->getUserId();
            $userRoleId = $user->getRoleId();
            $userLocationId = Mage::helper('webpos/user')->getCurrentUserLocationId();
            $orderComment = $productIds = '';
            $unitPrices = 0;
            $posorderModel = Mage::getModel('webpos/posorder');
            $posorderModel->setData('order_id', $order->getIncrementId());
            $posorderModel->setData('order_totals', $order->getGrandTotal());
            $posorderModel->setData('order_status', $order->getStatus());
            $posorderModel->setData('created_date', $order->getCreatedAt());
            $posorderModel->setData('order_comment', $orderComment);
            $posorderModel->setData('product_ids', $productIds);
            $posorderModel->setData('unit_prices', $unitPrices);
            $posorderModel->setData('user_id', $userId);
            $posorderModel->setData('location_id', $userLocationId);
            $posorderModel->setData('user_role_id', $userRoleId);
            $posorderModel->save();
            /*
              if (Mage::helper('persistent/session')->isPersistent()) {
              Mage::helper('persistent/session')->getSession()->removePersistentCookie();
              $customerSession = Mage::getSingleton('customer/session');
              if (!$customerSession->isLoggedIn()) {
              $customerSession->setCustomerId(null)->setCustomerGroupId(null);
              }
              Mage::getSingleton('persistent/observer')->setQuoteGuest();
              }
             */
            $reloading_order_id = Mage::getModel('checkout/session')->getData('reloading_order_id');
            if ($reloading_order_id) {
                try {
                    $order = Mage::getModel('sales/order')->load($reloading_order_id);
                    $order->unhold()->save();
                    $order->cancel()->save();
                    Mage::getModel('checkout/session')->setData('reloading_order_id', null);
                    Mage::getModel('checkout/session')->setData('holded_key', null);
                } catch (Exception $e) {
                    
                }
            }
            $enable_till = Mage::getStoreConfig('webpos/general/enable_tills');
            if ($order->getIncrementId() && $enable_till) {
                $storeId = Mage::app()->getStore()->getId();
                $postData = Mage::app()->getRequest()->getParams();
                $till = $session->getTill();
                $tillId = 0;
                if ($till->getTillId()) {
                    $tillId = $till->getTillId();
                } else {
                    $till_id = $this->getRequest()->getParam('till_id');
                    if (isset($till_id)) {
                        $tillId = $till_id;
                    }
                }
                $posorderModel->setData('till_id', $tillId);
                $posorderModel->save();
                $cash = $order->getGrandTotal();
                $cashIn = $order->getData('webpos_cash');
                if ($cashIn > 0 && $cashIn < $cash) {
                    $cash = $cashIn;
                }
                $data_transaction = array(
                    'payment_method' => $postData['payment_method'],
                    'cash_in' => $cash,
                    'cash_out' => 0,
                    'amount' => $cash,
                    'store_id' => $storeId,
                    'user_id' => $userId,
                    'order_id' => $order->getIncrementId(),
                    'till_id' => $tillId,
                    'location_id' => $userLocationId
                );

                if ($postData['payment_method'] == 'cashforpos' || $order->getData('webpos_cash') > 0) {
                    Mage::getModel('webpos/transaction')->saveTransactionData($data_transaction);
                }
            }
        }

        public function orderlistSearchAction() {
            if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
                return;
            }
            $this->loadLayout(false);
            $this->renderLayout();
        }

        public function cancelOrderAction() {
            if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
                return;
            }
            /*

              vietdq permission
             */
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            if ($isManage == false) {
                $result['error'] = $this->__("Access denied! You don't have the permission to process this action.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            /*
              end vietdq
             */
            $result = array();
            try {
                $orderId = $this->getRequest()->getParam('order_id');
                $orderModel = Mage::getModel('sales/order');
                $orderModel->load($orderId);
                if ($orderModel->canCancel()) {
                    $orderModel->cancel();
                    $orderModel->setStatus('canceled');
                    $orderModel->save();
                } else {
                    $result['message'] = $this->__('This order camnot be canceled!');
                }
                $result['success'] = true;
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function refundOrderAction() {
            if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
                return;
            }

            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            if ($isManage == false) {
                $result['error'] = $this->__("Access denied! You don't have the permission to process this action.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            /* dif */
            $dif = array();
            $qty = $this->getRequest()->getParam('qty');
            $stock = $this->getRequest()->getParam('stock');
            $qtyEx = explode('$refund$', $qty);
            $stockEx = explode('$refund$', $stock);
            for ($i = 0; $i < count($qtyEx); $i++) {
                $anItemQty = explode('/', $qtyEx[$i]);
                $anItemStock = explode('/', $stockEx[$i]);
                if (isset($anItemQty[1]))
                    $dif[$i]['qty'] = $anItemQty[1];
                if (isset($anItemQty[0]) && $anItemQty[0] != '')
                    $dif[$i]['order_item_id'] = $anItemQty[0];
                if (isset($anItemStock[1]))
                    $dif[$i]['back_to_stock'] = $anItemStock[1];
            }
            $orderId = $this->getRequest()->getParam('order_id');
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $orderIncrementId = Mage::getModel('sales/order')->load($orderId)->getIncrementId();
            $info = array();
            $info['order_increment_id'] = $orderIncrementId;
            $commentText = $this->getRequest()->getParam('comment');
            $ajustFee = $this->getRequest()->getParam('ajust_fee');
            $ajustRf = $this->getRequest()->getParam('ajust_refund');
            /* creditmemo data */
            $collectionCreditmemo = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->addAttributeToFilter('order_id', array('eq' => $orderId))
                    ->addAttributeToSelect('*');
            $shipRefunded = 0;
            if ($collectionCreditmemo->getSize()) {
                foreach ($collectionCreditmemo as $co) {
                    $shipRefunded += $co->getShippingAmount();
                }
            }
            $oldShip = Mage::getModel('sales/order')->load($orderId)->getShippingAmount();
            /**/
            $other = array(
                "do_offline" => "1",
                "comment_text" => $commentText,
                "shipping_amount" => $oldShip ? ($oldShip - $shipRefunded) : '0',
                "adjustment_positive" => (float) $ajustRf,
                "adjustment_negative" => (float) $ajustFee,
                "invoice_id" => $invoiceId
            );
            /**/
            $result = array();
            try {
                $result = $this->_prepareCreditmemo($dif, $info, $other);
                if (!$result['error'])
                    $result['success'] = 'The credit memo has been created successfully!';
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            /**/
            $statusOrderClass = "type4";
            $order = Mage::getModel('sales/order')->load($orderId);
            $orderStatus = $order->getStatus();
            $statusOrderClass = Mage::helper('webpos')->getOrderClass($orderStatus);
            $result['orderId'] = $order->getId();
            $result['invoiceId'] = $invoiceId;
            $result['statusOrderClass'] = $statusOrderClass;
            $result['order_status'] = $order->getStatus();
            $result['order_status_label'] = $order->getStatusLabel();
            $result['total_due'] = $order->getTotalDue();
            $result['message'] = $html;
            $result['can_refund'] = $order->canCreditmemo() ? 'true' : 'false';
            $result['can_ship'] = $order->canShip() ? 'true' : 'false';
            /**/
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function loadRefundPopupAction() {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $html = Mage::app()->getLayout()->createBlock('core/template')->setOrder($order)->setTemplate('webpos/webpos/order_items.phtml')->toHtml();
            $this->getResponse()->setBody($html);
        }

        /* Mr.Jack creditmemo */

        protected function _prepareCreditmemo($dif, $info, $other) {
            $qtys = array();

            foreach ($dif as $item) {
                if (isset($item['qty'])) {
                    $qtys[$item['order_item_id']] = array("qty" => $item['qty']);
                }
                if (isset($item['back_to_stock']) && $item['back_to_stock'] == 'true') {
                    $backToStock[$item['order_item_id']] = array("back_to_stock" => true);
                }
            }

            $data = array(
                "items" => $qtys,
                'back_to_stock' => $backToStock
            );
            $result = array();
            $data = array_merge($data, $other);
            if (!empty($data['comment_text'])) {
                Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
            }

            try {
                $creditmemo = $this->_initCreditmemo($data, $info);
                if ($creditmemo) {
                    if (($creditmemo->getGrandTotal() <= 0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                        Mage::throwException(
                                $this->__('Credit memo\'s total must be positive.')
                        );
                    }

                    $comment = '';
                    if (!empty($data['comment_text'])) {
                        $creditmemo->addComment(
                                $data['comment_text'], isset($data['comment_customer_notify']), isset($data['is_visible_on_front'])
                        );
                        if (isset($data['comment_customer_notify'])) {
                            $comment = $data['comment_text'];
                        }
                    }

                    if (isset($data['do_refund'])) {
                        $creditmemo->setRefundRequested(true);
                    }
                    if (isset($data['do_offline'])) {
                        $creditmemo->setOfflineRequested((bool) (int) $data['do_offline']);
                    }

                    $creditmemo->register();
                    if (!empty($data['send_email'])) {
                        $creditmemo->setEmailSent(true);
                    }

                    $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                    $this->_saveCreditmemo($creditmemo);
                    if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('creditmemo')) {
                        $template_creditmemo = Mage::helper('webpos/customer')->getWebposEmailTemplate('creditmemo');
                        if (isset($template_creditmemo['guest']) && $template_creditmemo['guest'] != '') {
                            Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_creditmemo['guest']);
                        }
                        if (isset($template_creditmemo['customer']) && $template_creditmemo['customer'] != '') {
                            Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE, $template_creditmemo['customer']);
                        }
                        $creditmemo->sendEmail(true, $comment);
                    }
                    Mage::getSingleton('adminhtml/session')->getCommentText(true);
                    return;
                } else {
                    $result['error'] = 'Cannot create credit memo for this order!';
                }
            } catch (Mage_Core_Exception $e) {
                $result['error'] = $e->getMessage();
                Mage::getSingleton('adminhtml/session')->setFormData($data);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            return $result;
        }

        protected function _initCreditmemo($data, $info, $update = false) {
            $creditmemo = false;
            $invoice = false;
            $orderId = $info['order_increment_id']; //$this->getRequest()->getParam('order_id');
            $invoiceId = $data['invoice_id'];
            if ($orderId) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                if ($invoiceId) {
                    $invoice = Mage::getModel('sales/order_invoice')
                            ->load($invoiceId)
                            ->setOrder($order);
                }
                if (!$order->canCreditmemo()) {
                    return false;
                }
                $savedData = array();
                $qtys = array();
                $backToStock = array();
                if (isset($data['items'])) {
                    $savedData = $data['items'];
                    foreach ($savedData as $orderItemId => $itemData) {
                        if (isset($itemData['qty'])) {
                            $qtys[$orderItemId] = $itemData['qty'];
                        }
                    }
                }
                if (isset($data['back_to_stock'])) {
                    $savedData = $data['back_to_stock'];
                    foreach ($savedData as $orderItemId => $itemData) {
                        if (isset($itemData['back_to_stock'])) {
                            $backToStock[$orderItemId] = $itemData['back_to_stock'];
                        }
                    }
                }
                $data['qtys'] = $qtys;
                $data['back_to_stock'] = $backToStock;
                /* Mr.Jack create creditmemo with full paid by cash 
                  if ($order->getWebposCash() >= $order->getGrandTotal()) {
                  if ($order->getPayment()->getMethodInstance()->getCode() == 'cashforpos') {
                  $order->setWebposCash(0)
                  ->setWebposBaseCash(0);
                  }
                  }
                 */
                $service = Mage::getModel('sales/service_order', $order);
                if ($invoice) {
                    if ($invoice->getWebposCash()) {
                        $order->setWebposCash(0)->setWebposBaseCash(0);
                        $invoice->setWebposCash(0)->setWebposBaseCash(0);
                    }
                    $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
                } else {
                    $creditmemo = $service->prepareCreditmemo($data);
                }

                /**
                 * Process back to stock flags
                 */
                foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                    $orderItem = $creditmemoItem->getOrderItem();
                    $parentId = $orderItem->getParentItemId();
                    if (isset($backToStock[$orderItem->getId()])) {
                        $creditmemoItem->setBackToStock(true);
                    } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                        $creditmemoItem->setBackToStock(true);
                    } elseif (empty($savedData)) {
                        $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                    } else {
                        $creditmemoItem->setBackToStock(false);
                    }
                }
            }

            return $creditmemo;
        }

        protected function _saveCreditmemo($creditmemo) {
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($creditmemo)
                    ->addObject($creditmemo->getOrder());
            if ($creditmemo->getInvoice()) {
                $transactionSave->addObject($creditmemo->getInvoice());
            }
            $transactionSave->save();

            return $this;
        }

        /* End Mr.Jack */

        public function saveOrderViewCommentAction() {
            $orderId = $this->getRequest()->getParam('order_id');
            $orderComment = $this->getRequest()->getParam('order_comment');
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            if ($isManage == false) {
                $result = array();
                $error = true;
                $result['error'] = $error;
                $result['html'] = '';
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            $error = true;
            $html = '';
            $result = array();
            if ($orderId) {
                try {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    $order->addStatusHistoryComment($orderComment, $order->getStatus());

                    $order->setCustomerNote($orderComment);
                    $order->save();
                    $_history = $order->getAllStatusHistory();
                    $_buffer = array();
                    if ($_history) {
                        $html .= '<p class="title">Comment History</p>';
                        foreach ($_history as $_historyItem) {
                            $html .= '<p>';
                            $html .= '<span class="date">' . $_historyItem->getData('created_at') . '</span>';
                            $html .= '<span class="status">' . $_historyItem->getData('status');
                            if ($_historyItem->getData('comment'))
                                $html .= " | " . $_historyItem->getData('comment');
                            $html .= '</span>';
                            $html .= '</p>';
                        }
                    }
                    $error = false;
                } catch (Exception $e) {
                    $error = true;
                }
            }
            $result['error'] = $error;
            $result['html'] = $html;
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function sendOrderEmailToCustomerAction() {
            $orderId = $this->getRequest()->getParam('order_id', null);
            $orderCustomerEmail = $this->getRequest()->getParam('customer_email');
            if (!Zend_Validate::is(trim($orderCustomerEmail), 'EmailAddress')) {
                $error = true;
                $html = 'Invalid email address.';
            } elseif ($orderId && $orderCustomerEmail) {
                $order = Mage::getModel('sales/order')->load($orderId);
                $error = true;
                $result = array();
                try {
//                $ord = $order->setEmailSent(false)->setCustomerEmail($orderCustomerEmail)->queueOrderUpdateEmail(true, '', true);
                    $ord = $order->setEmailSent(false)->setCustomerEmail($orderCustomerEmail)->sendNewOrderEmail();
                    if ($ord && $ord->getId()) {
                        $error = false;
                        $html = $this->__('The order #%s has been sent to the customer %s', $order->getIncrementId(), $order->getCustomerEmail());
                    } else {
                        $error = true;
                        $html = $this->__('The order #%s cannot be sent to the customer %s', $order->getIncrementId(), $order->getCustomerEmail());
                    }
                } catch (Exception $e) {
                    $error = true;
                    $html = $this->__('The order #%s cannot be sent to the customer %s', $order->getIncrementId(), $order->getCustomerEmail());
                }
            } else {
                $error = true;
                $html = $this->__('Cannot send the order');
            }
            $result['error'] = $error;
            $result['html'] = $html;
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        /**
         * Initialize invoice model instance
         *
         * @return Mage_Sales_Model_Order_Invoice
         */
        protected function _initInvoice($orderId, $update = false) {
            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                /**
                 * Check order existing
                 */
                if (!$order->getId()) {
                    return false;
                }
                /**
                 * Check invoice create availability
                 */
                if (!$order->canInvoice()) {
                    return false;
                }
                $savedQtys = $this->_getItemQtys($order);
                /* Mr.Jack create invoice with full paid by cash 
                  if ($order->getWebposCash() >= $order->getGrandTotal()) {
                  if ($order->getPayment()->getMethodInstance()->getCode() == 'cashforpos') {
                  $order->setWebposCash(0)
                  ->setWebposBaseCash(0);
                  }
                  }
                 */
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
                if (!$invoice->getTotalQty()) {
                    Mage::throwException($this->__('The invoice cannot be created without any products.'));
                }
            }

            Mage::register('current_invoice', $invoice);
            return $invoice;
        }

        /**
         * Prepare shipment
         *
         * @param Mage_Sales_Model_Order_Invoice $invoice
         * @return Mage_Sales_Model_Order_Shipment
         */
        protected function _prepareShipment($invoice, $order) {
            $savedQtys = $this->_getItemQtys($order);
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
            if (!$shipment->getTotalQty()) {
                return false;
            }
            $shipment->register();

            return $shipment;
        }

        /**
         * app\code\core\Mage\Adminhtml\controllers\Sales\Order\InvoiceController.php, function saveAction()
         */
        public function invoiceAction() {
            /*
              vietdq permission
             */
            $result = array();
            $error = true;
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            if ($isManage == false) {
                $result['error'] = $this->__("Access denied! You don't have the permission to process this action.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            /*
              end vietdq
             */
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($orderId && $order && $order->getId()) {
                $invoice = $this->_initInvoice($orderId);
                if ($invoice) {
                    if (!empty($data['capture_case'])) {
                        $invoice->setRequestedCaptureCase($data['capture_case']);
                    }
                    $invoice->register();
                    $invoice->setEmailSent(true);
                    $invoice->getOrder()->setCustomerNoteNotify(true);
                    $invoice->getOrder()->setIsInProcess(true);
                    $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());

                    $shipment = false;
                    if ((int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                        $shipment = $this->_prepareShipment($invoice);
                        if ($shipment) {
                            $shipment->setEmailSent($invoice->getEmailSent());
                            $transactionSave->addObject($shipment);
                        }
                    }
                    try {
                        $transactionSave->save();
                        if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('invoice')) {
                            $template_invoice = Mage::helper('webpos/customer')->getWebposEmailTemplate('invoice');
                            if (isset($template_invoice['guest']) && $template_invoice['guest'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_invoice['guest']);
                            }
                            if (isset($template_invoice['customer']) && $template_invoice['customer'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $template_invoice['customer']);
                            }
                            $invoice->sendEmail($order->getCustomerEmail(), '');
                        }
                        $error = false;
                        $html = $this->__('The invoice has been created.');
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $error = false;
                        $html = $this->__('Unable to send the invoice email.');
                    }
                }
            }
            $statusOrderClass = "type4";
            $order = Mage::getModel('sales/order')->load($orderId);
            $orderStatus = $order->getStatus();
            $statusOrderClass = Mage::helper('webpos')->getOrderClass($orderStatus);
            $result['orderId'] = $order->getId();
            $result['invoiceId'] = $invoice->getId();
            $result['statusOrderClass'] = $statusOrderClass;
            $result['order_status'] = $order->getStatus();
            $result['order_status_label'] = $order->getStatusLabel();
            $result['total_due'] = $order->getTotalDue();
            $result['message'] = $html;
            $result['error'] = $error;
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        /* Dung noi dung ham nay thay cho ham saveDataAftersaveAction */

        public function saveDataAftersaveAction() {
            $result = array();
            $error = true;
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isInvoice = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $create_shipment = $this->getRequest()->getParam('create_shipment');
            $create_invoice = $this->getRequest()->getParam('create_invoice');
            $invoice_error = '';
            $invoice_message = '';
            $shipment_error = '';
            $shipment_message = '';


            if ($isInvoice == false) {
                $result['message'] = $this->__("Access denied! You don't have the permission to process this action.");
                $result['error'] = true;
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            //end vietdq
            if ($orderId && $order && $order->getId()) {
                $result['error'] = false;
                if ($create_invoice) {
                    try {
                        $invoice = $this->_initInvoice($orderId);
                        if ($invoice) {
                            if (!empty($data['capture_case'])) {
                                $invoice->setRequestedCaptureCase($data['capture_case']);
                            }
                            $invoice->register();
                            $invoice->setEmailSent(true);
                            $invoice->getOrder()->setCustomerNoteNotify(true);
                            $invoice->getOrder()->setIsInProcess(true);
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder());

                            $shipment = false;
                            if ((int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                                $shipment = $this->_prepareShipment($invoice);
                                if ($shipment) {
                                    $shipment->setEmailSent($invoice->getEmailSent());
                                    $transactionSave->addObject($shipment);
                                }
                            }

                            $transactionSave->save();
                            if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('invoice')) {
                                $template_invoice = Mage::helper('webpos/customer')->getWebposEmailTemplate('invoice');
                                if (isset($template_invoice['guest']) && $template_invoice['guest'] != '') {
                                    Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_invoice['guest']);
                                }
                                if (isset($template_invoice['customer']) && $template_invoice['customer'] != '') {
                                    Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $template_invoice['customer']);
                                }
                                $invoice->sendEmail($order->getCustomerEmail(), '');
                            }
                            $invoice_error = false;
                            $invoice_message = $this->__('The invoice has been created.');
                        } else {
                            $invoice_error = true;
                            $invoice_message = $this->__('The invoice is not exist');
                        }
                    } catch (Mage_Core_Exception $e) {
                        $invoice_error = true;
                        $invoice_message = $e->getMessage();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $invoice_error = true;
                        $invoice_message = $this->__('Unable to save the invoice.');
                    }
                }

                if ($create_shipment) {
                    try {
                        $shipment = $this->_initShipment($orderId);
                        if ($shipment) {
                            $shipment->register();
                            $shipment->setEmailSent(true);
                            $shipment->getOrder()->setCustomerNoteNotify(true);
                            $this->_saveShipment($shipment);
                            if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('shipment')) {
                                $shipment->sendEmail($order->getCustomerEmail());
                            }
                            $shipment_error = false;
                            $shipment_message = $this->__('The shipment has been created.');
                        } else {
                            $shipment_error = true;
                            $shipment_message = $this->__('An error occurred while creating shipment.');
                        }
                    } catch (Mage_Core_Exception $e) {
                        $shipment_error = true;
                        $shipment_message = $e->getMessage();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $shipment_error = true;
                        $shipment_message = $this->__('An error occurred while creating shipment.');
                    }
                }
            } else {
                $result['error'] = true;
                $result['message'] = $this->__('The order does not exist');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            $result['create_invoice'] = $create_invoice;
            $result['create_shipment'] = $create_shipment;
            if ($create_shipment && $create_invoice && !$invoice_error && !$shipment_error) {
                $result['apply_message'] = $this->__('The invoice and shipment have been created.');
            } else {
                if ($create_invoice) {
                    // $result['create_invoice'] = $create_invoice;
                    $result['invoice_error'] = $invoice_error;
                    $result['invoice_message'] = $invoice_message;
                }
                if ($create_shipment) {
                    // $result['create_shipment'] = $create_shipment;
                    $result['shipment_error'] = $shipment_error;
                    $result['shipment_message'] = $shipment_message;
                }
            }
            $result['createCustomerForm'] = $this->getLayout()->createBlock('webpos/customer')
                    ->setTemplate('webpos/webpos/createcustomer.phtml')
                    ->toHtml();
            $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                    ->setTemplate('webpos/webpos/review/totals.phtml')
                    ->toHtml();
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function saveDataAftersaveAction1() {

            $result = array();
            $error = true;
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isInvoice = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $create_shipment = $this->getRequest()->getParam('create_shipment');
            $create_invoice = $this->getRequest()->getParam('create_invoice');
            $invoice_error = '';
            $invoice_message = '';
            $shipment_error = '';
            $shipment_message = '';


            if ($isInvoice == false) {
                $result['message'] = $this->__("Access denied! You don't have the permission to process this action.");
                $result['error'] = true;
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            if (!$create_invoice && !$create_shipment) {
                $result['error'] = true;
                $result['message'] = $this->__("");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            //end vietdq
            if ($orderId && $order && $order->getId()) {
                $result['error'] = false;
                if ($create_invoice) {
                    try {
                        $invoice = $this->_initInvoice($orderId);
                        if ($invoice) {
                            if (!empty($data['capture_case'])) {
                                $invoice->setRequestedCaptureCase($data['capture_case']);
                            }
                            $invoice->register();
                            $invoice->setEmailSent(true);
                            $invoice->getOrder()->setCustomerNoteNotify(true);
                            $invoice->getOrder()->setIsInProcess(true);
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder());

                            $shipment = false;
                            if ((int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                                $shipment = $this->_prepareShipment($invoice);
                                if ($shipment) {
                                    $shipment->setEmailSent($invoice->getEmailSent());
                                    $transactionSave->addObject($shipment);
                                }
                            }

                            $transactionSave->save();
                            $invoice->sendEmail($order->getCustomerEmail(), '');
                            $invoice_error = false;
                            $invoice_message = $this->__('The invoice has been created.');
                        } else {
                            $invoice_error = true;
                            $invoice_message = $this->__('The invoice is not exist');
                        }
                    } catch (Mage_Core_Exception $e) {
                        $invoice_error = true;
                        $invoice_message = $e->getMessage();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $invoice_error = true;
                        $invoice_message = $this->__('Unable to save the invoice.');
                    }
                }

                if ($create_shipment) {
                    try {
                        $shipment = $this->_initShipment();
                        if ($shipment) {
                            $shipment->register();
                            $shipment->setEmailSent(true);
                            $shipment->getOrder()->setCustomerNoteNotify(true);
                            $this->_saveShipment($shipment, $order);
                            $shipment->sendEmail($order->getCustomerEmail());
                            $shipment_error = false;
                            $shipment_message = $this->__('The shipment has been created.');
                        } else {
                            $shipment_error = true;
                            $shipment_message = $this->__('An error occurred while creating shipment.');
                        }
                    } catch (Exception $e) {
                        $shipment_error = true;
                        $shipment_message = $this->__('An error occurred while creating shipment.');
                    }
                }
            } else {
                $result['error'] = true;
                $result['message'] = $this->__('The order does not exist');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            if ((!$invoice_error && !$invoice_error) && ($create_shipment && $create_invoice)) {
                $result['apply_error'] = false;
                $result['apply_message'] = $this->__('The invoice and shipment have been created.');
            } else {
                if ($create_invoice) {
                    $result['create_invoice'] = $create_invoice;
                    $result['invoice_error'] = $invoice_error;
                    $result['invoice_message'] = $invoice_message;
                }
                if ($create_shipment) {
                    $result['create_shipment'] = $create_shipment;
                    $result['shipment_error'] = $shipment_error;
                    $result['shipment_message'] = $shipment_message;
                }
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        protected function _initShipment($orderId) {
            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);

                /**
                 * Check order existing
                 */
                if (!$order->getId()) {
                    return false;
                }
                /**
                 * Check shipment is available to create separate from invoice
                 */
                if ($order->getForcedDoShipmentWithInvoice()) {
                    return false;
                }
                /**
                 * Check shipment create availability
                 */
                if (!$order->canShip()) {
                    return false;
                }
                $savedQtys = $this->_getItemQtys($order);
                $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
            }

            Mage::register('current_shipment', $shipment);
            return $shipment;
        }

        /**
         * Save shipment and order in one transaction
         *
         * @param Mage_Sales_Model_Order_Shipment $shipment
         * @return Mage_Adminhtml_Sales_Order_ShipmentController
         */
        protected function _saveShipment($shipment) {
            $shipment->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

            return $this;
        }

        /**
         * Initialize items QTY
         */
        protected function _getItemQtys($order) {
            $savedQtys = array();
            $_order_items = $order->getAllItems();
            foreach ($_order_items as $_order_item) {
                $savedQtys[$_order_item->getId()] = $_order_item->getQtyOrdered();
            }
            return $savedQtys;
        }

        /* Mr.Jack load more order */

        public function loadMoreOrderAction() {
            $block = Mage::getBlockSingleton('webpos/orderlist');
            $this->getResponse()->setBody(json_encode($block->toHtml()));
        }

        /* end Mr.Jack */

        public function savePendingOrdersAction() {
            $result = array();
            try {
                $pendingJSONstring = $this->getRequest()->getParam('pendingJSONstring');
                $postData = Zend_Json::decode($pendingJSONstring, true);
                if (count($postData) > 0) {
                    $saveResult = $this->savePendingOrder($postData);
                    if (isset($saveResult['success'])) {
                        $result['numberOrderSaved'] = 1;
                        $result['orderId'] = $saveResult['orderId'];
                    } else {
                        $result['numberOrderUnsaved'] = 1;
                    }
                    if (isset($saveResult['error'])) {
                        $result['error'] = $saveResult['error'];
                    }
                    $result['success'] = true;
                } else {
                    $result['error'] = "Empty data";
                }
            } catch (Exception $e) {
                $result['error'] .= " " . $e->getMessage();
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        public function savePendingOrder($orderData) {
            if (empty($orderData))
                return array('error' => $this->__('Order data is empty!'));
            $error = false;
            $poshelper = Mage::helper('webpos');
            $onepage = $this->getOnepage();
            $posSession = Mage::getModel('webpos/session');
            $posorder = Mage::getModel('webpos/posorder');
            $customerModel = Mage::getModel('customer/customer');
            $customerSession = Mage::getModel('customer/session');
            $checkoutSession = Mage::getModel('checkout/session');
            $cart = Mage::getSingleton('checkout/cart');
            $quote = $cart->getQuote();
            $result = array();
            $shipping_method = (isset($orderData['shipping_method'])) ? $orderData['shipping_method'] : "webpos_shipping_free";
            $payment_method = (isset($orderData['payment_method'])) ? $orderData['payment_method'] : "cashforpos";
            $comment = (isset($orderData['comment'])) ? $orderData['comment'] : "";
            $cashin = (isset($orderData['cashin'])) ? $orderData['cashin'] : 0;
            $remain = (isset($orderData['remain'])) ? $orderData['remain'] : 0;
            $customerInCart = (isset($orderData['customerInCart'])) ? $orderData['customerInCart'] : array();
            $cartData = (isset($orderData['cartData'])) ? $orderData['cartData'] : array();
            $create_shipment = (isset($orderData['create_shipment'])) ? $orderData['create_shipment'] : 0;
            $create_invoice = (isset($orderData['create_invoice'])) ? $orderData['create_invoice'] : 0;
            $posUserDisplayName = (isset($orderData['posUserDisplayName'])) ? $orderData['posUserDisplayName'] : 0;
            $posUserId = (isset($orderData['posUserId'])) ? $orderData['posUserId'] : '';
            $till_id = (isset($orderData['till_id'])) ? $orderData['till_id'] : '';
            $totalPaid = $cashin;
            $totalRefunded = $remain;
            $paidFullAmount = false;
            $posSession->setDiscountValue(0);
            if ($totalRefunded <= 0) {
                $totalRefunded = 0 - $totalRefunded;
            } else {
                $totalRefunded = 0;
            }
            if (!isset($totalPaid)) {
                $totalPaid = 0;
            }
            $posorder->setShippingMethod($shipping_method);
            $posorder->setPaymentMethod($payment_method);
            if (count($customerInCart) > 0) {
                $customerid = $customerInCart['customerid'];
                $billingAddress = $customerInCart['billingAddress'];
                $shippingAddress = $customerInCart['shippingAddress'];
                $customer = $customerModel->load($customerId);
                $defaultCustomer = $posorder->getCustomer();
                $billingDefault = $defaultCustomer->getDefaultBillingAddress();
                $shippingDefault = $defaultCustomer->getDefaultShippingAddress();
                foreach ($billingDefault->getData() as $key => $value) {
                    if ($key == 'customer_id')
                        continue;
                    if (!isset($billingAddress[$key]) || empty($billingAddress[$key])) {
                        $billingAddress[$key] = $value;
                    }
                }
                foreach ($shippingDefault->getData() as $key => $value) {
                    if ($key == 'customer_id')
                        continue;
                    if (!isset($shippingAddress[$key]) || empty($shippingAddress[$key])) {
                        $shippingAddress[$key] = $value;
                    }
                }
                $posorder->setBillingAddress($billingAddress);
                $posorder->setShippingAddress($shippingAddress);
                $posorder->setCustomer($customerid);
            }
            if (count($cartData) > 0) {
                $products = $this->getProductsData($cartData);
                $posorder->createQuote();
                $posorder->addProductsToQuote($products);
                $posorder->setCashinToQuote($totalPaid);
                $orderResult = $posorder->saveOrderFromQuote();
            }
            if (isset($orderResult) && !empty($orderResult)) {
                $orderResult->sendNewOrderEmail();
                $orderId = $orderResult->getId();
                $result['success'] = true;
                $result['orderId'] = $orderResult->getIncrementId();
                $order = $orderResult;

                if (isset($comment) && $comment != '') {
                    $order->addStatusHistoryComment($comment);
                }
				/*
                $create_invoice = Mage::getStoreConfig('webpos/general/auto_create_invoice', Mage::app()->getStore()->getId());
                $create_shipment = Mage::getStoreConfig('webpos/general/auto_create_shipment', Mage::app()->getStore()->getId());
				*/
                $totalDue = $grandTotal - $totalPaid;
                if ($totalDue <= 0) {
                    $totalRefunded = 0 - $totalDue;
                    $paidFullAmount = true;
                    $create_invoice = true;
                } elseif ($payment_method == 'ccforpos') {
                    $order->setData('webpos_ccforpos', $totalDue);
                    $order->setData('webpos_base_ccforpos', $totalDue);
                } elseif ($payment_method == 'cp1forpos') {
                    $order->setData('webpos_cp1forpos', $totalDue);
                    $order->setData('webpos_base_cp1forpos', $totalDue);
                } elseif ($payment_method == 'cp2forpos') {
                    $order->setData('webpos_cp2forpos', $totalDue);
                    $order->setData('webpos_base_cp2forpos', $totalDue);
                } elseif ($payment_method == 'cashforpos' && $totalPaid == 0) {
                    $order->setData('webpos_cash', $order->getGrandTotal());
                    $order->setData('webpos_base_cash', $order->getBaseGrandTotal());
                }

                $user = $posSession->getUser();
                if ($user->getId() != '') {
                    $order->setWebposAdminId($user->getId())
                            ->setWebposAdminName($user->getDisplayName());
                    $userLocationId = $user->getLocationId();
                    if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
                        $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                                ->addFieldToFilter('user_id', $user->getId());
                        $userLocationId = $userCollection->getfirstItem()->getWarehouseId();
                    }
                    $order->setLocationId($userLocationId);
                }
                if (isset($till_id)) {
                    $order->setTillId($till_id);
                }
                $order->save();
                $this->createShipmentAndInvoice($lastOrderId, $order, $create_shipment, $create_invoice);
                $this->saveOrderData($order);
            } else {
                $result['error'] = $saveResult['error'];
            }
            return $result;
        }

        public function createShipmentAndInvoice($orderId, $order, $create_shipment, $create_invoice) {
            if ($order->getId()) {
                $result['error'] = false;
                if ($create_invoice) {
                    try {
                        $invoice = $this->_initInvoice($orderId);
                        if ($invoice) {
                            if (!empty($data['capture_case'])) {
                                $invoice->setRequestedCaptureCase($data['capture_case']);
                            }
                            $invoice->register();
                            $invoice->setEmailSent(true);
                            $invoice->getOrder()->setCustomerNoteNotify(true);
                            $invoice->getOrder()->setIsInProcess(true);
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder());

                            $shipment = false;
                            if ((int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                                $shipment = $this->_prepareShipment($invoice);
                                if ($shipment) {
                                    $shipment->setEmailSent($invoice->getEmailSent());
                                    $transactionSave->addObject($shipment);
                                }
                            }

                            $transactionSave->save();
                            if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('invoice')) {
                                $template_invoice = Mage::helper('webpos/customer')->getWebposEmailTemplate('invoice');
                                if (isset($template_invoice['guest']) && $template_invoice['guest'] != '') {
                                    Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_invoice['guest']);
                                }
                                if (isset($template_invoice['customer']) && $template_invoice['customer'] != '') {
                                    Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $template_invoice['customer']);
                                }
                                $invoice->sendEmail($order->getCustomerEmail(), '');
                            }
                            $invoice_error = false;
                            $invoice_message = $this->__('The invoice has been created.');
                        } else {
                            $invoice_error = true;
                            $invoice_message = $this->__('The invoice is not exist');
                        }
                    } catch (Mage_Core_Exception $e) {
                        $invoice_error = true;
                        $invoice_message = $e->getMessage();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $invoice_error = true;
                        $invoice_message = $this->__('Unable to save the invoice.');
                    }
                }

                if ($create_shipment) {
                    try {
                        $shipment = $this->_initShipment($orderId);
                        if ($shipment) {
                            $shipment->register();
                            $shipment->setEmailSent(true);
                            $shipment->getOrder()->setCustomerNoteNotify(true);
                            $this->_saveShipment($shipment);
                            if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('shipment')) {
                                $template_invoice = Mage::helper('webpos/customer')->getWebposEmailTemplate('shipment');
                                if (isset($template_shipment['guest']) && $template_shipment['guest'] != '') {
                                    Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_shipment['guest']);
                                }
                                if (isset($template_shipment['customer']) && $template_shipment['customer'] != '') {
                                    Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE, $template_shipment['customer']);
                                }
                                $shipment->sendEmail($order->getCustomerEmail());
                            }
                            $shipment_error = false;
                            $shipment_message = $this->__('The shipment has been created.');
                        } else {
                            $shipment_error = true;
                            $shipment_message = $this->__('An error occurred while creating shipment.');
                        }
                    } catch (Mage_Core_Exception $e) {
                        $shipment_error = true;
                        $shipment_message = $e->getMessage();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $shipment_error = true;
                        $shipment_message = $this->__('An error occurred while creating shipment.');
                    }
                }
            }
        }

        public function getProductsData($cartData) {
            $productsInfoStr = $cartData['productInfo'];
            $customOptions = $cartData['options'];
            $bundle_option = $cartData['bundle_option'];
            $bundle_option_qty = $cartData['bundle_option_qty'];

            $customPrice = $this->getRequest()->getParam('customPrice');
            $cart = Mage::getSingleton('checkout/cart');
            $session = $this->getSession();
            $quote = $cart->getQuote();
            $items = $quote->getAllItems();
            $errorMessage = '';
            $requestInfos = array();
            if (isset($productsInfoStr)) {
                $productsInfo = explode(',', $productsInfoStr);
                if (count($productsInfo) > 0) {
                    foreach ($productsInfo as $productInfo) {
                        $productData = array('id' => '', 'isnew' => false, 'params' => array('qty' => 0, 'super_attribute' => array()));
                        $data = explode('$s$', $productInfo);
                        if (count($data) > 1) {
                            $productId_itemId = (int) $data[0];
                            $qty = (int) $data[1];
                            $productData['params']['product'] = $productId_itemId;
                            $productData['id'] = $productId_itemId;
                            $productData['params']['qty'] = $qty;

                            if (isset($data[2])) {
                                $options = explode('|', $data[2]);
                                foreach ($options as $option) {
                                    $optionData = explode('-', $option);
                                    $optionId = $optionData[0];
                                    $optionValue = (int) $optionData[1];
                                    $productData['params']['super_attribute'][$optionId] = $optionValue;
                                }
                            }
                            if (count($customOptions) > 0)
                                foreach ($customOptions as $prdid => $customOption) {
                                    if (count($customOption) > 0 && (int) $prdid == $productId_itemId) {
                                        foreach ($customOption as $optionId => $optionValue) {
                                            $productData['params']['options'][$optionId] = $optionValue;
                                        }
                                        break;
                                    }
                                }
                            if (count($bundle_option) > 0)
                                foreach ($bundle_option as $prdid => $bundleOption) {
                                    if (count($bundleOption) > 0 && (int) $prdid == $productId_itemId) {
                                        foreach ($bundleOption as $optionId => $optionValue) {
                                            $productData['params']['bundle_option'][$optionId] = $optionValue;
                                        }
                                        break;
                                    }
                                }
                            if (count($bundle_option_qty) > 0)
                                foreach ($bundle_option_qty as $prdid => $bundleQty) {
                                    if (count($bundleQty) > 0 && (int) $prdid == $productId_itemId) {
                                        foreach ($bundleQty as $optionId => $qty) {
                                            $productData['params']['bundle_option_qty'][$optionId] = $qty;
                                        }
                                        break;
                                    }
                                }
                            $requestInfos[] = $productData;
                        }
                    }
                }
            }
            $updateItemsData = array();
            $newItemsData = array();
            foreach ($requestInfos as $requestInfo) {
                $newItemsData[] = $requestInfo['params'];
            }
            return $newItemsData;
        }

        //vietdq ship
        public function shipAction() {
            /*
              vietdq permission
             */
            $result = array();
            $error = true;
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $this->getRequest()->getParam('order_id'));
            if ($isManage == false) {
                $result['error'] = $this->__("Access denied! You don't have the permission to process this action.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            /*
              end vietdq
             */
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $order = Mage::getModel('sales/order')->load($orderId);
//Or


            $qty = array();
            foreach ($order->getAllItems() as $eachOrderItem) {

                $Itemqty = 0;
                $Itemqty = $eachOrderItem->getQtyOrdered() - $eachOrderItem->getQtyShipped() - $eachOrderItem->getQtyRefunded() - $eachOrderItem->getQtyCanceled();
                $qty[$eachOrderItem->getId()] = $Itemqty;
            }

            /*
              echo "<pre>";
              print_r($qty);
              echo "</pre>";
             */
            /* check order shipment is prossiable or not */

            $email = true;
            $includeComment = true;
            $comment = "test Shipment";

            if ($order->canShip()) {
                /* @var $shipment Mage_Sales_Model_Order_Shipment */
                /* prepare to create shipment */
                $shipment = $order->prepareShipment($qty);
                if ($shipment) {
                    $shipment->register();
                    $shipment->addComment($comment, $email && $includeComment);
                    $shipment->getOrder()->setIsInProcess(true);
                    try {
                        $transactionSave = Mage::getModel('core/resource_transaction')
                                ->addObject($shipment)
                                ->addObject($shipment->getOrder())
                                ->save();
                        if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('shipment')) {
                            $template_shipment = Mage::helper('webpos/customer')->getWebposEmailTemplate('shipment');
                            if (isset($template_shipment['guest']) && $template_shipment['guest'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_shipment['guest']);
                            }
                            if (isset($template_shipment['customer']) && $template_shipment['customer'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE, $template_shipment['customer']);
                            }
                            $shipment->sendEmail($email, ($includeComment ? $comment : ''));
                        }
                        $result['success'] = true;
                        $result['message'] = $this->__('The shipment has been created !');
                        $orderStatus = $order->getStatus();
                        $statusOrderClass = Mage::helper('webpos')->getOrderClass($orderStatus);
                        $result['orderId'] = $order->getId();
                        $result['statusOrderClass'] = $statusOrderClass;
                        $result['order_status'] = $order->getStatus();
                        $result['order_status_label'] = $order->getStatusLabel();
                        $result['total_due'] = $order->getTotalDue();
                        $result['can_refund'] = $order->canCreditmemo() ? 'true' : 'false';
                    } catch (Mage_Core_Exception $e) {
                        $result['error'] = $e;
                    }
                }
            } else {
                $result['error'] = $this->__('This order can not be shipped !');
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        /**
         * iterate through quote and remove all items
         *           
         * @return nothing
         */
        protected function _removeAllItems($quote) {
            foreach ($quote->getAllItems() as $item) {
                $item->isDeleted(true);
                if ($item->getHasChildren())
                    foreach ($item->getChildren() as $child)
                        $child->isDeleted(true);
            }
            $quote->collectTotals()->save();
        }

        public function createInvoice($orderId) {
            $result = array();
            $error = true;
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $orderId);
            if ($isManage == false) {
                $result['error'] = $this->__("Access denied! You don't have the permission to process this action.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            $order = Mage::getModel('sales/order')->load($orderId);

            if ($orderId && $order && $order->getId()) {

                $invoice = $this->_initInvoice($orderId);
                if ($invoice) {
                    if (!empty($data['capture_case'])) {
                        $invoice->setRequestedCaptureCase($data['capture_case']);
                    }
                    $invoice->register();
                    $invoice->setEmailSent(true);
                    $invoice->getOrder()->setCustomerNoteNotify(true);
                    $invoice->getOrder()->setIsInProcess(true);
                    $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());

                    $shipment = false;
                    if ((int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                        $shipment = $this->_prepareShipment($invoice);
                        if ($shipment) {
                            $shipment->setEmailSent($invoice->getEmailSent());
                            $transactionSave->addObject($shipment);
                        }
                    }
                    try {
                        $transactionSave->save();
                        if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('invoice')) {
                            $template_invoice = Mage::helper('webpos/customer')->getWebposEmailTemplate('invoice');
                            if (isset($template_invoice['guest']) && $template_invoice['guest'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_invoice['guest']);
                            }
                            if (isset($template_invoice['customer']) && $template_invoice['customer'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $template_invoice['customer']);
                            }
                            $invoice->sendEmail($order->getCustomerEmail(), '');
                        }
                        $error = false;
                        $html = $this->__('The invoice has been created.');
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $error = false;
                        $html = $this->__('Unable to send the invoice email.');
                    }
                }
            }
            $this->createShip($orderId);
        }

        public function createShip($orderId) {
            $result = array();
            $error = true;
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $isManage = Mage::helper('webpos/permission')->canManageOrder($userId, $orderId);
            if ($isManage == false) {
                $result['error'] = $this->__("Access denied! You don't have the permission to process this action.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            $order = Mage::getModel('sales/order')->load($orderId);
            $qty = array();
            foreach ($order->getAllItems() as $eachOrderItem) {
                $Itemqty = 0;
                $Itemqty = $eachOrderItem->getQtyOrdered() - $eachOrderItem->getQtyShipped() - $eachOrderItem->getQtyRefunded() - $eachOrderItem->getQtyCanceled();
                $qty[$eachOrderItem->getId()] = $Itemqty;
            }
            $email = true;
            $includeComment = true;
            $comment = "POS Shipment";

            if ($order->canShip()) {
                $shipment = $order->prepareShipment($qty);
                if ($shipment) {

                    $shipment->register();
                    $shipment->addComment($comment, $email && $includeComment);
                    $shipment->getOrder()->setIsInProcess(true);
                    try {
                        $transactionSave = Mage::getModel('core/resource_transaction')
                                ->addObject($shipment)
                                ->addObject($shipment->getOrder())
                                ->save();
                        if (Mage::helper('webpos/customer')->isEnableAutoSendEmail('shipment')) {
                            $template_shipment = Mage::helper('webpos/customer')->getWebposEmailTemplate('shipment');
                            if (isset($template_shipment['guest']) && $template_shipment['guest'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE, $template_shipment['guest']);
                            }
                            if (isset($template_shipment['customer']) && $template_shipment['customer'] != '') {
                                Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE, $template_shipment['customer']);
                            }
                            $shipment->sendEmail($email, ($includeComment ? $comment : ''));
                        }
                    } catch (Mage_Core_Exception $e) {
                        $result['error'] = $e;
                    }
                }
            } else {
                $result['error'] = $this->__('This order can not be shipped !');
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }

    }
    