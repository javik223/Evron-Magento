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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Totalstockdifferentwhenphysicalstocktakingbywarehouse extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_arr = array();

    public function __construct() {
        parent::__construct();
        $this->setId('totalstockdifferentwhenphysicalstocktakingbywarehousegrid');
        $this->setDefaultSort('different');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _getAttributeTableAlias($attributeCode) {
        return 'at_' . $attributeCode;
    }

    protected function _prepareCollection() {
        $series = array();
        $total_data = array();
        $warehouse_name = array();
        $productIds = array();
        $difference = array();
        $phyids = '';
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $installer = Mage::getModel('core/resource');

        if ($requestData['warehouse_select']) {
            $warehouse_collection = Mage::getModel('inventoryplus/warehouse')->getCollection();
            $warehouse_id = $requestData['warehouse_select'];
            $query = 'SELECT DISTINCT p.product_id,t.physicalstocktaking_id
                FROM '.$installer->getTableName("erp_inventory_physicalstocktaking t").'
                LEFT JOIN '.$installer->getTableName("erp_inventory_physicalstocktaking_product p").'
                ON t.physicalstocktaking_id = p.physicalstocktaking_id
                WHERE t.status > 0 AND t.warehouse_id = "' . $warehouse_id . '" AND t.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"
                ';
            $results = $readConnection->fetchAll($query);
            if ($results) {
                foreach ($results as $value) {
                    $productIds[] = $value['product_id'];
                    if ($phyids == '') {
                        $phyids = "('" . $value['physicalstocktaking_id'] . "'";
                    } else {
                        $phyids .= ',';
                        $phyids .= "'" . $value['physicalstocktaking_id'] . "'";
                    }
                }
                $phyids .= ")";
            } else {
                $phyids = "('0')";
            }
            $productIds = Mage::helper('inventoryreports')->checkProductInWarehouse($productIds, $warehouse_id);
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('entity_id', array('in' => $productIds));
            $collection->joinField('old_qty', 'inventoryphysicalstocktaking/physicalstocktaking_product', 'old_qty', 'product_id=entity_id', '{{table}}.old_qty IS NOT NULL AND {{table}}.old_qty > 0 AND {{table}}.physicalstocktaking_id IN ' . $phyids, 'left');
            $collection->joinField('adjust_qty', 'inventoryphysicalstocktaking/physicalstocktaking_product', 'adjust_qty', 'product_id=entity_id', '{{table}}.adjust_qty IS NOT NULL AND {{table}}.adjust_qty > 0 AND {{table}}.physicalstocktaking_id IN ' . $phyids, 'left');
            $collection->getSelect()->columns(array('sum_old_qty' => new Zend_Db_Expr("SUM(at_old_qty.old_qty)")));
            $collection->getSelect()->columns(array('sum_adjust_qty' => new Zend_Db_Expr("SUM(at_adjust_qty.adjust_qty)")));
            $collection->getSelect()->columns(array('difference' => new Zend_Db_Expr("SUM(at_adjust_qty.adjust_qty) - SUM(at_old_qty.old_qty)")));
//            $collection->getSelect()->columns(array('name' => new Zend_Db_Expr("e.name")));
            $collection->getSelect()->group('e.entity_id');
        }
        Mage::getSingleton('core/resource_iterator')->walk(
                $collection->addAttributeToSelect('*')->getSelect(), array(array($this, 'collectionCallback')), array()
        );
        $filterCollection = new Varien_Data_Collection();
        for ($i = 0; $i < count($this->_arr); $i++) {
            $filterCollection->addItem($this->_arr[$i]);
        }
        $this->setCollection($filterCollection);
        $this->_prepareTotals('sum_adjust_qty,sum_old_qty,difference');
        return parent::_prepareCollection();
    }

    public function collectionCallback($args) {
        $_product = Mage::getModel('catalog/product');
        $name = $_product->load($args['row']['entity_id'])->getName();
        $args['row']['name'] = $name;
        $_product->setData($args['row']);
        $sumold = $_product->getData('sum_old_qty');
        $sumadjust = $_product->getData('sum_adjust_qty');
        if (isset($sumold) && isset($sumadjust)) {
            $this->_arr[] = $_product;
        }
    }

    protected function _prepareTotals($columns = null) {
        $columns = explode(',', $columns);
        if (!$columns) {
            return;
        }
        $this->_countTotals = true;
        $totals = new Varien_Object();
        $fields = array();
        foreach ($columns as $column) {
            $fields[$column] = 0;
        }
        foreach ($this->getCollection() as $item) {
            foreach ($fields as $field => $value) {
                $fields[$field]+=$item->getData($field);
            }
        }
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
//        if($this->is_warehouse == 0){
//            $fields['warehouse_name'] = 'Totals';
//        }
//        else{$fields['name'] = 'Totals';}
        $fields['name'] = 'Totals';
        $totals->setData($fields);
        $this->setTotals($totals);
        return;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if ($requestData['warehouse_select']) {   //Warehouse Selected
            $this->addColumn('name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'name',
                'type' => 'text',
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));
            $this->addColumn('sum_adjust_qty', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty After Stocktake'),
                'align' => 'right',
                'index' => 'sum_adjust_qty',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));
            $this->addColumn('sum_old_qty', array(
                'header' => Mage::helper('inventoryreports')->__('Total Qty Before Stocktake'),
                'align' => 'right',
                'index' => 'sum_old_qty',
                'type' => 'number',
                'width' => '100px',
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));
            $this->addColumn('difference', array(
                'header' => Mage::helper('inventoryreports')->__('Qty Variance'),
                'align' => 'right',
                'index' => 'difference',
                'type' => 'number',
                'width' => '100px',
                'filter' => false,
                'sortable' => false,
                'filter_condition_callback' => array($this, '_filterCallback'),
            ));
        }
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
        return false;
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/totalstockdifferentwhenphysicalstocktakingbywarehousegrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    protected function _filterCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        foreach ($collection as $item) {
            $fieldValue = $item->getData($column->getId());
            $pass = TRUE;
            if (!isset($filter['from']) && !isset($filter['to'])) {
                if (strpos(strtolower($fieldValue), strtolower($filter)) == false) {
                    $pass = false;
                }
            }
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
        $this->_prepareTotals('sum_adjust_qty,sum_old_qty,difference');
    }

}
