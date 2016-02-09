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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyproduct_Grid_Moststockremain extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('moststockremainGrid');
        $this->setDefaultSort('total_remain');
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
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);


        $collection = Mage::getResourceModel('inventoryreports/product_collection')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id');
        $collection->joinField('warehouse_qty', 'inventoryplus/warehouse_product', 'total_qty', 'product_id=entity_id', '{{table}}.warehouse_id > 0', 'inner');
        $collection->getSelect()->columns(array('total_remain' => new Zend_Db_Expr("SUM(at_warehouse_qty.total_qty)")));
        $collection->getSelect()->group('e.entity_id');
        $collection->getSelect()->order('total_remain DESC');
        $collection->setIsGroupCountSql(true);
        $limitCollection = Mage::helper('inventoryreports')->_tempCollection();
        foreach ($collection as $c) {
            if ($checkResult == 7) {
                break;
            }
            $limitCollection->addItem($c);
            $checkResult++;
        }
        $this->setCollection($limitCollection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));

        $this->addColumn('name', array(
            'header' => Mage::helper('inventoryreports')->__('Product Name'),
            'align' => 'left',
            'index' => 'name',
            'filter' => false,
            'sortable' => false,
        ));
        $this->addColumn('total_remain', array(
            'header' => Mage::helper('inventoryreports')->__('Qty. Remaining'),
            'align' => 'right',
            'index' => 'total_remain',
            'type' => 'number',
            'width' => '100px',
            'filter_condition_callback' => array($this, '_filterTotalRemainCallback'),
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
        return $this->getUrl('adminhtml/inr_report/moststockremaingrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    protected function _setCollectionOrder($column) {
        $filterData = $this->getFilterData();
        $collection = $this->getCollection();

         //process require information to calculate outstock date

        if ($collection) {
            switch ($column->getId()) {
                case 'total_remain':
                    $arr = array();
                    foreach ($collection as $item) {
                        $arr[] = $item;
                    }
                    if ($column->getDir() == 'asc') {
                        $sorted = usort($arr, array($this, 'cmpAsc'));
                    } else {
                        $sorted = usort($arr, array($this, 'cmpDesc'));
                    }
                    $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
                    for ($i = 0; $i < count($arr); $i++) {
                        $temp->addItem($arr[$i]);
                    }
                    $this->setCollection($temp);
                    break;
                default:
                    $filter = $column->getIndex();
                    if ($column->getDir() == 'asc') {
                        $collection->setOrder($filter, 'ASC');
                    } else {
                        $collection->setOrder($filter, 'DESC');
                    }
                    break;
            }
        }
    }

    public static function cmpAsc($a, $b) {
        return $a->getTotalRemain() > $b->getTotalRemain();
    }

    public static function cmpDesc($a, $b) {
        return $a->getTotalRemain() < $b->getTotalRemain();
    }

    public function _filterTotalRemainCallback($collection, $column) {
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
                $item->setData($column->getId(), $fieldValue);
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
