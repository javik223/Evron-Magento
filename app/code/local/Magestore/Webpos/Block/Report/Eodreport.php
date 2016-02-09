<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Report_Eodreport extends Mage_Core_Block_Template {

    public function _construct() {
        $this->setTemplate('webpos/webpos/reports/eodreport.phtml');
    }

    public function getOrderCollectionCollection() {
        $current_user = Mage::getSingleton('webpos/session')->getUser()->getUserId();
        $till = Mage::getSingleton('webpos/session')->getTill();
        $till_id = 0;
        if ($till->getId()) {
            $till_id = $till->getTillId();
        }
        $storeId = Mage::app()->getStore()->getId();
		$enableTill = Mage::getStoreConfig('webpos/general/enable_tills', $storeId);
        $today = date("Y-m-d");
        $collection = Mage::getModel('webpos/posorder')->getCollection();
        $collection->addFieldToFilter('main_table.user_id', $current_user);
		if($enableTill == true){
			$collection->addFieldToFilter('main_table.till_id', $till_id);
		}
        $select = $collection->getSelect();
        $select->joinLeft(array('A' => $collection->getTable('webpos/user')), '`main_table`.`user_id` = `A`.`user_id`', array('user_name' => 'display_name'));
        $select->joinLeft(array('C' => $collection->getTable('sales/order_grid')), '`main_table`.`order_id` = `C`.`increment_id`', array('billing_name', 'store_id', 'status'));
        $select->joinLeft(array('B' => $collection->getTable('core/store')), '`C`.`store_id` = `B`.`store_id`', array('name'));
        $select->where('main_table.created_date  >= ?', $today . ' 00:00:00');
        $select->where('main_table.created_date   <= ?', $today . ' 23:59:59');
        $collection->addFieldToFilter('C.store_id', $storeId);
        $collection->addOrder('main_table.webpos_order_id', 'DESC');
        return $collection;
    }

}
