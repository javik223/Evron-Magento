<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Block_Admin_Orderlist_Holdedlist extends Magestore_Webpos_Block_Admin_Orderlist_Orderlist {
    public function __construct() {
        parent::__construct();
        $this->setIsHoldedList();
        $this->setTemplate('webpos/webpos/orderlist/orderlist_holded.phtml');
    }
}
