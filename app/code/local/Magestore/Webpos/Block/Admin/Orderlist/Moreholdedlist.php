<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Block_Admin_Orderlist_Moreholdedlist extends Mage_Core_Block_Template {
    public function __construct() {
        parent::__construct();
        $this->setTemplate('webpos/webpos/orderlist/newholdedlist.phtml');
    }
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }
    public function getOrder() {
        $limit = 15;
        $collection = Mage::getBlockSingleton('webpos/admin_orderlist_holdedlist')->getOrderGridCollections();
        $page = Mage::app()->getRequest()->getParam('page');
        $end_page = (int)(($collection->getSize())/$limit) ;
        if (($collection->getSize())%$limit)
                $end_page = $end_page + 1;
        $collection->setPageSize($limit)->setCurPage($page);
        return $collection;
    }
}
