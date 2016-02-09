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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {        
        parent::__construct();
        $this->setId('barcodeGrid');
        $this->setDefaultSort('barcode_id');
        $this->setDefaultDir('DESC');
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
        
        $this->addColumn('qty', array(
            'header'    => Mage::helper('inventorybarcode')->__('Qty'),
            'align'     =>'left',
            'index'     => 'qty',
            'type' => 'number'
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

        $this->addColumn('action',
            array(
                'header'    =>    Mage::helper('inventorybarcode')->__('Action'),
                'width'        => '100',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('inventorybarcode')->__('View'),
                        'url'        => array('base'=> '*/*/edit'),
                        'field'        => 'id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'index'        => 'stores',
                'is_system'    => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventorybarcode')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventorybarcode')->__('XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Inventorybarcode_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('barcode_id');
        $this->getMassactionBlock()->setFormFieldName('inventorybarcode');

        
        $statuses = Mage::getSingleton('inventorybarcode/status')->getOptionArray();

       
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('inventorybarcode')->__('Change status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('inventorybarcode')->__('Status'),
                    'values'=> $statuses
                ))
        ));
       
        /* remove by kai - print barcode */
//        $this->getMassactionBlock()->addItem('print_barcode', array(
//             'label'    => Mage::helper('inventorybarcode')->__('Print barcodes'),
//             'url'      => $this->getUrl('*/*/massPrint')
//        ));
        /* end remove by kai - print barcode */
        return $this;
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}