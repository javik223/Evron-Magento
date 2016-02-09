<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Model_Transaction extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('webpos/transaction');
    }

    public function currentBalance($store_id, $userId, $tillId) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $catalogResource = Mage::getModel('catalog/product')->getResource();

        if ($store_id == NULL && $store_id == '') {
            $store_id = 0;
        }
        if ($tillId == NULL && $tillId == '') {
            $tillId = 0;
        }
        $current_balance = $readConnection->fetchOne('SELECT current_balance FROM ' . $catalogResource->getTable('webpos_transaction') . ' WHERE store_id = ' . $store_id . ' AND user_id = ' . $userId . ' AND till_id = ' . $tillId . ' AND transac_flag =  1   ORDER BY transaction_id DESC LIMIT 1');
        $_store = Mage::app()->getStore($store_id);
        $current_balance = $_store->convertPrice($current_balance, true, false);
        $return = array();
        $return['msg'] = $current_balance;
        return $return;
    }

    public function saveTransactionData($data) {
        $return = array(
            'msg' => 'Error! Please recheck the form OR contact administrator for more details.',
            'error' => true);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName('webpos_transaction');
        $store_id = $data['store_id'];
        if ($store_id == "" || $store_id == NULL) {
            $store_id = 0;
        }

        $current_balance = "SELECT `current_balance` FROM " . $tableName . " WHERE  `store_id` = '" . $store_id . "' AND `user_id` = '" . $data['user_id'] . "' AND `till_id` = '" . $data['till_id'] . "' AND `location_id` = '" . $data['location_id'] . "' ORDER BY `transaction_id` DESC LIMIT 1";
        $current_balance = $readConnection->fetchOne($current_balance);

        $previous_balance = "SELECT `previous_balance` FROM " . $tableName . " WHERE  `store_id` = '" . $store_id . "' AND `user_id` = '" . $data['user_id'] ."' AND `till_id` = '" . $data['till_id'] . "' AND `location_id` = '" . $data['location_id'] . "' ORDER BY `transaction_id` DESC LIMIT 1";
        $previous_balance = $readConnection->fetchOne($previous_balance);
        $now = date('Y-m-d H:i:s');

        switch ($data['type']) {
            case 'in':
                $previous_balance = $current_balance;
                $current_balance += $data['amount'];
                if (!isset($data['user_id']))
                    $data['user_id'] = NULL;
                $query = 'INSERT INTO ' . $tableName . " (`cash_in`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`, `user_id`, `payment_method`, `comment`,  `store_id`, `transac_flag`, `till_id`, `location_id`) VALUE ('" . $data['amount'] . "', 'in','" . $now . "', 'Manual','" . $previous_balance . "','" . $current_balance . "', '" . $data['user_id'] . "', 'cash_in', '" . $data['note'] . "' , '" . $store_id . "' , '1','" . $data['till_id'] . "','" . $data['location_id'] . "' )";
                if ($writeConnection->query($query)) {
                    $return['msg'] = 'The transaction has been saved successfully.';
                    $return['error'] = false;
                } else {
                    $return['msg'] = 'Can NOT save this transaction';
                    $return['error'] = true;
                }
                break;

            case 'out':

                if ($data['type'] == 'out' && $current_balance >= $data['amount']) {
                    $previous_balance = $current_balance;
                    $current_balance -= $data['amount'];
                    if (!isset($data['user_id']))
                        $data['user_id'] = NULL;
                    $query = 'INSERT INTO ' . $tableName . " (`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`, `user_id`, `payment_method`, `comment`, `store_id`, `transac_flag` , `till_id`, `location_id`) VALUE ('" . $data['amount'] . "', 'out','" . $now . "', 'Manual','" . $previous_balance . "','" . $current_balance . "',  '" . $data['user_id'] . "' ,'cash_out', '" . $data['note'] . "'  , '" . $store_id . "' ,'1','" . $data['till_id'] . "','" . $data['location_id'] . "')";
                    if ($writeConnection->query($query)) {
                        $return['msg'] = 'The transaction has been saved successfully.';
                        $return['error'] = false;
                    } else {
                        $return['msg'] = 'Can NOT save this transaction';
                        $return['error'] = true;
                    }
                } else {
                    $return['msg'] = 'You can NOT withdraw an amount of money which is greater than the Current Balance';
                    $return['error'] = true;
                }

                break;
            default:
                $amount = $data['cash_in'] - $data['cash_out'];
                $note = "";
                $previous_balance = $current_balance;
                $current_balance += $amount;
                $query = 'INSERT INTO ' . $tableName . " (`cash_in`,`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`, `payment_method`, `comment`, `store_id`, `transac_flag`, `till_id`, `location_id`) VALUE ('" . $data['cash_in'] . "', '" . $data['cash_out'] . "', 'in','" . $now . "', '" . $data['order_id'] . "','" . $previous_balance . "','" . $current_balance . "' , '" . $data['user_id'] . "', '" . $data['payment_method'] . "','" . $note . "', '" . $store_id . "', '1' ,'" . $data['till_id'] . "','" . $data['location_id'] . "')";

                if ($writeConnection->query($query)) {
                    $return['msg'] = 'The transaction has been saved successfully.';
                    $return['error'] = false;
                } else {
                    $return['msg'] = 'Can NOT save this transaction';
                    $return['error'] = true;
                }
                break;
        }
        return $return;
    }

}
