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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_List_Purchaseorder_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('purchaseordersGrid');
        $this->setDefaultSort('purchase_on');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        if (!$this->getFilterData())
            $this->setFilterData(new Varien_Object());
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $filter = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $purchaseIdsArray = explode(',', $filter['purchaseorderids']);
        $collection = Mage::getModel('inventorypurchasing/purchaseorder')->getCollection()
                ->addFieldToFilter('purchase_order_id', array('in' => $purchaseIdsArray));
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $this->addColumn('purchase_order_id', array(
            'header' => Mage::helper('inventorypurchasing')->__('Order #'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'purchase_order_id',
        ));

        $this->addColumn('purchase_on', array(
            'header' => Mage::helper('inventorypurchasing')->__('Purchased On'),
            'align' => 'right',
            'type' => 'date',
            'index' => 'purchase_on',
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('inventorypurchasing')->__('Created by'),
            'width' => '80px',
            'align' => 'left',
            'index' => 'created_by'
        ));

        $this->addColumn('bill_name', array(
            'header' => Mage::helper('inventorypurchasing')->__('Bill to Name'),
            'width' => '150px',
            'align' => 'left',
            'index' => 'bill_name',
        ));

        $this->addColumn('warehouse_id', array(
            'header' => Mage::helper('inventorypurchasing')->__('Warehouse'),
            'type' => 'options',
            'align' => 'left',
            'options' => Mage::helper('inventoryplus/warehouse')->getAllWarehouseName(),
            'renderer' => 'inventorypurchasing/adminhtml_purchaseorder_renderer_warehouse',
            'filter_index' => 'warehouse_id',
            'index' => 'warehouse_id'
        ));

        $this->addColumn('supplier_name', array(
            'header' => Mage::helper('inventorypurchasing')->__('Supplier'),
            'type' => 'options',
            'width' => '150px',
            'align' => 'left',
            'index' => 'supplier_name',
            'options' => Mage::helper('inventorypurchasing/supplier')->getAllSupplierName(),
            'renderer' => 'inventorypurchasing/adminhtml_purchaseorder_renderer_supplier',
        ));

        $this->addColumn('total_products', array(
            'header' => Mage::helper('inventorypurchasing')->__('Qty Requested'),
            'width' => '150px',
            'type' => 'number',
            'align' => 'right',
            'index' => 'total_products',
        ));
        $this->addColumn('total_products_recieved', array(
            'header' => Mage::helper('inventorypurchasing')->__('Qty Received'),
            'width' => '150px',
            'type' => 'number',
            'align' => 'right',
            'index' => 'total_products_recieved',
        ));

        $this->addColumn('total_amount', array(
            'header' => Mage::helper('inventorypurchasing')->__('Subtotal'),
            'width' => '150px',
            'type' => 'number',
            'align' => 'right',
            'index' => 'total_amount',
            'filter_index' => 'total_amount',
            'renderer' => 'inventorypurchasing/adminhtml_purchaseorder_renderer_total',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('inventorypurchasing')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('inventorypurchasing/purchaseorder')->getReturnOrderStatus(),
        ));
        $labelAction = __('Edit');
        $this->addColumn('action', array(
            'header' => Mage::helper('inventorypurchasing')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $labelAction,
                    'url' => array('base' => 'adminhtml/inpu_purchaseorders/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true
        ));

        //$this->addExportType('*/*/exportPurchaseOrderCsv', Mage::helper('inventorypurchasing')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('inventorypurchasing')->__('XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/purchaseordergrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

}
