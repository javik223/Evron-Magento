<?php

class Magestore_Inventorydropship_Block_Adminhtml_Supplier_Dropship extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('dropshipGrid');
        $this->setDefaultSort('dropship_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_dropships') {
            $dropshipIds = $this->_getSelectedDropships();
            if (empty($dropshipIds)) {
                $dropshipIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('dropship_id', array('in' => $dropshipIds));
            } else {
                if ($dropshipIds) {
                    $this->getCollection()->addFieldToFilter('dropship_id', array('nin' => $dropshipIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection() {
        $supplierId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('inventorydropship/inventorydropship')
                ->getCollection()
                ->addFieldToFilter('supplier_id', $supplierId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('dropship_id', array(
            'header' => Mage::helper('inventorydropship')->__('Drop Shipments #'),
            'align' => 'left',
            'width' => '10px',
            'type' => 'number',
            'index' => 'dropship_id',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('inventorydropship')->__('Ship to Name'),
            'align' => 'left',
            'index' => 'shipping_name',
        ));


        $this->addColumn('increment_id', array(
            'header' => Mage::helper('inventorydropship')->__('Order #'),
            'align' => 'left',
            'index' => 'increment_id',
            'renderer' => 'inventorydropship/adminhtml_inventorydropship_renderer_order',
        ));

        $this->addColumn('created_on', array(
            'header' => Mage::helper('catalog')->__('Date Created'),
            'sortable' => true,
            'width' => '150',
            'type' => 'datetime',
            'index' => 'created_on'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('inventorydropship')->__('Status'),
            'align' => 'left',
            'width' => '120',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('inventorydropship')->getDropshipStatus()
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('inventorydropship')->__('Action'),
            'width' => '6px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('inventorydropship')->__('View'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/supplierdropshipGrid', array('_current' => true, 'order_id' => $this->getRequest()->getParam('order_id')));
    }

    public function _getSelectedDropships() {
        $dropships = $this->getDropships();
        if (!is_array($dropships)) {
            $dropships = array_keys($this->getSelectedRelatedDropships());
        }
        return $dropships;
    }

    public function getSelectedRelatedProducts() {
        $dropships = array();
        $dropshipCollections = Mage::getModel('inventorydropship/inventorydropship')->getCollection();
        foreach ($dropshipCollections as $dropshipCollection) {
            $dropships[$dropshipCollection->getId()] = array('position' => 1);
        }
        return $dropships;
    }

}
