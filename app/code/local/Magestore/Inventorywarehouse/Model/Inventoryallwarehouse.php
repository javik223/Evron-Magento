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

class Magestore_Inventorywarehouse_Model_Inventoryallwarehouse {
    public function toOptionArray()
    {
		$warehouses = Mage::getModel('inventoryplus/warehouse')
						->getCollection()
						->addFieldToFilter('status',1);
		$warehouse_array = array();
		foreach($warehouses as $warehouse){
			$warehouse_array[] = array(
				'value'	=>	$warehouse->getId(),
				'label'	=>	$warehouse->getWarehouseName()
			);
		}
        return $warehouse_array;
    }
}

