<?php

class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Physicalstocktaking_Listphysicalstocktaking_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('listphysicalstocktakingGrid');
        $this->setDefaultSort('physicalstocktaking_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventory_Block_Adminhtml_Adjuststock_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking')->getCollection();
        $resource = Mage::getSingleton('core/resource');
        $collection->getSelect()
                ->columns(array('physical_status' => 'status', 'physical_warehouse_name' => 'warehouse_name', 'physical_created_at' => 'created_at'))
                ->join(array('warehouse' => $resource->getTableName("erp_inventory_warehouse")), "main_table.warehouse_id = warehouse.warehouse_id", array('warehouse.*'));

        $filter = $this->getParam($this->getVarNameFilter(), null);
        $condorder = '';
        $status = '';
        if ($filter) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
//                if ($value == 'physical_created_at') {
//                    $condorder = $key;
//                }
                if ($value == 'physical_status') {
                    $status['value'] = $key;
                }
            }
        }

        if ($status) {
            $collection->addFieldToFilter('main_table.status', (int) $status['value']);
        }




        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _setFilterValues($data) {
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset($data[$columnId]) && (!empty($data[$columnId]) || strlen($data[$columnId]) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                if ($columnId != 'physical_status')
                    $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Adjuststock_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('physicalstocktaking_id', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'align' => 'right',
            'type' => 'number',
            'index' => 'physicalstocktaking_id'
        ));

        $this->addColumn('physical_created_at', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Created on'),
            'type' => 'date',
            'width' => '150px',
            'align' => 'right',
            'index' => 'physical_created_at',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ));

        $this->addColumn('create_by', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Created by'),
            'width' => '80px',
            'align' => 'left',
            'index' => 'create_by'
        ));

        $this->addColumn('physical_warehouse_name', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Stocktake Warehouse'),
            'width' => '150px',
            'align' => 'left',
            'index' => 'physical_warehouse_name',
            'filter_condition_callback' => array($this, 'filterWarehouseName')
        ));


        $this->addColumn('warehouse_contact', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Warehouse\'s Contact'),
            'width' => '150px',
            'align' => 'left',
            'index' => 'manager_name',
        ));

        $this->addColumn('warehouse_email', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Warehouse\'s Email'),
            'width' => '150px',
            'align' => 'left',
            'index' => 'manager_email',
        ));

        $this->addColumn('warehouse_phone', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Warehouse\'s Phone'),
            'width' => '150px',
            'align' => 'right',
            'index' => 'telephone',
        ));

        $this->addColumn('warehouse_country', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Warehouse\'s Country'),
            'width' => '150px',
            'align' => 'left',
            'index' => 'country_id',
            'type' => 'options',
            'options' => Mage::helper('inventoryplus')->getCountryList()
        ));

        $this->addColumn('physical_status', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'physical_status',
            'type' => 'options',
            'options' => array(
                0 => 'Pending',
                1 => 'Completed',
                2 => 'Canceled',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getPhysicalstocktakingId',
            'renderer' =>   'inventoryphysicalstocktaking/adminhtml_physicalstocktaking_listphysicalstocktaking_renderer_action',
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryphysicalstocktaking')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventoryphysicalstocktaking')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getPhysicalstocktakingId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

    public function filterCreatedOn($collection, $column) {
        $condorder = $column->getFilter()->getValue();
        if ($condorder) {
            $from = $to = '';
            if (isset($condorder['from']))
                $from = $condorder['from'];
            if (isset($condorder['to']))
                $to = $condorder['to'];
            if ($from) {
                $from = date('Y-m-d', strtotime($from));
                $collection->addFieldToFilter('main_table.created_at', array('gteq' => $from));
            }
            if ($to) {
                $to = date('Y-m-d', strtotime($to));
                $to .= ' 23:59:59';
                $collection->addFieldToFilter('main_table.created_at', array('lteq' => $to));
            }
        }

        return $this;
    }

    public function filterWarehouseName($collection, $column) {
        $value = $column->getFilter()->getValue();
        $collection->addFieldToFilter('main_table.warehouse_name', array('like' => '%' . $value . '%'));
        return $this;
    }

}
