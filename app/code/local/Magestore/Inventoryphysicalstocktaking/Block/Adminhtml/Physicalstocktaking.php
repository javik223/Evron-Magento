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
 * Supplier Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Physicalstocktaking extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_physicalstocktaking';
        $this->_blockGroup = 'inventoryphysicalstocktaking';
        $this->_headerText = Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking');
        $this->_addButtonLabel = Mage::helper('inventoryphysicalstocktaking')->__('Create New Physical Stocktaking');
        parent::__construct();
        $this->setTemplate('inventoryphysicalstocktaking/physicalstocktaking/new.phtml');
    }
    
    public function getWarehouseByAdmin(){
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouseIds = array();
        $collection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                            ->addFieldToFilter('admin_id',$adminId)
                            ->addFieldToFilter('can_physical',1);
        foreach($collection as $assignment){
            $warehouseIds[] = $assignment->getWarehouseId();
        }
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                                    ->addFieldToFilter('warehouse_id',array('in'=>$warehouseIds));
        return $warehouseCollection;
    }
}