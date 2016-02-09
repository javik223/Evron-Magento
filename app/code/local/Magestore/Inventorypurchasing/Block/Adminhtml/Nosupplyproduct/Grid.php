<?php

class Magestore_Inventorypurchasing_Block_Adminhtml_Nosupplyproduct_Grid extends Mage_Adminhtml_Block_Widget_Grid {


    public function __construct() {
        parent::__construct();
        $this->setId('nosupplyproductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $resource = Mage::getModel('core/resource');
        $warehouse = Mage::helper('inventoryplus/stock')->getWarehouse();
        if (!$warehouse) {
            return parent::_prepareCollection();
        }
        $warehouseId = $warehouse->getId();
        if (!$warehouse->getId()) {
            $warehouseId = 0;
        }
        $supplierProductIds = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $supplierProductSql = "SELECT DISTINCT `product_id` FROM ".$resource->getTableName('erp_inventory_supplier_product');
        $supplierProducts = $readConnection->fetchAll($supplierProductSql);
        foreach($supplierProducts as $result){
            $supplierProductIds[] = $result['product_id'];
        }
        $collection = Mage::getResourceModel('inventoryplus/product_collection');
        $collection->addFieldToFilter('entity_id',array('nin' => $supplierProductIds));
        $collection->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        $collection->getSelect()
                ->join(
                    array('warehouse_product' => $collection->getTable('inventoryplus/warehouse_product')), 'e.entity_id=warehouse_product.product_id', array('total_qty', 'available_qty')
        );
        
        $collection->getSelect()->group('e.entity_id');
        $collection->setResetHaving(true);
        $collection->setIsGroupCountSql(true);
        //echo $collection->getSelect()->__toString();die();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $warehouse = Mage::helper('inventoryplus/stock')->getWarehouse();
        if (!$warehouse)
            return parent::_prepareColumns();
        $warehouseId = $warehouse->getId();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type' => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));
        
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));

        
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage',
            'index' => 'product_image',
            'filter' => false
        ));

        $this->addColumn('product_status', array(
            'header' => Mage::helper('catalog')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('product_price', array(
            'header' => Mage::helper('catalog')->__('Price'),
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));
        $this->addColumn('warehouse_id', array(
            'header' => Mage::helper('catalog')->__('Warehouse') . '<br/>' . Mage::helper('catalog')->__('(Phys. / Avail. Qty)'),
            'type' => 'options',
            'sortable' => false,
            'filter' => false,
            'options' => Mage::helper('inventoryplus/warehouse')->getAllWarehouseName(),
            'renderer' => 'inventoryplus/adminhtml_stock_renderer_warehouse',
            'align' => 'left'
        ));
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/noSupplyProductGrid', array(
                    '_current' => true
        ));
    }

    public function getRowUrl($row) {
        return false;
    }

}
