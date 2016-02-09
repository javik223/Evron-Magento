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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorysupplyneeds Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Model_Observer
{
    
    //add Menu
    public function addMenu($observer) {
        $menu = $observer->getEvent()->getMenus()->getMenu();

        // $menu['supplyneeds'] = array('label' => Mage::helper('inventoryshipment')->__('Supply Needs'),
        //     'sort_order' => 600,
        //     'url' => Mage::helper("adminhtml")->getUrl("adminhtml/insu_inventorysupplyneeds/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
        //     'active' => (in_array(Mage::app()->getRequest()->getControllerName(),array('adminhtml_inventorysupplyneeds'))) ? true : false,
        //     'level' => 0,           
        // );
        $observer->getEvent()->getMenus()->setData('menu', $menu);
    }
}