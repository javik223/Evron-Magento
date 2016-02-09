<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Report_Xreport extends Mage_Adminhtml_Block_Template {

    private $_till_id = 0;

    public function __construct($till_id = 0) {
        $this->setTemplate('webpos/webpos/reports/xreport.phtml');
        $this->_till_id = $till_id;
        $till = Mage::getModel('webpos/session')->getTill();
        if ($till->getId()) {
            $this->_till_id = $till->getTillId();
        }
        parent::_construct();
    }

    public function getStoreName() {
        $storeId = Mage::app()->getStore()->getId();
        $storeName = Mage::getModel('core/store')->load($storeId)->getData('name');
        return $storeName;
    }

    public function getPaymentPaidInfo() {
        $data = array();
        $storeId = Mage::app()->getStore()->getId();
        $userId = Mage::getSingleton('webpos/session')->getUser()->getUserId();
        $till_id = $this->_till_id;
        $enableTill = Mage::getStoreConfig('webpos/general/enable_tills', $storeId);

        if ($storeId == 'NULL') {
            $report_collec = Mage::getModel('webpos/report')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => 0))
                    ->addFieldToFIlter('user_id', $userId);
            if ($enableTill == true) {

                $report_collec->addFieldToFIlter('till_id', $till_id);
            }
            $report_collec->addOrder('report_id', 'DESC');
        } else {
            $report_collec = Mage::getModel('webpos/report')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $storeId))
                    ->addFieldToFIlter('user_id', $userId);
            if ($enableTill == true) {

                $report_collec->addFieldToFIlter('till_id', $till_id);
            }
            $report_collec->addOrder('report_id', 'DESC');
        }


        if (count($report_collec) > 0) {
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        } else
            $previous_time = '2015-12-28 00:00:00';
        if ($storeId != 'NULL') {
            $collection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('created_at', array('from' => $previous_time))
                    ->addAttributeToFilter('store_id', array('eq' => $storeId))
                    ->addFieldToFilter('status', 'complete')
                    ->addFieldToFIlter('webpos_admin_id', $userId);
            if ($enableTill == true) {
                $collection->addFieldToFIlter('till_id', $till_id);
            }
            $collection->load();
        } else {
            $collection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('created_at', array('from' => $previous_time))
                    ->addAttributeToFilter('store_id', array('null' => true))
                    ->addFieldToFilter('status', 'complete')
                    ->addFieldToFIlter('webpos_admin_id', $userId);
            if ($enableTill == true) {
                $collection->addFieldToFIlter('till_id', $till_id);
            }
            $collection->load();
        }
        $data['other_payment']['num_order_total'] = count($collection);
        $data['other_payment']['previous_time'] = $previous_time;
        $data['other_payment']['grand_order_total'] = 0;
        $data['other_payment']['tax_order_total'] = 0;
        $data['other_payment']['total_refund'] = 0;
        $payment_arr = array();
        $data['other_payment']['money_system'] = 0;
        $data['other_payment']['order_count'] = 0;
        $data['other_payment']['transac_in'] = 0;
        $data['other_payment']['transac_out'] = 0;


        if ($storeId == 'NULL') {
            $transacs = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => 0))
                    ->addFieldToFilter('transac_flag', array('eq' => '1'))
                    ->addFieldToFilter('user_id', $userId)
                    ->addFieldToFilter('created_time', array('from' => $previous_time));
            if ($enableTill == true) {
                $transacs->addFieldToFilter('till_id', $till_id);
            }
            $transacs->addOrder('transaction_id', 'DESC');
        } else {
            $transacs = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $storeId))
                    ->addFieldToFilter('user_id', $userId)
                    ->addFieldToFilter('transac_flag', array('eq' => '1'))
                    ->addFieldToFilter('created_time', array('from' => $previous_time));
            if ($enableTill == true) {
                $transacs->addFieldToFilter('till_id', $till_id);
            }
            $transacs->addOrder('transaction_id', 'DESC');
        }

        $real_current_balance = $transacs->getFirstItem()->getData('current_balance');
        $transacs = Mage::getModel('webpos/transaction')->getCollection()
                ->addFieldToFilter('store_id', array('eq' => $storeId))
                ->addFieldToFilter('user_id', $userId)
                ->addFieldToFilter('transac_flag', array('eq' => '1'))
                ->addFieldToFilter('created_time', array('from' => $previous_time));
        if ($enableTill == true) {
            $transacs->addFieldToFilter('till_id', $till_id);
        }
        $transacs->addFieldToFilter('order_id', 'Manual')
                ->addOrder('transaction_id', 'DESC');
        $previous_transfer = $transacs->getLastItem()->getData('current_balance');
        if ($storeId == 'NULL') {
            $transac_collection = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => 0))
                    ->addFieldToFilter('order_id', array('eq' => 'Manual'))
                    ->addFieldToFIlter('user_id', $userId)
                    ->addFieldToFilter('transac_flag', array('eq' => '1'))
                    ->addFieldToFilter('created_time', array('from' => $previous_time));
            if ($enableTill == true) {
                $transac_collection->addFieldToFIlter('till_id', $till_id);
            }
            $transac_collection->addOrder('transaction_id', 'ASC');
        } else {
            $transac_collection = Mage::getModel('webpos/transaction')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $storeId))
                    ->addFieldToFilter('order_id', array('eq' => 'Manual'))
                    ->addFieldToFIlter('user_id', $userId)
                    ->addFieldToFilter('transac_flag', array('eq' => '1'))
                    ->addFieldToFilter('created_time', array('from' => $previous_time));
            if ($enableTill == true) {
                $transac_collection->addFieldToFIlter('till_id', $till_id);
            }
            $transac_collection->addOrder('transaction_id', 'ASC');
        }

        $current_balance = $transac_collection->getFirstItem()->getData('current_balance');
        foreach ($transac_collection as $transaction) {
            if ($transaction->getData('type') == 'in') {
                $data['other_payment']['transac_in'] += floatval($transaction->getData('cash_in'));
            } else {
                $data['other_payment']['transac_out'] += floatval($transaction->getData('cash_out'));
            }
        }

        if ($previous_time != '2015-12-28 00:00:00')
            $amount_diff = $data['other_payment']['transac_in'] - $data['other_payment']['transac_out'] - $previous_transfer;
        else {
            $amount_diff = $data['other_payment']['transac_in'] - $data['other_payment']['transac_out'];
            $previous_transfer = 0;
        }

        if (count($collection) > 0)
            $webpos_payment_methods = array('cashforpos', 'ccforpos', 'cp1forpos', 'cp2forpos');
        foreach ($collection as $order) {
            $data['other_payment']['grand_order_total'] += floatval($order->getData('base_grand_total'));
            $data['other_payment']['tax_order_total'] += floatval($order->getData('base_tax_amount'));
            $payment = $order->getPayment()->getMethod();
            if (in_array($payment, $webpos_payment_methods) || $payment == 'paypal_direct') {
                switch ($payment) {
                    case 'ccforpos' :
                        $data[$payment]['payment_name'] = Mage::helper('webpos/payment')->getCcMethodTitle();
                        break;
                    case 'cp1forpos' :
                        $data[$payment]['payment_name'] = Mage::helper('webpos/payment')->getCp1MethodTitle();
                        break;
                    case 'cp2forpos' :
                        $data[$payment]['payment_name'] = Mage::helper('webpos/payment')->getCp2MethodTitle();
                        break;
                    case 'cashforpos':
                        $data[$payment]['payment_name'] = Mage::helper('webpos/payment')->getCashMethodTitle();
                        break;
                    case 'paypal_direct':
                        $data[$payment]['payment_name'] = 'Paypal';
                        if (isset($data[$payment]['money_system'])) {
                            $data[$payment]['money_system'] += floatval($order->getData('base_grand_total'));
                        } else {
                            $data[$payment]['money_system'] = floatval($order->getData('base_grand_total'));
                        }
                        if (isset($data[$payment]['order_count'])) {
                            $data[$payment]['order_count'] ++;
                        } else {
                            $data[$payment]['order_count'] = 1;
                        }
                        break;
                    default:
                        break;
                }
                foreach ($webpos_payment_methods as $method_code) {
                    $db_field_name = ($method_code == 'cashforpos') ? 'webpos_base_cash' : 'webpos_base_' . $method_code;
                    $amountPaid = $order->getData($db_field_name);
                    if ($method_code == 'cashforpos') {
                        $amountChange = $order->getData('webpos_base_change');
                        $amountPaid = $amountPaid - $amountChange;
                    }
                    if (isset($amountPaid) && $amountPaid > 0) {
                        if (in_array($method_code, $payment_arr)) {
                            $data[$method_code]['money_system'] += floatval($amountPaid);
                            $data[$method_code]['order_count'] += 1;
                        } else {
                            $data[$method_code]['money_system'] = floatval($amountPaid);
                            $data[$method_code]['order_count'] = 1;
                        }
                        array_push($payment_arr, $method_code);
                    } elseif ($payment == 'cashforpos' && $method_code == 'cashforpos') {
                        if (in_array($payment, $payment_arr)) {
                            $data[$payment]['money_system'] += floatval($order->getData('base_grand_total'));
                            $data[$payment]['order_count'] += 1;
                        } else {
                            $data[$payment]['money_system'] = floatval($order->getData('base_grand_total'));
                            $data[$payment]['order_count'] = 1;
                        }
                        array_push($payment_arr, 'cashforpos');
                    }
                }
            } else {
                $cashforpos = $order->getData('webpos_base_cash');
                if (isset($cashforpos) && $cashforpos > 0) {
                    if (isset($cashforpos) && $cashforpos > 0) {
                        if (in_array('cashforpos', $payment_arr)) {
                            $data['cashforpos']['money_system'] += floatval($cashforpos);
                            $data['cashforpos']['order_count'] += 1;
                        } else {
                            $data['cashforpos']['money_system'] = floatval($cashforpos);
                            $data['cashforpos']['order_count'] = 1;
                        }
                        array_push($payment_arr, 'cashforpos');
                    }
                    $data['other_payment']['payment_name'] = Mage::helper('webpos')->__('Other Payments');
                    $data['other_payment']['money_system'] = floatval($order->getData('base_grand_total')) - floatval($cashforpos);
                    $data['other_payment']['order_count'] = 1;
                } else {
                    $data['other_payment']['payment_name'] = Mage::helper('webpos')->__('Other Payments');
                    $data['other_payment']['money_system'] = floatval($order->getData('base_grand_total'));
                    $data['other_payment']['order_count'] = 1;
                }
            }
            array_push($payment_arr, $payment);
        }
        if ($storeId != 'NULL') {
            $refundCollection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('updated_at', array('from' => $previous_time))
                    ->addAttributeToFilter('store_id', array('eq' => $storeId));
            if ($enableTill == true) {
                $refundCollection->addFieldToFIlter('till_id', $till_id);
            }
            $refundCollection->addFieldToFIlter('webpos_admin_id', $userId)
                    ->load();
        } else {
            $refundCollection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('updated_at', array('from' => $previous_time));
            if ($enableTill == true) {
                $refundCollection->addFieldToFIlter('till_id', $till_id);
            }
            $refundCollection->addFieldToFIlter('webpos_admin_id', $userId)
                    ->load();
        }
        if (count($refundCollection) > 0) {
            foreach ($refundCollection as $order) {
                $data['other_payment']['total_refund'] += floatval($order->getData('base_total_refunded'));
            }
        }

        if (isset($data['cashforpos']['money_system']))
            $total = $data['cashforpos']['money_system'];
        else
            $total = $current_balance;
        $result = $total + $amount_diff;
        $data['cashforpos']['payment_name'] = 'Cash';
        $data['cashforpos']['money_system'] = $result;
        $data['cashforpos']['total'] = $total;
        $data['cashforpos']['in_out'] = $amount_diff;
        $data['cashforpos']['previous_transfer'] = $previous_transfer;
        $data['other_payment']['grand_order_total'] += $previous_transfer + $amount_diff;
        $data['other_payment']['till_current'] = $real_current_balance;

        Mage::getSingleton('adminhtml/session')->setPaymentInfo($data);
        return $data;
    }

    public function getDiscountPaidInfo() {
        $till_id = $this->_till_id;
        $userId = Mage::getSingleton('webpos/session')->getUser()->getUserId();
        $storeId = Mage::app()->getStore()->getId();
        $report_collec = Mage::getModel('webpos/report')->getCollection();
        $enableTill = Mage::getStoreConfig('webpos/general/enable_tills', $storeId);
        if ($enableTill == true) {
            $report_collec->addFieldToFIlter('till_id', $till_id);
        }
        $report_collec->addOrder('report_id', 'DESC');
        if (count($report_collec) > 0) {
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        } else
            $previous_time = '2015-12-28 00:00:00';

        $data = array();
        $data['discount_amount'] = 0;
        $data['order_count'] = 0;
        $data['voucher'] = 0;
        $data['voucher_orders'] = 0;

        if ($storeId == 'NULL') {
            $collection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addFieldToFIlter('webpos_admin_id', $userId);
            if ($enableTill == true) {
                $collection->addFieldToFIlter('till_id', $till_id);
            }
            $collection->addFieldToFilter('status', 'complete')
                    ->addFieldToFilter('base_discount_amount', array('neq' => '0'))
                    ->addAttributeToFilter('created_at', array('from' => $previous_time))
                    ->load();
        } else {
            $collection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('store_id', array('eq' => $storeId))
                    ->addFieldToFilter('base_discount_amount', array('neq' => '0'))
                    ->addFieldToFIlter('webpos_admin_id', $userId);
            if ($enableTill == true) {
                $collection->addFieldToFIlter('till_id', $till_id);
            }
            $collection->addFieldToFilter('status', 'complete')
                    ->addAttributeToFilter('created_at', array('from' => $previous_time))
                    ->load();
        }
        if (count($collection) > 0)
            foreach ($collection as $order) {
                if (Mage::getEdition() == "Enterprise") {
                    if ((float) $order->getData('base_customer_balance_amount') || (float) $order->getData('base_gift_cards_amount')) {
                        $data['voucher_orders'] ++;
                    }
                    $data['voucher'] += floatval($order->getData('base_customer_balance_amount'));
                    $data['voucher'] += floatval($order->getData('base_gift_cards_amount'));
                }

                $data['discount_amount'] += floatval($order->getData('base_discount_amount'));

                $data['order_count'] ++;
            }

        Mage::getSingleton('adminhtml/session')->setDiscountInfo($data);
        return $data;
    }

}
