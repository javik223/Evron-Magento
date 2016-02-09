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
 * Inventory Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Model_Session extends Mage_Core_Model_Session_Abstract {

 public function __construct() {
   $namespace = 'inventoryphysicalstocktaking';
   $namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

   $this->init($namespace);
    Mage::dispatchEvent('inventoryphysicalstocktaking_session_init', array('inventoryphysicalstocktaking_session' => $this));
 }

}