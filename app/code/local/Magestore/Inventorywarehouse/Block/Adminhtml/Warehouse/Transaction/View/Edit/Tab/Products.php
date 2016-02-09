<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Transaction_View_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('transactionproductGrid');
        $this->setDefaultSort('warehouse_transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if ($this->getWarehouse() && $this->getWarehouse()->getId()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }
    
    protected function _prepareCollection() {
        $transaction_id = $this->getRequest()->getParam('transaction_id');
        $collection = Mage::getModel('inventorywarehouse/transaction_product')
            ->getCollection()
            ->addFieldToFilter('warehouse_transaction_id',$transaction_id);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type'  => 'number',
            'index' => 'product_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'product_name',
        ));
        
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '100px',
            'index' => 'product_sku'
        ));
        
        $this->addColumn('qty', array(
            'header' => Mage::helper('catalog')->__('Qty '),
            'width' => '80px',
            'index' => 'qty',
            'type' => 'number',
            'editable' => false,
            'default' => 0
        ));
        return parent::_prepareColumns();
    }

    public function getStockissuing() {
        return Mage::getModel('inventorywarehouse/transaction')
                ->load($this->getRequest()->getParam('transaction_id'));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/transactionproductView', array(
                '_current' => true,
                'transaction_id' => $this->getRequest()->getParam('transaction_id'),
                'store' => $this->getRequest()->getParam('store')
            ));
    }

    public function getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
}

