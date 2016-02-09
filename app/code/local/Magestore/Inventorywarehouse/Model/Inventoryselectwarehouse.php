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

class Magestore_Inventorywarehouse_Model_Inventoryselectwarehouse {
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse with the largest product Qty.')),
            array('value' => 2, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse with the smallest product Qty.')),
            array('value' => 3, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse with the minimum distance to customer’s shipping address')),
            array('value' => 4, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse associated with current Store')),
        );
    }
}

