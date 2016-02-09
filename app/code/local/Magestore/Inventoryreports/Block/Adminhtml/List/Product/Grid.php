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
class Magestore_Inventoryreports_Block_Adminhtml_List_Product_Grid extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productdetailsGrid');
        $this->setDefaultSort('transactionproductqty');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        if (!$this->getFilterData())
            $this->setFilterData(new Varien_Object());
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $filter = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $transactionIdsArray = explode(',', $filter['movementids']);
        $collection = Mage::getResourceModel('inventorywarehouse/transaction_product_collection');
        $collection->addFieldToFilter('main_table.warehouse_transaction_id', array('in' => $transactionIdsArray));
        $collection->getSelect()
                ->columns(array(
                    'transactionproductqty' => 'IFNULL(SUM(main_table.qty),0)',
                ))
                ->group('main_table.product_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('product_id', array(
            'header' => Mage::helper('inventoryreports')->__('Product ID'),
            'sortable' => true,
            'index' => 'product_id',
            'type' => 'number',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('inventoryreports')->__('Name'),
            'sortable' => true,
            'index' => 'product_name',
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('inventoryreports')->__('SKU'),
            'sortable' => true,
            'index' => 'product_sku',
        ));

        $this->addColumn('transactionproductqty', array(
            'header' => Mage::helper('inventoryreports')->__('Total Qty'),
            'sortable' => true,
            'index' => 'transactionproductqty',
            'type' => 'number',
            'filter_condition_callback' => array($this, '_filterNumberCallback'),
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/detailsGrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    /**
     * Get real field in query
     * 
     * @param sting $alias
     * @return string
     */
    protected function _getRealFieldFromAlias($alias) {
        $field = $alias;
        switch ($alias) {
            case 'transactionproductqty':
                $field = 'IFNULL(SUM(main_table.qty),0)';
                break;
        }
        return $field;
    }

}
