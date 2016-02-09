<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('barcode_action_log_id');
        $this->setDefaultSort('barcode_action_log_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
  
    protected function _prepareCollection() {
        $barcode = $this->getRequest()->getParam('barcode');
        $collection = Mage::getModel('inventorybarcode/barcode_actionlog')->getCollection()
                                    ->addFieldToFilter('barcode',$barcode);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('barcode_action_log_id', array(
            'header' => Mage::helper('inventorybarcode')->__('ID'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'barcode_action_log_id',
        ));
        
        $this->addColumn('created_by', array(
            'header' => Mage::helper('inventoryplus')->__('Action Owner'),
            'type' => 'text',
            'width' => '80px',
            'index' => 'created_by',
        ));
        
        
        $this->addColumn('barcode_action', array(
            'header' => Mage::helper('inventoryplus')->__('Action'),
            'type' => 'text',
            'width' => '250px',
            'index' => 'barcode_action',
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('inventoryplus')->__('Time Stamp'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        
        
        return parent::_prepareColumns();
    }
    
    
    public function getGridUrl() {
        return $this->getUrl('*/*/history', array(
                    '_current' => true
        ));
    }
    
    public function getRowUrl($row)
    {
        return false;
    }
}

