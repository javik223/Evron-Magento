<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Tab_Printbarcode extends Mage_Adminhtml_Block_Widget_Grid

{
    public function __construct()
    {        
        parent::__construct();
        $this->setId('barcodeGrid');
        $this->setDefaultSort('barcode_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Grid
     */
    protected function _prepareCollection()
    {        
        $collection = Mage::getModel('inventorybarcode/barcode')->getCollection();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Grid
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'barcode_id',
                'use_index' => true,
        ));

        $this->addColumn('barcode_id', array(
            'header'    => Mage::helper('inventorybarcode')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'barcode_id',
        ));

        $this->addColumn('barcode', array(
            'header'    => Mage::helper('inventorybarcode')->__('Barcode'),
            'align'     =>'left',
            'index'     => 'barcode',
        ));

        $barcodeAttributes = Mage::getModel('inventorybarcode/barcodeattribute')->getCollection()
                                                ->addFieldToFilter('attribute_status',1)
                                                ->addFieldToFilter('attribute_display',1);
        foreach($barcodeAttributes as $barcodeAttribute){
            $this->addColumn($barcodeAttribute->getAttributeCode(), array(
                'header'    => $barcodeAttribute->getAttributeName(),
                'align'     =>'left',
                'index'     => $barcodeAttribute->getAttributeCode(),
            ));
        }

        $this->addColumn('purchase_more', array(
                'header' => Mage::helper('inventoryplus')->__('Qty to print'),
                'align' => 'right',
                'width' => '80px',
                'index' => 'qty',
                'type' => 'input',
                'editable' => true,
                'sortable' => false,
                'filter' => false
        ));
        
        
        $this->addColumn('created_date', array(
            'header'    => Mage::helper('inventorybarcode')->__('Created Date'),
            'align'     =>'left',
            'index'     => 'created_date',
            'type' => 'datetime'
        ));
        
        $this->addColumn('barcode_status', array(
            'header'    => Mage::helper('inventorybarcode')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'barcode_status',
            'type'        => 'options',
            'options'     => array(
                1 => 'Enabled',
                0 => 'Disabled',
            ),
        ));

        return parent::_prepareColumns();
    }
    
    public function _getSelectedProducts() {
        
    }
    
    public function getGridUrl() {
        return $this->getUrl('*/*/printbarcodegrid', array('_secure' => true));
    }
}