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
 * Warehouse Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Edit_Tab_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Get full path of store
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $website = Mage::app()->getGroup($row->getGroupId())->getWebsite()->getName();
        $html = $website .' -- '. $row->getName();
        return $html;
    }

}
