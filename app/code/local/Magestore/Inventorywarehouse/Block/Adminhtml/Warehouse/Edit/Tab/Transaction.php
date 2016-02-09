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
class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Edit_Tab_Transaction extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('transaction_grid');
        $this->setDefaultSort('warehouse_transaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function getWarehouse() {
        return Mage::getModel('inventoryplus/warehouse')
                        ->load($this->getRequest()->getParam('id'));
    }

    protected function _prepareCollection() {
        $arr = array();
        $warehouseId = $this->getRequest()->getParam('id');
        $warehouse = $this->getWarehouse();
        $warehouseName = $warehouse->getName();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core/read');
        $sql = 'SELECT `type`,`warehouse_transaction_id`,`warehouse_name_from`,`warehouse_name_to`,`warehouse_id_from`,`warehouse_id_to` FROM ' . $resource->getTableName('inventorywarehouse/transaction');
        $datas = $readConnection->fetchAll($sql);
        foreach ($datas as $data) {
            $type = $data['type'];
            if ($type == '1') {
                if ($data['warehouse_id_from'] == $warehouseId) {
                    $arr[] = $data['warehouse_transaction_id'];
                }
            } elseif ($type == '2') {
                if ($data['warehouse_id_to'] == $warehouseId) {
                    $arr[] = $data['warehouse_transaction_id'];
                }
            } elseif ($type == '3') {
                if ($data['warehouse_id_to'] == $warehouseId) {
                    $arr[] = $data['warehouse_transaction_id'];
                }
            } elseif ($type == '4') {
                if ($data['warehouse_id_from'] == $warehouseId) {
                    $arr[] = $data['warehouse_transaction_id'];
                }
            } elseif ($type == '5') {
                if ($data['warehouse_id_from'] == $warehouseId) {
                    $arr[] = $data['warehouse_transaction_id'];
                }
            } elseif ($type == '6') {
                if ($data['warehouse_id_from'] == $warehouseId) {
                    $arr[] = $data['warehouse_transaction_id'];
                }
            }
        }

        $collection = Mage::getResourceModel('inventorywarehouse/transaction_collection')
                ->addFieldToFilter('warehouse_transaction_id', array('in' => $arr));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('warehouse_transaction_id', array(
            'header' => Mage::helper('inventorywarehouse')->__('ID'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'warehouse_transaction_id',
//            'renderer' => 'inventoryplus/adminhtml_warehouse_edit_tab_renderer_increment',
            'type' => 'number'
        ));

        $this->addColumn('transaction_type', array(
            'header' => Mage::helper('inventorywarehouse')->__('Type'),
            'sortable' => true,
            'index' => 'type',
            'type' => 'options',
            'options' => Mage::helper('inventorywarehouse')->getTransactionType()
        ));

        $this->addColumn('transaction_object', array(
            'header' => Mage::helper('inventorywarehouse')->__('Sender/Recipient'),
            'sortable' => true,
            'renderer' => 'inventorywarehouse/adminhtml_warehouse_edit_tab_renderer_transactionobject',
            'filter_condition_callback' => array($this, 'filterTransactionObject')
        ));

        $this->addColumn('total_products', array(
            'header' => Mage::helper('inventorywarehouse')->__('Product Qty'),
            'width' => '80px',
            'index' => 'total_products',
            'type' => 'number',
            'default' => 0,
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('inventorywarehouse')->__('Created At'),
            'width' => '80px',
            'index' => 'created_at',
            'type' => 'date',
            'filter_condition_callback' => array($this, 'filterCreatedAt')
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('inventorywarehouse')->__('Created By'),
            'sortable' => true,
            'index' => 'created_by',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('inventorywarehouse')->__('Action'),
            'align' => 'left',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('inventorywarehouse')->__('View'),
                    'url' => array('base' => 'adminhtml/inw_warehouse/viewtransaction',
                        'params' => array('warehouse_id' => $this->getRequest()->getParam('id'))
                    ),
                    'field' => 'transaction_id',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/transactionGrid', array('_current' => true, 'warehouse_id' => $this->getRequest()->getParam('id')));
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/inw_warehouse/viewtransaction', array('transaction_id' => $row->getWarehouseTransactionId(), 'warehouse_id' => $this->getRequest()->getParam('id')));
    }

    public function filterTransactionObject($collection, $column) {
        $value = $column->getFilter()->getValue();
		if(strpos($value,'/')==false)
			$collection->getSelect()->where("warehouse_name_from LIKE '%$value%' OR warehouse_name_to LIKE '%$value%'");
		else{
			$finder = explode('/',$value);
			$collection->getSelect()->where("warehouse_name_from LIKE '%".$finder[0]."%' AND warehouse_name_to LIKE '%".$finder[1]."%'");
		}
    }

    public function filterCreatedAt($collection, $column) {
        $value = $column->getFilter()->getValue();
        $from = $value['orig_from'];
        $to = $value['orig_to'];
        if ($from) {
            $from = date('Y-m-d', strtotime($from));
            $collection->addFieldToFilter('created_at', array('gteq' => $from));
        }
        if ($to) {
            $to = date('Y-m-d', strtotime($to));
            $to .= ' 23:59:59';
            $collection->addFieldToFilter('created_at', array('lteq' => $to));
        }
    }

}
