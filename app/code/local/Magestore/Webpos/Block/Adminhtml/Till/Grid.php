<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Adminhtml_Till_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('tillGrid');
        $this->setDefaultSort('till_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('webpos/till')->getCollection();
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            $collection->getSelect()
                    ->joinLeft(array('warehouse' => Mage::getModel('core/resource')->getTableName('inventoryplus/warehouse')), 'main_table.location_id=warehouse.warehouse_id', array('warehouse.warehouse_name as warehouse_name'));
        } else {

            $collection->getSelect()
                    ->joinLeft(array('location_table' => Mage::getModel('core/resource')->getTableName('webpos/userlocation')), 'main_table.location_id=location_table.location_id', array('location_table.display_name as location'));
        }
        $collection->getSelectCountSql();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns() {
        $this->addColumn('till_id', array(
            'header' => Mage::helper('webpos')->__('ID'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'till_id',
        ));
        $this->addColumn('till_name', array(
            'header' => Mage::helper('webpos')->__('Cash Drawer Name'),
            'align' => 'left',
            'index' => 'till_name',
        ));
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            $this->addColumn('warehouse', array(
                'header' => Mage::helper('webpos')->__('Warehouse'),
                'align' => 'left',
                'index' => 'warehouse_name',
            ));
        } else {
            $this->addColumn('location', array(
                'header' => Mage::helper('webpos')->__('Location'),
                'align' => 'left',
                'index' => 'location',
            ));
        }
        $this->addColumn('status', array(
            'header' => Mage::helper('webpos')->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enable',
                2 => 'Disable',
            ),
        ));
        $this->addColumn('action', array(
            'header' => Mage::helper('webpos')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('webpos')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));


        $this->addExportType('*/*/exportCsv', Mage::helper('webpos')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('webpos')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('till_id');
        $this->getMassactionBlock()->setFormFieldName('till');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('webpos')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('webpos')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('webpos/status')->getOptionArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('webpos')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('webpos')->__('Status'),
                    'values' => $statuses
                ))
        ));
        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
