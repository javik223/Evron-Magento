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
class Magestore_Inventorybarcode_Block_Adminhtml_Barcodeattribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('barcodeattributeGrid');
        $this->setDefaultSort('barcode_attribute_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Inventorybarcode_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('inventorybarcode/barcodeattribute')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Inventorybarcode_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('barcode_attribute_id', array(
            'header'    => Mage::helper('inventorybarcode')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'barcode_attribute_id',
        ));

        $this->addColumn('attribute_name', array(
            'header'    => Mage::helper('inventorybarcode')->__('Attribute Name'),
            'align'     =>'left',
            'index'     => 'attribute_name',
        ));

        $this->addColumn('attribute_code', array(
            'header'    => Mage::helper('inventorybarcode')->__('Attribute Code'),
            'width'     => '150px',
            'index'     => 'attribute_code',
        ));
        
        $this->addColumn('attribute_display', array(
            'header'    => Mage::helper('inventorybarcode')->__('Display On Barcode List'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'attribute_display',
            'type'        => 'options',
            'options'     => array(
                1 => Mage::helper('inventorybarcode')->__('Yes'),
                0 => Mage::helper('inventorybarcode')->__('No'),
            ),
        ));
        
//        $this->addColumn('attribute_require', array(
//            'header'    => Mage::helper('inventorybarcode')->__('Require'),
//            'align'     => 'left',
//            'width'     => '80px',
//            'index'     => 'attribute_require',
//            'type'        => 'options',
//            'options'     => array(
//                1 => Mage::helper('inventorybarcode')->__('Yes'),
//                0 => Mage::helper('inventorybarcode')->__('No'),
//            ),
//        ));
//        
//        
//        $this->addColumn('attribute_unique', array(
//            'header'    => Mage::helper('inventorybarcode')->__('Unique'),
//            'align'     => 'left',
//            'width'     => '80px',
//            'index'     => 'attribute_unique',
//            'type'        => 'options',
//            'options'     => array(
//                1 => Mage::helper('inventorybarcode')->__('Yes'),
//                0 => Mage::helper('inventorybarcode')->__('No'),
//            ),
//        ));
        

        $this->addColumn('attribute_status', array(
            'header'    => Mage::helper('inventorybarcode')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'attribute_status',
            'type'        => 'options',
            'options'     => array(
                1 => Mage::helper('inventorybarcode')->__('Enabled'),
                0 => Mage::helper('inventorybarcode')->__('Disabled'),
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
                        'caption'    => Mage::helper('inventorybarcode')->__('Edit'),
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
        $this->setMassactionIdField('barcode_attribute_id');
        $this->getMassactionBlock()->setFormFieldName('inventorybarcode');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'        => Mage::helper('inventorybarcode')->__('Delete'),
            'url'        => $this->getUrl('*/*/massDelete'),
            'confirm'    => Mage::helper('inventorybarcode')->__('Are you sure?')
        ));

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
        
        $displays = Mage::getModel('inventorybarcode/show')->getOptionArray();

        
        
        $this->getMassactionBlock()->addItem('barcodelist', array(
            'label'=> Mage::helper('inventorybarcode')->__('Display on Barcode list'),
            'url'    => $this->getUrl('*/*/massDisplaybarcodelist', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'barcodelist',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('inventorybarcode')->__('Show'),
                    'values'=> $displays
                ))
        ));
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