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
 * Inventory Adminhtml Block
 *
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Title extends Mage_Adminhtml_Block_Template {
    
    public function getTitle() {
        $title = '<h3><a href="' . $this->getUrl('adminhtml/inp_dashboard/index') .'">';
        $title .= '<span>'. $this->__('Inventory Plus') .'</span></a></h3>';
        $titleObject = new Varien_Object(array('text' => $title));
        Mage::dispatchEvent('inventoryplus_before_show_title', array('title' => $titleObject));
        return $titleObject->getText();
    }
}