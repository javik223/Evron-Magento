<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Helper_User extends Mage_Core_Helper_Abstract {
    /*
      These are some functions to get payment method information
     */

    public function getCurrentUserLocationId() {
        $user = Mage::getModel('webpos/session')->getUser();
        $userLocationId = $user->getLocationId();
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                    ->addFieldToFilter('user_id', $userId);
            $userLocationId = $userCollection->getfirstItem()->getWarehouseId();
        }
        return $userLocationId;
    }

}
