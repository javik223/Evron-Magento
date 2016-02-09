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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Model_Transaction extends Mage_Core_Model_Abstract
{ 
    const TYPE_SEND_STOCK = 1;
    const TYPE_RECEIVE_STOCK = 2;
    const TYPE_DELIVERY_STOCK = 3;
    const TYPE_RETURN_STOCK = 4;
    const TYPE_SHIP_STOCK = 5;
    const TYPE_REFUND_STOCK = 6;
        
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventorywarehouse/transaction');
    }

    public function addShipmentTransaction($warehouseId, $shipItem){
        
    }
 
    public function addRefundTransaction($creditmemo){

    }    
}