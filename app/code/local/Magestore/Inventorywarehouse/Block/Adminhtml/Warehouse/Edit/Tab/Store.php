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
class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Edit_Tab_Store extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('storeGrid');
        $this->setDefaultSort('store_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('core/store_group')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     */
    protected function _prepareColumns() {
        $this->addColumn('selected_store', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'group_id',
            'field_name' => 'selected_store[]',
            'values' => $this->_getSelectedStores(),
            'align' => 'center',
            'index' => 'group_id',
            'disabled_values' => array()
        ));
        $this->addColumn('group_id', array(
            'header' => Mage::helper('inventorywarehouse')->__('ID'),
            'align' => 'center',
            'width' => '100px',
            'index' => 'group_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('inventorywarehouse')->__('Store'),
            'align' => 'left',
            'index' => 'name',
            'renderer' => 'inventorywarehouse/adminhtml_warehouse_edit_tab_renderer_store',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventorywarehouse')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventorywarehouse')->__('XML'));
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
        return $this->getUrl('adminhtml/inw_warehouse/storegrid', array('id' => $this->getRequest()->getParam('id')));
    }

    protected function _getSelectedStores() {
        $rootWarehouse = Mage::helper('inventoryplus/warehouse')->getPrimaryWarehouse();
        $warehouseId = $this->getRequest()->getParam('id');
        if (!isset($warehouseId)) {
            if ($rootWarehouse) {
                $warehouseId = $rootWarehouse->getId();
            } else {
                $warehouseId = 0;
            }
        }
        $selectedStores = array();
        if ($warehouseId) {
            $stores = Mage::getModel('core/store_group')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId));

            foreach ($stores as $store) {
                array_push($selectedStores, $store->getId());
            }
        }
        return $selectedStores;
    }

}
