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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbycustomer_Grid
    extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid {

    protected $_requestData = null;
    protected $_filter = null;

    public function __construct() {
        parent::__construct();
        $this->setId('reportcustomerGrid');
        $this->setUseAjax(true);
        //set request data
        $requestData = $this->getRequestData();
        $this->_requestData = $requestData;
        //show total row
        $this->setCountTotals(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::helper('inventoryreports/customer')->getCustomerReportCollection($this->_requestData);
        $this->setCollection($collection);
        $this->_setDefaultSort('sum_base_grand_total', 'DESC');
        $this->_sortCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        
        $this->addColumn('telephone', array(
            'header' => Mage::helper('inventoryreports')->__('Telephone'),
            'sortable' => true,
            'index' => 'telephone',
            'filter_condition_callback' => array($this, '_filterTelephoneCallback'),
        ));
        
        $this->addColumn('customer_email', array(
            'header' => Mage::helper('inventoryreports')->__('Email'),
            'sortable' => true,
            'index' => 'customer_email',
        ));
        
        $this->addColumn('customer_firstname', array(
            'header' => Mage::helper('inventoryreports')->__('First Name'),
            'sortable' => true,
            'index' => 'customer_firstname',
        ));

        $this->addColumn('customer_lastname', array(
            'header' => Mage::helper('inventoryreports')->__('Last Name'),
            'sortable' => true,
            'index' => 'customer_lastname',
        ));

        $this->addColumn('numberoforder', array(
            'header' => Mage::helper('inventoryreports')->__('Total Orders'),
            'sortable' => true,
            'align' => 'right',
            'index' => 'numberoforder',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('sum_base_grand_total', array(
            'header' => Mage::helper('inventoryreports')->__('Grand Total'),
            'align' => 'right',
            'index' => 'sum_base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('inventoryreports')->__('Action'),
                'width'     => '120px',
                'align'     => 'center',
                'type'      => 'action',
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'renderer'  => 'inventoryreports/adminhtml_reportcontent_reportbycustomer_renderer_action'
        ));
        
        $this->addCSVExport();
        
        return parent::_prepareColumns();
    }
    
    /**
     * Get data totals
     * 
     * @return \Varien_Object
     */
    public function getTotals()
    {
        $totals = new Varien_Object();
        $totalsData = array(
            'numberoforder' => 0,
            'sum_base_grand_total' => 0,
        );
        foreach ($this->getCollection() as $item) {
            foreach($totalsData as $field=>$value){
                $totalsData[$field] += $item->getData($field);
            }
        }
        $totalsData['sum_base_grand_total'] = $this->helper('core')->currency($totalsData['sum_base_grand_total']);
        //First column in the grid
        $totalsData['telephone'] = '';
        $totalsData['customer_email'] = '';
        $totalsData['customer_firstname'] = '';
        $totalsData['customer_lastname']= $this->__('Totals');
        $totalsData['action'] = $this->__('Totals');
        $totals->setData($totalsData);
        return $totals;
    }        
    
    /**
     * Filter data collection
     *
     * @param collection $collection
     */    
    protected function _filterCallback($collection, $column){
        $requestData = $this->getRequestData();
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        if (!$field) {
            return;
        }
        if (isset($filter['from']) && $filter['from'] >= 0) {
            $collection->getSelect()->having($field . ' >= \'' . $filter['from'] . '\'');
        }
        if (isset($filter['to']) && $filter['to'] >= 0) {
            $collection->getSelect()->having($field . ' =< \'' . $filter['to'] . '\'');
        }
        return $collection;
    }
    
    /**
     * 
     * @param string $field
     * @param string $dir
     * @return \Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbycustomer_Grid
     */
    protected function _setDefaultSort($field, $dir) {
        $sort = $this->getRequest()->getParam('sort');
        if($sort) return $this;
        $this->getCollection()->getSelect()->order($this->_getRealFieldFromAlias($field)." $dir");
        return $this;
    }
    
    /**
     * Sort collection
     * 
     * @param collection $collection
     * @return collection
     */
    protected function _sortCollection($collection) {
        $sort = $this->getRequest()->getParam('sort');
        $dir = $this->getRequest()->getParam('dir');
        $field = $this->_getRealFieldFromAlias($sort);
        if ($field) {
            $collection->getSelect()->order("$field $dir");
        }
        return $collection;
    }
    
    /**
     * Get real filed from alias in sql
     * 
     * @param string $alias
     * @return string
     */
    protected function _getRealFieldFromAlias($alias){
        $field = null;
        switch ($alias) {
            case 'numberoforder':
                $field = 'COUNT(DISTINCT main_table.entity_id)';
                break;
            case 'sum_base_grand_total':
                $field = 'IFNULL(SUM(main_table.base_grand_total),0)';
                break;
            default :
                $field = $alias;
        }
        if($field != $alias){
            $field = new Zend_Db_Expr($field);
        }        
        return $field;
    }    
    
    protected function _filterTelephoneCallback($collection, $column){
        $value = $column->getFilter()->getValue();
        $collection->getSelect()->where('address.telephone like ?',"%$value%");
        return $this;
    }
    
    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/reportcustomergrid', array('type_id' => 'customer', 'top_filter' => $this->getRequest()->getParam('top_filter')));
    }
    
    public function getRowUrl($row) {
        return false;
    }    

}
