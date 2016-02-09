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
class Magestore_Inventorywebpos_Block_Adminhtml_Warehouse_Edit_Tab_Webpos_Permission extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('userGrid');
        $this->setDefaultSort('user_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Marketingautomation_Block_Adminhtml_Marketingautomation_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('webpos/user')->getCollection();
        $collection->getSelect()
                ->joinLeft(array('webpos_role' => $collection->getTable('webpos/role')), "main_table.role_id = webpos_role.role_id", array('role' => 'display_name'));
        $collection->getSelect()
                ->joinLeft(array('webpos_user_warehouse' => $collection->getTable('inventorywebpos/webposuser')), "main_table.user_id = webpos_user_warehouse.user_id", array('can_create_shipment'));
        $collection->getSelect()->columns(array(
            'can_create_shipment' => 'IFNULL(webpos_user_warehouse.can_create_shipment, 0)'
        ));
        $collection->getSelect()
                ->joinLeft(array('warehouse' => $collection->getTable('inventoryplus/warehouse')), "webpos_user_warehouse.warehouse_id = warehouse.warehouse_id", array('warehouse_name'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Marketingautomation_Block_Adminhtml_Marketingautomation_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('selected_user', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'user_id',
            'field_name' => 'selected_user[]',
            'values' => $this->_getSelectedWebposUsers(),
            'align' => 'center',
            'index' => 'user_id',
            'disabled_values' => array()
        ));
        $this->addColumn('user_id', array(
            'header' => Mage::helper('webpos')->__('ID'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'user_id',
            'filter_index' => 'main_table.user_id',
        ));
        $this->addColumn('username', array(
            'header' => Mage::helper('webpos')->__('User Name'),
            'align' => 'left',
            'index' => 'username',
        ));
        $this->addColumn('email', array(
            'header' => Mage::helper('webpos')->__('Email'),
            'align' => 'left',
            'index' => 'email',
        ));
        $this->addColumn('display_name', array(
            'header' => Mage::helper('webpos')->__('Display Name'),
            'align' => 'left',
            'index' => 'display_name',
            'filter_index' => 'main_table.display_name',
        ));
        $this->addColumn('warehouse', array(
            'header' => Mage::helper('webpos')->__('Warehouse'),
            'align' => 'left',
            'index' => 'warehouse_name',
        ));
        $this->addColumn('role', array(
            'header' => Mage::helper('webpos')->__('Role'),
            'align' => 'left',
            'index' => 'role',
            'filter_index' => 'webpos_role.display_name',
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('webpos')->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enable',
                2 => 'Disable',
            ),
            'filter_index' => 'main_table.status',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('webpos')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('webpos')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return false;
    }
    
    public function getGridUrl() {
        return $this->getUrl('*/inwe_warehouse/webpospermissionsgrid',array(
                      '_current'	=> true,
                      'id'              => $this->getRequest()->getParam('id'),
                      'store'		=> $this->getRequest()->getParam('store')
                    ));
    }
    
    public function getSelectedWebposUsers() {
        return $this->_getSelectedWebposUsers();
    }

    public function _getSelectedWebposUsers() {
        $warehouseId = $this->getRequest()->getParam('id');
        if (!isset($warehouseId)) {
            $warehouseId = 0;
        }

        $webposUsersWarehouse = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId);
        $selectedWebposUsers = array();
        foreach ($webposUsersWarehouse as $webposUser) {
            $selectedWebposUsers[] = $webposUser->getUserId();
        }
        return $selectedWebposUsers;
    }

}
