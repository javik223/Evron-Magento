<?php
class Magestore_Inventorywarehouse_Block_Adminhtml_Requeststock_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_selectedProducts = array();

    public function __construct() {
        parent::__construct();
        $this->setId('requeststockproductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $getSelectedProducts = $this->_getSelectedProducts();
        if (count($getSelectedProducts) > 0) {
            $this->_selectedProducts = $getSelectedProducts;
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    protected function getPostRqProducts() {
        return $this->getRequest()->getPost('requeststock_products', null);
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_selectedProducts;
            if (empty($productIds))
                $productIds = 0;
            if ($column->getFilter()->getValue())
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            elseif ($productIds)
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection() {
        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        if ($source && $source == 'others') {
            $warehouse = $target;
        } else {
            $warehouse = $source;
            if ($warehouse) {
                $collection->joinField('warehouse_qty', 'inventoryplus/warehouse_product', 'total_qty', 'product_id=entity_id', "{{table}}.warehouse_id=$warehouse", 'inner');
                $collection->addFieldToFilter('warehouse_qty', array('gt' => 0));
            } else {
                $id = $this->getRequest()->getParam('id');
                $collection->joinField('qty', 'inventorywarehouse/requeststock_product', 'qty', 'product_id=entity_id', "{{table}}.warehouse_requeststock_id=$id", 'inner');
            }
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $id = $this->getRequest()->getParam('id');
        $source = $this->getRequest()->getParam('source');
        $editable = true;
        if ($id)
            $editable = false;
        else
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_selectedProducts,
                'align' => 'center',
                'index' => 'entity_id',
                'use_index' => true,
                'editable' => false,
            ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'entity_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'index' => 'product_image',
            'filter' => false,
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage'
        ));
        if ($id) {
            $this->addColumn('qty_request', array(
                'header' => Mage::helper('catalog')->__('Requesting Qty'),
                'width' => '80px',
                'index' => 'qty',
                'type' => 'number',
                'editable' => $editable
            ));
        } else {
            if ($source != 'others') {
                $this->addColumn('warehouse_qty', array(
                    'header' => Mage::helper('catalog')->__('Total Qty in Source Warehouse'),
                    'width' => '80px',
                    'index' => 'warehouse_qty',
                    'type' => 'number'
                ));
            }

            $this->addColumn('qty_request', array(
                'header' => Mage::helper('catalog')->__('Requesting Qty'),
                'width' => '80px',
                'index' => 'qty_request',
                'editable' => true,
                'edit_only' => true,
                'type' => 'number',
                'renderer' => 'inventoryplus/adminhtml_renderer_input'
            ));
        }
        return parent::_prepareColumns();
    }

    public function _getSelectedProducts() {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }

    public function getSelectedProducts() {
        $requeststock = $this->getRequeststock();
        $products = array();
        if ($requeststock->getId()) {
            $productCollection = Mage::getResourceModel('inventorywarehouse/requeststock_product_collection')
                    ->addFieldToFilter('warehouse_requeststock_id', $requeststock->getId());
            if (count($productCollection)) {
                foreach ($productCollection as $product) {
                    $products[$product->getProductId()] = array('qty_request' => $product->getQty());
                }
                return $products;
            }
        }
        if ($requestStockProductImports = Mage::getModel('admin/session')->getData('requeststock_product_import')) {
            $productModel = Mage::getModel('catalog/product');
            foreach ($requestStockProductImports as $productImport) {
                $productId = $productModel->getIdBySku($productImport['SKU']);
                if ($productId)
                    $products[$productId] = array('qty_request' => $productImport['QTY']
                    );
            }
            return $products;
        }
        $productArrays = $this->getPostRqProducts();
        if (is_array($productArrays)) {
            if (count($productArrays) > 1) {
                $tempArr = array();
                foreach ($productArrays as $productStr) {
                    parse_str(urldecode($productStr), $tempArr);
                    foreach ($tempArr as $pId => $base64code) {
                        $codeArr = array();
                        parse_str(base64_decode($base64code), $codeArr);
                        $products[$pId] = array('qty_request' => $codeArr['qty_request']);
                    }
                }
                return $products;
            } else {
                $tempArr = array();
                parse_str(urldecode($productArrays[0]), $tempArr);
                foreach ($tempArr as $pId => $base64code) {
                    $codeArr = array();
                    parse_str(base64_decode($base64code), $codeArr);
                    $products[$pId] = array('qty_request' => $codeArr['qty_request']);
                }
                return $products;
            }
        }
        return $products;
    }

    public function getWarehouse() {
        return Mage::getModel('inventoryplus/warehouse')
                        ->load($this->getRequest()->getParam('warehouse_id'));
    }

    public function getRequeststock() {
        return Mage::getModel('inventorywarehouse/requeststock')
                        ->load($this->getRequest()->getParam('id'));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productsGrid', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
        ));
    }

    public function getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getRowUrl($row) {
        return false;
    }

}
