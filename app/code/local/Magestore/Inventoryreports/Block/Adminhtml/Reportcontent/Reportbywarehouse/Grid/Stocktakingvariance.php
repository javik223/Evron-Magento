<?php
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Grid_Stocktakingvariance extends  Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('stocktakingvariancegrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(false);
    }
    protected function _prepareCollection() {
		$requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if (empty($requestData)) {
            $requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();
        }
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
        $collection = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking_product')->getCollection();
		/* Get product name and sku from catalog, because physicalstocktaking_product does not have them */
		if ($storeId = $this->getRequest()->getParam('store', 0))
            $collection->addStoreFilter($storeId);
		$productAttributes = array('product_name' => 'name');
    	foreach ($productAttributes as $alias => $attributeCode) {
    		$tableAlias = $attributeCode . '_table';
    		$attribute = Mage::getSingleton('eav/config')
    		->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
    		$collection->getSelect()->joinLeft(
    				array($tableAlias => $attribute->getBackendTable()),
    				"main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}",
    				array($alias => 'value')
    		);
    	}
		$collection->join(
			'catalog/product',
			'product_id=`catalog/product`.entity_id',
			array('product_sku' => 'sku')
		);
		/* Endl get product name and sku from catalog */
		/* Join physicalstocktaking to get created_at */
			$collection->getSelect()->joinLeft(
    				array('physicalstocktaking' =>$collection->getTable('inventoryphysicalstocktaking/physicalstocktaking')),
    				"main_table.physicalstocktaking_id = physicalstocktaking.physicalstocktaking_id",
    				array('created_at','warehouse_id')
    		);
		/* Endl join physicalstocktaking to get created_at */
        $collection->getSelect()->columns(array('difference' => new Zend_Db_Expr("(adjust_qty - old_qty)")));
		$collection->getSelect()->where("physicalstocktaking.created_at BETWEEN '". $gettime['date_from'] ."' AND '".$gettime['date_to']."'");
		if(isset($requestData['warehouse_select']) && $requestData['warehouse_select']){
			$warehouse_id = $requestData['warehouse_select'];
			$collection->getSelect()->where("physicalstocktaking.warehouse_id = '$warehouse_id'");
		}
		$this->setCollection($collection);
		$this->_prepareTotals('adjust_qty,old_qty,difference');
        return parent::_prepareCollection();
    }
    protected function _prepareTotals($columns = null) {
        $columns = explode(',', $columns);
        if (!$columns) {
            return;
        }
        $this->_countTotals = true;
        $totals = new Varien_Object();
        $fields = array();
        foreach ($columns as $column) {
            $fields[$column] = 0;
        }
        foreach ($this->getCollection() as $item) {
            foreach ($fields as $field => $value) {
                $fields[$field]+=$item->getData($field);
            }
        }
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $fields['product_name'] = 'Totals';
        $totals->setData($fields);
        $this->setTotals($totals);
        return;
    }

    protected function _prepareColumns() {
		$this->addColumn('product_name', array(
			'header' => Mage::helper('inventoryreports')->__('Product Name'),
			'align' => 'left',
			'index' => 'product_name',
			'type' => 'text'
		));
		$this->addColumn('product_sku', array(
			'header' => Mage::helper('inventoryreports')->__('Product SKU'),
			'align' => 'left',
			'index' => 'product_sku',
			'type' => 'text'
		));
		$this->addColumn('created_at', array(
			'header' => Mage::helper('inventoryreports')->__('Created At'),
			'align' => 'left',
			'index' => 'created_at',
			'type' => 'date'
		));		
		$this->addColumn('old_qty', array(
			'header' => Mage::helper('inventoryreports')->__('Qty Before Stocktake'),
			'align' => 'right',
			'index' => 'old_qty',
			'type' => 'number',
			'width' => '100px'
		));
		$this->addColumn('adjust_qty', array(
			'header' => Mage::helper('inventoryreports')->__('Qty After Stocktake'),
			'align' => 'right',
			'index' => 'adjust_qty',
			'type' => 'number',
			'width' => '100px'
		));
		$this->addColumn('difference', array(
			'header' => Mage::helper('inventoryreports')->__('Qty Variance'),
			'align' => 'right',
			'index' => 'difference',
			'type' => 'number',
			'width' => '100px',
			'filter' => false,
			'sortable' => false
		));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }
}