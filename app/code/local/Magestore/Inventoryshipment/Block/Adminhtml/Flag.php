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
 * @package     Magestore_Inventoryshipment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryshipment Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryshipment
 * @author      Magestore Developer
 */
class Magestore_Inventoryshipment_Block_Adminhtml_Flag extends Mage_Core_Block_Text
{
    public function getText()
    {
        if($this->helper('inventoryplus')->isInInventorySection()){
            return '<input type="hidden" name="inventoryplus_section" value="1" />';
        }
    }
}