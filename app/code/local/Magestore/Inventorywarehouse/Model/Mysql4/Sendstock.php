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
 * Inventorywarehouse Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Model_Mysql4_Sendstock extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('inventorywarehouse/sendstock', 'warehouse_sendstock_id');
    }
}