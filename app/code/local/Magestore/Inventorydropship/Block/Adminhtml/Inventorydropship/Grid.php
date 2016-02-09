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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('inventorydropshipGrid');
        $this->setDefaultSort('inventorydropship_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('inventorydropship/inventorydropship')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('inventorydropship_id', array(
            'header'    => Mage::helper('inventorydropship')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'inventorydropship_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('inventorydropship')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        $this->addColumn('content', array(
            'header'    => Mage::helper('inventorydropship')->__('Item Content'),
            'width'     => '150px',
            'index'     => 'content',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('inventorydropship')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'        => 'options',
            'options'     => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    =>    Mage::helper('inventorydropship')->__('Action'),
                'width'        => '100',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('inventorydropship')->__('Edit'),
                        'url'        => array('base'=> '*/*/edit'),
                        'field'        => 'id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'index'        => 'stores',
                'is_system'    => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventorydropship')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventorydropship')->__('XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('inventorydropship_id');
        $this->getMassactionBlock()->setFormFieldName('inventorydropship');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'        => Mage::helper('inventorydropship')->__('Delete'),
            'url'        => $this->getUrl('*/*/massDelete'),
            'confirm'    => Mage::helper('inventorydropship')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('inventorydropship/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('inventorydropship')->__('Change status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('inventorydropship')->__('Status'),
                    'values'=> $statuses
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