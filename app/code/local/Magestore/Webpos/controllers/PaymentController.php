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
class Magestore_Webpos_PaymentController extends Magestore_Webpos_Controller_Action {

    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function saveDataAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $helperPayment = Mage::helper('webpos/payment');
        $cashin = $this->getRequest()->getParam('cashin');
        $shipping_method = $this->getRequest()->getPost('shipping_method', 'webpos_shipping_free');
        $payment_method = $this->getRequest()->getPost('payment_method', 'cashforpos');
        if ((empty($shipping_method) || $shipping_method == '') && $helperPayment->isWebposShippingEnabled())
            $shipping_method = 'webpos_shipping_free';
        if ((empty($payment_method) || $payment_method == '') && $helperPayment->isCashPaymentEnabled())
            $payment_method = 'cashforpos';
        try {
            $this->getOnepage()->saveShippingMethod($shipping_method);
        } catch (Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        try {
            $payment = $this->getRequest()->getPost('payment', array());
            $payment['method'] = $payment_method;
            Mage::helper('webpos')->savePaymentMethod($payment);
        } catch (Exception $e) {
            if (!isset($result['errorMessage']))
                $result['errorMessage'] = '';
            $result['errorMessage'] .= $e->getMessage();
        }
        try {
            if (isset($cashin) && $cashin != '') {
                Mage::getModel('webpos/session')->setWebposCash($cashin);
            } else {
                Mage::getModel('webpos/session')->setWebposCash(null);
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
        } catch (Exception $e) {
            if (!isset($result['errorMessage']))
                $result['errorMessage'] = '';
            $result['errorMessage'] .= "<br />" . $e->getMessage();
        }
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();
        $grandTotal = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
        $result['grandTotals'] = Mage::app()->getStore()->formatPrice($grandTotal);
//        $downgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50);
//        $upgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50 + 50);
        $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
        $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
        $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal, ".");
        $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal, ".");

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function checkNetworkAction() {
        die('success');
    }

    public function holdOrderAction() {
        $onepage = $this->getOnepage();
        try {
            $shipping_method = $this->getRequest()->getParam('shipping_method');
            $comment = $this->getRequest()->getParam('comment');
            $posSession = Mage::getModel('webpos/session');
            $user = $posSession->getUser();
            $payment = array();
            if (!$shipping_method)
                $shipping_method = 'webpos_shipping_free';
            $onepage->saveShippingMethod($shipping_method);
            $payment_method = 'cashforpos';
            $payment['method'] = $payment_method;
            $onepage->getQuote()->getPayment()->importData($payment);
            $onepage->saveOrder();
            $lastOrderId = $onepage->getCheckout()->getLastOrderId();
            $order = Mage::getModel('sales/order')->load($lastOrderId);
            $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true);
            $order->setStatus('holded');
            if ($user->getId() != '') {
                $order->setWebposAdminId($user->getId())
                        ->setWebposAdminName($user->getDisplayName());
            }
            if ($comment) {
                $order->addStatusHistoryComment($comment, $order->getStatus());
                $order->setCustomerNote($comment);
            }
            $order->save();
            $result = array();
            $result['order_id'] = $order->getId();
        } catch (Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        try {
            Mage::helper('webpos')->emptyPOSdata();
        } catch (Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        /*
        $grandTotal = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
        $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
        $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
        $result['grand_total'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
        $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
        $result['number_item'] = Mage::helper('checkout/cart')->getSummaryCount();
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();
        $result['pos_items'] = $this->getLayout()->createBlock('webpos/cart_items')
                ->setTemplate('webpos/webpos/cart/items.phtml')
                ->toHtml();
        */
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function reloadOrderAction() {
        $result = array();
        $holded_key = Mage::app()->getRequest()->getParam('holded_key');
        $customerId = Mage::app()->getRequest()->getParam('customer_id');
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        if ($order) {
            $checkoutSession = Mage::getSingleton('checkout/session');
            try {
                $reloading = $checkoutSession->getData('reloading_order_id');
                if (isset($reloading) && $reloading != null && $reloading != '') {
                    Mage::helper('webpos')->emptyPOSdata();
                }
            } catch (Exception $e) {
                
            }
            try {
                $checkoutSession->setData('reloading_order_id', $order_id);
                $checkoutSession->setData('holded_key', $holded_key);
                $result['success'] = true;
            } catch (Exception $e) {
                $result['errorMessage'] = $e->getMessage();
            }
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    Mage::getModel('checkout/cart')->addOrderItem($item);
                } catch (Mage_Core_Exception $e) {
                    if ($checkoutSession->getUseNotice(true)) {
                        $checkoutSession->addNotice($e->getMessage());
                    } else {
                        $checkoutSession->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    $checkoutSession->addException($e, Mage::helper('webpos')->__('Cannot add the item to shopping cart.'));
                }
            }
			$coupon_code = $order->getData('coupon_code');
			if($coupon_code){
				try{
					$checkoutSession->setData("coupon_code",$coupon_code);
					Mage::getModel('checkout/cart')->getQuote()->setCouponCode($coupon_code);
				}catch(Exception $e){
					$checkoutSession->addException($e);
				}
			}
			$shipping_method = $order->getShippingMethod();
			if($shipping_method){
				try{
					$this->getOnepage()->saveShippingMethod($shipping_method);
				}catch(Exception $e){
					$checkoutSession->addException($e);
				}
			}
            Mage::getModel('checkout/cart')->save();

            $hasError = Mage::getBlockSingleton('checkout/cart')->hasError();
            if ($hasError == true) {
                $result['hasError'] = true;
            }
            $defaultCustomerId = Mage::helper('webpos/customer')->getDefaultCustomerId();
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customerId && $customer) {
                Mage::getModel('customer/session')->setCustomerAsLoggedIn($customer);
                if ($defaultCustomerId != $customerId) {
                    $result['customer_name'] = $customer->getFirstname() . " " . $customer->getLastname();
                    $result['customer_email'] = $customer->getEmail();
                }
            }
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function cancelHoldedOrderAction() {
        $result = array();
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        if ($order->getId()) {
            try {
                $order->unhold()->save();
                $order->cancel()->save();
                $result['success'] = true;
            } catch (Exception $e) {
                $result['errorMessage'] = $e->getMessage();
            }
        }
        $reloading_order_id = Mage::getModel('checkout/session')->getData('reloading_order_id');
        if ($reloading_order_id) {
            try {
                if (Mage::helper('persistent/session')->isPersistent()) {
                    Mage::helper('persistent/session')->getSession()->removePersistentCookie();
                    Mage::getSingleton('persistent/observer')->setQuoteGuest();
                }
                Mage::getModel('checkout/session')->clear();
                Mage::getSingleton('checkout/cart')->truncate();
                Mage::getSingleton('checkout/cart')->save();
                $customerSession = Mage::getSingleton('customer/session');
                if ($customerSession->isLoggedIn()) {
                    $customer = Mage::getModel('customer/customer')->load(0);
                    $customerSession->setCustomerAsLoggedIn($customer);
                };
                Mage::getModel('checkout/session')->setData('reloading_order_id', null);
                Mage::getModel('checkout/session')->setData('holded_key', null);
                Mage::getSingleton('webpos/session')->setCustomDiscount(null);
                Mage::getSingleton('webpos/session')->setType(null);
                Mage::getSingleton('webpos/session')->setDiscountValue(null);
                Mage::getSingleton('webpos/session')->setDiscountName(null);
            } catch (Exception $e) {
                
            }
            $grandTotal = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
            $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
            $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
            $result['grand_total'] = Mage::app()->getStore()->formatPrice($grandTotal);
            $result['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
            $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
            $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
            $result['number_item'] = Mage::helper('checkout/cart')->getSummaryCount();
            $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                    ->setTemplate('webpos/webpos/shipping_method.phtml')
                    ->toHtml();
            $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                    ->setTemplate('webpos/webpos/payment_method.phtml')
                    ->toHtml();
            $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                    ->setTemplate('webpos/webpos/review/totals.phtml')
                    ->toHtml();
            $result['pos_items'] = $this->getLayout()->createBlock('webpos/cart_items')
                    ->setTemplate('webpos/webpos/cart/items.phtml')
                    ->toHtml();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function getOnholdListAction() {
        $block = Mage::getBlockSingleton('webpos/admin_orderlist_holdedlist');
        $this->getResponse()->setBody(json_encode($block->toHtml()));
    }

    public function loadMoreHoldedOrderAction() {
        $block = Mage::getBlockSingleton('webpos/admin_orderlist_moreholdedlist');
        $this->getResponse()->setBody(json_encode($block->toHtml()));
    }

    public function saveTillAction() {
        $result = array();
        try {
            $till_id = $this->getRequest()->getParam('till_id');
            $till = Mage::getModel('webpos/till')->load($till_id);
            if ($till->getId()) {
                Mage::getModel('webpos/session')->setTill($till);
            }
        } catch (Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function getEndOfDayReportAction() {
        $till_id = $this->getRequest()->getParam('till_id');
        if (isset($till_id)) {
            Mage::helper('webpos')->setTillData($till_id);
        }
        $block = Mage::getBlockSingleton('webpos/report_eodreport');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function getXreportAction() {
        $till_id = $this->getRequest()->getParam('till_id');
        if (isset($till_id)) {
            Mage::helper('webpos')->setTillData($till_id);
        }
        $block = Mage::getBlockSingleton('webpos/report_xreport');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function getZreportAction() {
        $till_id = $this->getRequest()->getParam('till_id');
        if (isset($till_id)) {
            Mage::helper('webpos')->setTillData($till_id);
        }
        $block = Mage::getBlockSingleton('webpos/report_xreport')->setTemplate('webpos/webpos/reports/zreport.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function getCurrentBalanceAction($type = 'json') {
        $store_id = $this->getRequest()->getParam('store_id');
        $till_id = $this->getRequest()->getParam('till_id');
        $current_user = Mage::getSingleton('webpos/session')->getUser();
        if (isset($till_id)) {
            Mage::helper('webpos')->setTillData($till_id);
        }
        $return = Mage::getSingleton('webpos/transaction')->currentBalance($store_id, $current_user['user_id'], $till_id);
        if ($type == 'json')
            return $this->getResponse()->setBody(json_encode($return));
    }

    public function getTransactionGridAction() {
        $till_id = $this->getRequest()->getParam('till_id');
        if (isset($till_id)) {
            Mage::helper('webpos')->setTillData($till_id);
        }
        $block = Mage::getBlockSingleton('webpos/cashdrawer_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function newTransactionAction() {
        $current_user = Mage::getSingleton('webpos/session')->getUser();
        $storeId = Mage::app()->getStore()->getId();
        $amount = $this->getRequest()->getParam('amount');
        $note = $this->getRequest()->getParam('note');
        $note = trim(strip_tags($note));
        $type = $this->getRequest()->getParam('type');
        $till_id = $this->getRequest()->getParam('till_id');
        if (isset($till_id)) {
            Mage::helper('webpos')->setTillData($till_id);
        }
        $userLocationId = Mage::helper('webpos/user')->getCurrentUserLocationId();
        $result = false;
        if (
                is_numeric($amount) &&
                $amount >= 0 &&
                in_array($type, array(
                    'in',
                    'out')
                )
        ) {

            $data = array(
                'amount' => $amount,
                'user_id' => $current_user['user_id'],
                'type' => $type,
                'store_id' => $storeId,
                'till_id' => $till_id,
                'location_id' => $userLocationId,
                'note' => $note,
            );

            $result = Mage::getModel('webpos/transaction')->saveTransactionData($data);
        }
        if ($result == false) {
            $result = array(
                'msg' => Mage::helper('webpos')->__('Can NOT save this transaction. Please recheck the input value or contact with your administrator'),
                'error' => true);
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    public function openCashTransferAction() {
        $current_user = Mage::getSingleton('webpos/session')->getUser();
        $user_name = $current_user['display_name'];
        $now_time = Mage::getModel('core/date')->timestamp(time());
        echo "### " . date('d/m/Y H:i ', $now_time) . " Manual Opening of Cash Drawer " . "- " . $user_name . " ###";
    }

    public function reportprintAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveZReportAction() {
        $data = Mage::getSingleton('adminhtml/session')->getPaymentInfo();
        $refund_amount = $data['other_payment']['total_refund'];
        $store_id = Mage::app()->getStore()->getId();
        $cashier_id = Mage::getSingleton('webpos/session')->getUser()->getUserId();
        $order_total = $this->getRequest()->getParam('order_total');
        $amount_total = $this->getRequest()->getParam('amount_total');
        $transfer_amount = $this->getRequest()->getParam('transfer_amount');
        $tax_amount = $this->getRequest()->getParam('tax_amount');
        $discount_amount = $this->getRequest()->getParam('discount_amount');
        $cash_system = $this->getRequest()->getParam('cash_system');
        $cash_count = $this->getRequest()->getParam('cash_count');
        $cc_system = $this->getRequest()->getParam('cc_system');
        $cc_count = $this->getRequest()->getParam('cc_count');
        $other_system = $this->getRequest()->getParam('other_system');
        $other_count = $this->getRequest()->getParam('other_count');
        $till_id = ($this->getRequest()->getParam('till_id')) ? $this->getRequest()->getParam('till_id') : 0;
        $location_id = Mage::helper('webpos/user')->getCurrentUserLocationId();
//        $create_time = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $create_time = date("Y-m-d H:i:s", time());
        $amount = (float) $this->getRequest()->getParam('amount');
        $type = $this->getRequest()->getParam('type');
        $note = $this->getRequest()->getParam('note');
        $errorMessage = '';
        try {
            $this->setTransacsionFlag($till_id, $store_id);
        } catch (Exception $e) {
            $errorMessage .= $e->getMessage() . '<br/>';
        }
        try {
            if (isset($amount) && $amount > 0) {
                $this->transferToCashDrawer($till_id, $amount, $type, $note);
            }
        } catch (Exception $e) {
            $errorMessage .= $e->getMessage() . '<br/>';
        }

        $data = array();
        $data['refund_amount'] = $refund_amount;
        $data['created_time'] = $create_time;
        $data['store_id'] = $store_id;
        $data['user_id'] = $cashier_id;
        $data['order_total'] = $order_total;
        $data['amount_total'] = $amount_total;
        $data['transfer_amount'] = $transfer_amount;
        $data['tax_amount'] = $tax_amount;
        $data['discount_amount'] = $discount_amount;
        $data['cash_system'] = $cash_system;
        $data['cash_count'] = $cash_count;
        $data['cc_system'] = $cc_system;
        $data['cc_count'] = $cc_count;
        $data['other_system'] = $other_system;
        $data['other_count'] = $other_count;
        $data['till_id'] = $till_id;
        $data['location_id'] = $location_id;
        $model = Mage::getModel('webpos/report');
        $model->setData($data);
        try {
            $model->save();
            $result['success'] = true;
        } catch (Exception $e) {
            $errorMessage .= $e->getMessage() . '<br/>';
        }
        if ($errorMessage != '') {
            $result['error'] = $errorMessage;
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function setTransacFlagAction() {
        $store_id = $this->getRequest()->getParam('store_id');
        $till_id = $this->getRequest()->getParam('till_id');
        if ($store_id == 'NULL' || $store_id == "") {
            $transac_collection = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => 0))
                    ->addFieldToFilter('till_id', array('eq' => $till_id))
                    ->addFieldToFilter('transac_flag', array('eq' => '1'));
        } else {
            $transac_collection = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $store_id))
                    ->addFieldToFilter('till_id', array('eq' => $till_id))
                    ->addFieldToFilter('transac_flag', array('eq' => '1'));
        }

        foreach ($transac_collection as $transac) {
            $transac->setData('transac_flag', '0');
            $transac->save();
        }

        $result = array();
        $this->getResponse()->setBody(json_encode($result));
    }

    public function setTransacsionFlag($till_id, $store_id = 'NULL') {
        if ($store_id == 'NULL' || $store_id == "") {
            $transac_collection = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => 0))
                    ->addFieldToFilter('till_id', array('eq' => $till_id))
                    ->addFieldToFilter('transac_flag', array('eq' => '1'));
        } else {
            $transac_collection = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $store_id))
                    ->addFieldToFilter('till_id', array('eq' => $till_id))
                    ->addFieldToFilter('transac_flag', array('eq' => '1'));
        }

        foreach ($transac_collection as $transac) {
            $transac->setData('transac_flag', '0');
            $transac->save();
        }
    }

    public function transferToCashDrawer($till_id, $amount, $type, $note) {
        $current_user = Mage::getSingleton('webpos/session')->getUser();
        $storeId = Mage::app()->getStore()->getId();
        $note = trim(strip_tags($note));
        $till = Mage::getSingleton('webpos/session')->getTill();
        if ($till->getId()) {
            $till_id = $till->getTillId();
        }
        $userLocationId = Mage::helper('webpos/user')->getCurrentUserLocationId();
        $result = false;
        if (
                is_numeric($amount) &&
                $amount >= 0 &&
                in_array($type, array(
                    'in',
                    'out')
                )
        ) {

            $data = array(
                'amount' => $amount,
                'user_id' => $current_user['user_id'],
                'type' => $type,
                'store_id' => $storeId,
                'till_id' => $till_id,
                'location_id' => $userLocationId,
                'note' => $note,
            );
            Mage::getModel('webpos/transaction')->saveTransactionData($data);
        }
    }

    public function toogleTaxAction() {
        $tax_off = Mage::getModel('webpos/session')->getData('tax_off');
        if (!isset($tax_off) || $tax_off == false) {
            Mage::getModel('webpos/session')->setData('tax_off', true);
        } else {
            Mage::getModel('webpos/session')->setData('tax_off', false);
        }
    }

}
