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
 * Warehouse Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Warehouse_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('warehouse_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventoryplus')->__('Warehouse Information'));
    }

    /**
     * prepare before render block to html
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Edit_Tabs
     */
    protected function _beforeToHtml() {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $username = $admin->getUsername();
        $warehouse = Mage::registry('warehouse_data');
        //add top tabs into warehouse
        Mage::dispatchEvent('inventory_adminhtml_add_top_tab_warehouse', array('tab' => $this, 'warehouse' => $warehouse));

        //warehouse information
        if (!$this->getRequest()->getParam('id')) {
            $this->addTab('form_section', array(
                'label' => Mage::helper('inventoryplus')->__('Information'),
                'title' => Mage::helper('inventoryplus')->__('Information'),
                'content' => $this->getLayout()
                        ->createBlock('inventoryplus/adminhtml_warehouse_edit_tab_form')
                        ->toHtml(),
            ));
        }
        //warehouse products
        if ($this->getRequest()->getParam('id'))
            $this->addTab('product_section', array(
                'label' => Mage::helper('inventoryplus')->__('Stocks On-Hand'),
                'title' => Mage::helper('inventoryplus')->__('Stocks On-Hand'),
                'url' => $this->getUrl('*/*/products', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
                )),
                'class' => 'ajax',
            ));
        //warehouse permission
        if (!$this->getRequest()->getParam('id')) {
            $this->addTab('permission_section', array(
                'label' => Mage::helper('inventoryplus')->__('Permission'),
                'title' => Mage::helper('inventoryplus')->__('Permission'),
                'content' => $this->getLayout()
                        ->createBlock('inventoryplus/adminhtml_warehouse_edit_tab_permission')
                        ->toHtml(),
            ));
        } else {
            $this->addTab('permission_section', array(
                'label' => Mage::helper('inventoryplus')->__('Permission'),
                'title' => Mage::helper('inventoryplus')->__('Permission'),
                'content' => $this->getLayout()
                        ->createBlock('inventoryplus/adminhtml_warehouse_edit_tab_permission')
                        ->toHtml(),
            ));
        }

        Mage::dispatchEvent('inventory_adminhtml_add_tab_warehouse', array('tab' => $this, 'warehouse_id' => $this->getRequest()->getParam('id')));

        if ($this->getRequest()->getParam('id')) {
            $this->addTab('form_section', array(
                'label' => Mage::helper('inventoryplus')->__('Information'),
                'title' => Mage::helper('inventoryplus')->__('Information'),
                'content' => $this->getLayout()
                        ->createBlock('inventoryplus/adminhtml_warehouse_edit_tab_form')
                        ->toHtml(),
            ));
            /*
            $this->addTab('salesstaff_section', array(
                'label' => Mage::helper('inventoryplus')->__('Sales Staffs'),
                'title' => Mage::helper('inventoryplus')->__('Sales Staffs'),
                'content' => $this->getLayout()
                        ->createBlock('inventoryplus/adminhtml_warehouse_edit_tab_salesstaff')
                        ->toHtml(),
            ));
             */
        }

        //warehouse change history
        if ($this->getRequest()->getParam('id')) {
            $this->addTab('history_section', array(
                'label' => Mage::helper('inventoryplus')->__('Change History'),
                'title' => Mage::helper('inventoryplus')->__('Change History'),
                'url' => $this->getUrl('*/*/history', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
                )),
                'class' => 'ajax',
            ));
        }
        return parent::_beforeToHtml();
    }

}
