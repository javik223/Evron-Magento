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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Status Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Model_Purchaseorder extends Varien_Object
{

    
    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $purchaseorders = Mage::getModel('inventorypurchasing/purchaseorder')->getCollection()
                                                                             ->setOrder('purchase_order_id','DESC');
        $return = array();
        foreach($purchaseorders as $purchaseorder){
            $return[$purchaseorder->getId()] = Mage::helper('inventorybarcode')->__('PO#').$purchaseorder->getId();
        }
        
        return $return;
    }
    
}