<?php

class Magestore_Webpos_Block_Admin_Orderlist_Orderlist extends Mage_Core_Block_Template {

    protected $_is_holded_list = false;
    protected $_enable_till = false;
    protected $_till = false;
    protected $_till_id = false;

    public function _construct() {
        parent::_construct();
        $storeId = Mage::app()->getStore()->getId();
        //$this->_enable_till = Mage::getStoreConfig('webpos/general/enable_tills', $storeId);
        $this->_till = Mage::getModel('webpos/session')->getTill();
        if ($this->_till->getId()) {
            $this->_till_id = Mage::getModel('webpos/session')->getTill()->getId();
        }
    }

    public function getOrderCollection() {
        $collection = null;
        $collection = Mage::getModel('sales/order')->getCollection();
        return $collection;
    }

    public function getOrderbyId($orderId) {
        $order = Mage::getModel('sales/order')->load($orderId);
        return $order;
    }

    public function getOrderSearchByCustomerEmail($key) {
        $orderIds = array();
        $collections = Mage::getModel('sales/order')->getCollection();
        $collections->getSelect()->where('main_table.customer_firstname like "%' . $key . '%"
                                                            OR main_table.customer_lastname like "%' . $key . '%"
                                                            OR main_table.customer_middlename like "%' . $key . '%"
                                                            OR main_table.customer_email like "%' . $key . '%"
                                                            ');
        if ($this->_enable_till == true && $this->_till_id != false) {
            $collections->addFieldToFilter('till_id', $this->_till_id);
        }
        return $collections->getAllIds();
    }

    public function getOrderSearchById($orderId) {
        $orderIds = array();
        $collections = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('increment_id', array('like' => '%' . $orderId . '%'));
        if ($this->_enable_till == true && $this->_till_id != false) {
            $collections->addFieldToFilter('till_id', $this->_till_id);
        }
        return $collections->getAllIds();
    }

    public function getOrderGridCollections() {
        $storeId = Mage::app()->getStore()->getId();
        $rows = Mage::getStoreConfig('webpos/admin/rows', $storeId);
        $orderId = $this->getRequest()->getParam('order_id');
        $email = $this->getRequest()->getParam('name_email');
        $userId = Mage::helper('webpos/permission')->getCurrentUser();
        $isOrderThisUser = Mage::helper('webpos/permission')->isOrderThisUser($userId);
        $isOtherStaff = Mage::helper('webpos/permission')->isOtherStaff($userId);
        $isAllOrder = Mage::helper('webpos/permission')->isAllOrder($userId);

        if ($isAllOrder || ($isOrderThisUser && $isOtherStaff)) {
            $collection = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('webpos_admin_id', array('neq' => ''))
                    ->setOrder('entity_id', 'DESC');
        } elseif ($isOrderThisUser) {
            $collection = $this->getOrderByUserCollection($userId);
        } elseif ($isOtherStaff) {
            $collection = $this->getOrderByOtherStaffCollection($userId);
        }
        if ($this->_is_holded_list == true) {
            $collection->addFieldToFilter('status', array('eq' => 'holded'));
        } else {
            $collection->addFieldToFilter('status', array('neq' => 'holded'));
        }
        if ($rows)
            $collection = $collection->setPageSize($rows);
        if ($orderId)
            $collection = $collection->addFieldToFilter('entity_id', array('in' => $this->getOrderSearchById($orderId)));
        if ($email) {
            $collection = $collection->addFieldToFilter('entity_id', array('in' => $this->getOrderSearchByCustomerEmail($email)));
        }
        if ($this->_enable_till == true && $this->_till_id != false && $this->_is_holded_list == false) {
            $collection->addFieldToFilter('till_id', $this->_till_id);
        }
        return $collection;
    }

    /* Mr.Jack */

    public function getLimitOrderCollection() {
        $collection = $this->getOrderGridCollections();
        $collection->setPageSize(10)->setCurPage(1);
        return $collection;
    }

    /**/

    //vietdq
    public function getOrderByUserCollection($userId) {

        $collection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('webpos_admin_id', $userId)
                ->setOrder('entity_id', 'DESC');

        if ($this->_enable_till == true && $this->_till_id != false) {
            $collection->addFieldToFilter('till_id', $this->_till_id);
        }
        return $collection;
    }

    public function getOrderByOtherStaffCollection($userId) {

        $collection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('webpos_admin_id', array('neq' => ''))
                ->addFieldToFilter('webpos_admin_id', array('neq' => $userId))
                ->setOrder('entity_id', 'DESC');

        if ($this->_enable_till == true && $this->_till_id != false) {
            $collection->addFieldToFilter('till_id', $this->_till_id);
        }
        return $collection;
    }

    //end
    public function getEmail($orderId) {
        $order = Mage::getModel('sales/order')->load($orderId);
        return $order->getCustomerEmail();
    }

    public function hasInvoice($Id) {
        $status = Mage::getModel('sales/order')->load($Id)->getStatus();
        if ($status == 'processing') {
            return true;
        }
        return false;
    }

    public function getSearchUrl() {
        return $this->getUrl('webpos/index/orderlistSearch', array('_secure' => true));
    }

    public function getOrderStoreName($order) {
        if ($order) {
            $storeId = $order->getStoreId();
            if (is_null($storeId)) {
                $deleted = Mage::helper('webpos')->__(' [deleted]');
                return nl2br($order->getStoreName()) . $deleted;
            }
            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode('<br/>', $name);
        }
        return null;
    }

    public function getCustomerGroupName($order) {
        if ($order) {
            return Mage::getModel('customer/group')->load((int) $order->getCustomerGroupId())->getCode();
        }
        return null;
    }

    /* Jack */

    public function getTotalPage() {
        $limit = 15;
        $collection = $this->getOrderGridCollections();
        /* paginator */
        $end_page = (int) (($collection->getSize()) / $limit);
        if (($collection->getSize()) % $limit)
            $end_page = $end_page + 1;
        return $end_page;
    }

    /**/

    public function setIsHoldedList() {
        $this->_is_holded_list = true;
    }

}

?>