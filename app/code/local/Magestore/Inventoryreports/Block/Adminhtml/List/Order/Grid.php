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
 * @package     Magestore_Inventoryplus
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
class Magestore_Inventoryreports_Block_Adminhtml_List_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('reportOrderGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        if (!$this->getFilterData())
            $this->setFilterData(new Varien_Object());
        $this->setUseAjax(true);
    }

    protected function _getCollectionClass() {
        return 'inventoryreports/sales_order_grid_collection';
    }

    protected function _prepareCollection() {
        $filter = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $orderIdsArray = explode(',', $filter['orderids']);
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->addFieldToFilter('entity_id', array('in' => $orderIdsArray));
        $collection->getSelect()
                ->joinLeft(array('c' => $collection->getTable('sales/order')), 'main_table.entity_id = c.entity_id', array('email' => 'customer_email', 'shipping_method', 'coupon_code')
                )->joinLeft(array('a' => $collection->getTable('sales/order_address')), "main_table.entity_id = a.parent_id AND a.address_type = 'shipping'", array()
        )->joinLeft(array('b' => $collection->getTable('sales/order_address')), "main_table.entity_id = b.parent_id AND b.address_type = 'billing'", array(
            'telephone' => 'IF (a.telephone IS NULL, b.telephone, a.telephone)',
                )
        )->joinLeft(array('i' => $collection->getTable('sales/order_item')), 'main_table.entity_id = i.order_id AND i.parent_item_id IS NULL', array('skus' => 'GROUP_CONCAT(i.sku SEPARATOR ", ")')
        )->group('main_table.entity_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
                'filter_index' => 'main_table.store_id',
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
            'filter_index' => 'main_table.created_at',
        ));

        $this->addColumn('skus', array(
            'header' => Mage::helper('sales')->__('Product SKU(s)'),
            'index' => 'skus',
            'filter_index' => 'i.sku',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_index' => 'main_table.base_grand_total',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_index' => 'main_table.grand_total',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'filter_index' => 'main_table.status',
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action', array(
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url' => array('base' => 'adminhtml/sales_order/view'),
                        'field' => 'order_id',
                        'data-column' => 'action',
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/ordersgrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

}
