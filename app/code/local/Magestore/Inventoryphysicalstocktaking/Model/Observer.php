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
 * @package     Magestore_Inventoryphysicalstocktaking
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryphysicalstocktaking Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Model_Observer {

    /**
     * process inventory_adminhtml_add_column_permission_grid event
     *
     * @return Magestore_Inventoryphysicalstocktaking_Model_Observer
     */
    public function addColumnPermission($observer) {
        $grid = $observer->getEvent()->getGrid();
        $disabledvalue = $observer->getEvent()->getDisabled();
        $grid->addColumn('can_physical', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'physical[]',
            'values' => $this->_getSelectedCanPhysicalAdmins($grid)
        ));
    }

    /**
     * process inventory_adminhtml_add_more_permission event
     *
     * @return Magestore_Inventoryphysicalstocktaking_Model_Observer
     */
    public function addMorePermission($observer) {
        $event = $observer->getEvent();
        $assignment = $event->getPermission();
        $datas = $event->getData();
        $data = $datas['data'];
        $adminId = $event->getAdminId();
        $changePermissions = $observer->getEvent()->getChangePermssions();
        $physicals = array();
        if (isset($data['physical']) && is_array($data['physical'])) {
            $physicals = $data['physical'];
        }
        if ($assignment->getId()) {
            $oldPhysical = $assignment->getCanPhysical();
        }

        if (in_array($adminId, $physicals)) {
            if ($assignment->getId()) {
                if ($oldPhysical != 1) {
                    $changePermissions[$adminId]['old_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot physical stocktaking Warehouse');
                    $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Can physical stocktaking Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_physical'] = '';
                $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Can physical stocktaking Warehouse');
            }
            $assignment->setData('can_physical', 1);
        } else {
            if ($assignment->getId()) {
                if ($oldPhysical != 0) {
                    $changePermissions[$adminId]['old_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Can physical stocktaking Warehouse');
                    $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot physical stocktaking Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_physical'] = '';
                $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot physical stocktaking Warehouse');
            }
            $assignment->setData('can_physical', 0);
        }
    }

    protected function _getSelectedCanPhysicalAdmins($grid) {
        $warehouse = $grid->getWarehouse();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $array = array();
        if ($warehouse->getId()) {
            $canPhysicalAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse->getId())
                    ->addFieldToFilter('can_physical', 1);
            foreach ($canPhysicalAdmins as $canPhysicalAdmin) {
                $array[] = $canPhysicalAdmin->getAdminId();
            }
        } else {
            $array = array($adminId);
        }


        return $array;
    }
    
    /**
     * Add physical stock-taking button to inventory listing page
     * 
     * @param Object $observer
     */
    public function inventoryplus_inventory_stock_action_buttons($observer){
        $actionsObject = $observer->getEvent()->getData('stock_actions_object');
        $actions = $actionsObject->getActions();
        $actions['physical_stocktaking'] = array('params' => array(
                    'label' => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stock-taking'),
                    'onclick' => 'location.href=\''.Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inph_physicalstocktaking/new').'\'',
                    'class' => 'save',
                        ),
                    'position' => -90
            );
        $actionsObject->setActions($actions);
    }
    
    /**
     * Add physical stock-taking button to warehouse page
     * 
     * @param Object $observer
     */    
    public function inventoryplus_warehouse_stock_action_buttons($observer){
        $actionsObject = $observer->getEvent()->getData('stock_actions_object');
        $warehouse = $observer->getEvent()->getData('warehouse');
        if(!Mage::helper('inventoryplus/warehouse')->isAllowAction('physical', $warehouse))
            return;
        $actions = $actionsObject->getActions();
        $actions['physical_stocktaking'] = array('params' => array(
                    'label' => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stock-taking'),
                    'onclick' => 'location.href=\''.Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inph_physicalstocktaking/prepare',array('warehouse_id'=>$warehouse->getId())).'\'',
                    'class' => 'save',
                        ),
                    'position' => -90
            );
        $actionsObject->setActions($actions);        
    }

}
