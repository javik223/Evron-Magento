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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Transaction_View extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {        
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventorywarehouse';
        $this->_controller = 'adminhtml_warehouse_transaction_view';
        $warehouse_id = $this->getRequest()->getParam('warehouse_id');
        $transaction_id = $this->getRequest()->getParam('transaction_id');
        $transaction = Mage::getModel('inventorywarehouse/transaction')->load($transaction_id);
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl("adminhtml/inp_warehouse/edit", array("id" => $warehouse_id)) . '\')');
        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('transaction_data')
                && Mage::registry('transaction_data')->getId()
        ) {
            return Mage::helper('inventorywarehouse')->__("View Transaction '%s'", Mage::registry('transaction_data')->getId());
        }
    }

}