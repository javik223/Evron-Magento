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
 * Inventory Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Helper_Adjuststock extends Mage_Core_Helper_Abstract {

    /**
     * get all warehouses to adjust stock.
     * 
     * @return boolean
     */
    public function getWarehouseByAdmin(){
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouseIds = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                            ->addFieldToFilter('admin_id',$adminId)
                            ->addFieldToFilter('can_adjust',1);
        foreach($collection as $assignment){
            $warehouseIds[] = $assignment->getWarehouseId();
        }
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                                    ->addFieldToFilter('status',1)
                                    ->addFieldToFilter('warehouse_id',array('in'=>$warehouseIds));
        return $warehouseCollection;
    }

}
