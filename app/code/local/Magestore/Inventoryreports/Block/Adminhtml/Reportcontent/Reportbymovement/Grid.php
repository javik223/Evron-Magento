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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbymovement_Grid 
    extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid {
    protected $_filter = null;

    public function __construct() {
        parent::__construct();
        $this->setId('reportmovementGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setCountTotals(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::helper('inventoryreports/stockmovement')->getMovementReportCollection($this->getRequestData());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('type', array(
            'header' => Mage::helper('inventoryreports')->__('Type'),
            'sortable' => true,
            'index' => 'type',
            'type' => 'options',
            'options' => Mage::helper('inventoryreports')->getMovementType()
        ));

        $this->addColumn('numberoftransaction', array(
            'header' => Mage::helper('inventoryreports')->__('Total Transaction'),
            'align' => 'right',
            'index' => 'numberoftransaction',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('numbertransactionproduct', array(
            'header' => Mage::helper('inventoryreports')->__('Total SKU'),
            'align' => 'right',
            'index' => 'numbertransactionproduct',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));

        $this->addColumn('transactionproductqty', array(
            'header' => Mage::helper('inventoryreports')->__('Total Qty'),
            'align' => 'right',
            'index' => 'transactionproductqty',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
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
                'renderer'  => 'inventoryreports/adminhtml_reportcontent_reportbymovement_renderer_action'
        ));
        
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
            'transactionproductqty' => 0,
            'numbertransactionproduct' => 0,
            'numberoftransaction' => 0,
        );
        foreach ($this->getCollection() as $item) {
            foreach($totalsData as $field=>$value){
                $totalsData[$field] += (float) $item->getData($field);
            }
        }
        //First column in the grid
        $totalsData['type'] = $this->__('Totals');
        $totalsData['action'] = $this->__('Totals');
        $totals->setData($totalsData);
        return $totals;
    }       

    public function getGridUrl() {
        return $this->getUrl('adminhtml/inr_report/reportmovementgrid', array('type_id' => 'stockmovement', 'top_filter' => $this->getRequest()->getParam('top_filter')));
    }
    
    public function getRowUrl($row) {
        return false;
    }    

    protected function _getRealFieldFromAlias($alias) {
        $field = null;
        switch ($alias) {
            case 'transactionproductqty':
                $field = 'IFNULL(SUM(transactionproduct.qty),0)';
                $field = $this->isStockOutReport() ? 'IFNULL(-SUM(transactionproduct.qty),0)' : $field;
                break;
            case 'numbertransactionproduct':
                $field = 'IFNULL(COUNT(DISTINCT transactionproduct.product_id),0)';
                break;
            case 'numberoftransaction':
                $field = 'IFNULL(COUNT(DISTINCT main_table.warehouse_transaction_id),0)';
                break;   
            default:
                $field = $alias;
        }
        if($field != $alias){
            $field = new Zend_Db_Expr($field);
        }        
        return $field;
    }

}
