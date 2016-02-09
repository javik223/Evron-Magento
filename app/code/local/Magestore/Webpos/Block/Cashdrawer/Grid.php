<?php
/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Cashdrawer_Grid extends Mage_Core_Block_Template {

    public function _construct() {
        $this->setTemplate('webpos/webpos/transaction/grid.phtml');
    }
    
    public function getTransactionCollection(){
        $current_user = Mage::getSingleton('webpos/session')->getUser();
        $till = Mage::getSingleton('webpos/session')->getTill();
        $till_id = 0;
        if($till->getId()){
            $till_id = $till->getTillId();
        }
        $collection = Mage::getModel('webpos/transaction')->getCollection()
                ->addFieldToFilter('transac_flag', array('eq' => '1'))
                ->addFieldToFilter('till_id', $till_id)
                ->addFieldToFilter(array('cash_in', 'cash_out'), array(array('gt' => '0'), array('gt' => '0')));
        $collection->getSelect()->where('main_table.user_id = '.$current_user['user_id']);
        $select = $collection->getSelect();
        $select->joinLeft(array('A' => $collection->getTable('webpos/user')), '`main_table`.`user_id` = `A`.`user_id`');
        $select->joinLeft(array('B' => $collection->getTable('core/store')), '`main_table`.`store_id` = `B`.`store_id`', array('name'));
        $select->joinLeft(array('C' => $collection->getTable('webpos/userlocation')), '`main_table`.`location_id` = `C`.`location_id`', array('display_name'));
        $collection->setOrder('transaction_id', 'DESC');
        return $collection;
    }
}
