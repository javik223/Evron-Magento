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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyproduct_Grid_Bestseller extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('bestsellerGrid');
        $this->setDefaultSort('total_sold', 'DESC');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
//        Zend_Debug::dump($requestData);
        $gettime = Mage::helper('inventoryreports')->getTimeSelected($requestData);
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->getSelect()
                ->columns('sum(main_table.qty_ordered) as total_ordered')
                ->columns('sum(main_table.qty_canceled) as total_canceled')
                ->columns('sum(main_table.qty_refunded) as total_refunded')
                ->columns('(sum(main_table.qty_ordered) - sum(main_table.qty_canceled) - sum(main_table.qty_refunded)) as total_sold')
                ->where('main_table.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '" AND main_table.product_type NOT IN ("configurable", "bundle", "grouped")')
                ->group('main_table.sku')   
        ;
        $collection->setOrder('total_sold','DESC');
        $checkResult = 0;
        $limitCollection = new Varien_Data_Collection();
        foreach ($collection as $c) {
            if ($checkResult == 7) {
                break;
            }
            $limitCollection->addItem($c);
            $checkResult++;
        }
        //$collection->setPage(0,7);
        $this->setCollection($limitCollection);
        parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsSupplier();
        }

        $this->addColumn('name', array(
            'header' => Mage::helper('sales')->__('Product Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('total_ordered', array(
            'header' => Mage::helper('sales')->__('Total Ordered'),
            'align' => 'right',
            'index' => 'total_ordered',
            'width' => '100px',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterTotalCallback'),
        ));

        $this->addColumn('total_canceled', array(
            'header' => Mage::helper('sales')->__('Total Canceled'),
            'align' => 'right',
            'index' => 'total_canceled',
            'width' => '100px',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterTotalCallback'),
        ));

        $this->addColumn('total_refunded', array(
            'header' => Mage::helper('sales')->__('Total Refunded'),
            'align' => 'right',
            'index' => 'total_refunded',
            'width' => '100px',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterTotalCallback'),
        ));

        $this->addColumn('total_sold', array(
            'header' => Mage::helper('sales')->__('Total Sold'),
            'align' => 'right',
            'index' => 'total_sold',
            'width' => '100px',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterTotalCallback'),
        ));
//        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryreports')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('inventoryreports')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/bestsellergrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        foreach ($collection as $item) {
            $fieldValue = $item->getData($column->getId());
            $pass = TRUE;
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($fieldValue) < floatval($filter['from'])) {
                    $pass = FALSE;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($fieldValue) > floatval($filter['to'])) {
                        $pass = FALSE;
                    }
                }
            }
            if ($pass) {
                $item->setData($column->getId(),$fieldValue);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
        for ($i = 0; $i < count($arr); $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

}
