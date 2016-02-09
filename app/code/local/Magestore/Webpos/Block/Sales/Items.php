<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Sales_Items extends Mage_Core_Block_Template {

    const DEFAULT_INFORMATION_SEPARATOR_TYPE = '';

    public function _construct() {
        $this->setTemplate('webpos/webpos/orderlist/items.phtml');
    }

    /*
     * Get Order's Items
     * @return collection
     */

    public function getItems() {
        $obj = new Magestore_Webpos_Block_Admin_Orderlist_Printinvoice();
        $_order = $obj->getOrder();
        return $_order->getItemsCollection();
    }

    public function getHtmlSeparatorStyle() {
        return 'border-top: dashed 1px #000; border-bottom: dashed 1px #000';
    }

}
