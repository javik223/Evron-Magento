<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        
        if (Mage::getModel('admin/session')->getData('barcode_product_import')) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
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

        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->_getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id',
            'use_index' => true,
        ));



        $this->addColumn('entity_id', array(
            'header' => Mage::helper('inventorybarcode')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type' => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('inventorybarcode')->__('Name'),
            'align' => 'left',
            'index' => 'name'
        ));


        $this->addColumn('barcode', array(
            'header' => Mage::helper('inventorybarcode')->__('Barcode'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'barcode',
            'type' => 'input',
            'editable' => true,
            'edit_only' => true,
			'filter' => false,
			'sortable' => false,
            'renderer' => 'inventorybarcode/adminhtml_barcode_edit_renderer_custom',
        ));


        $this->addColumn('product_sku', array(
            'header' => Mage::helper('inventorybarcode')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('inventorybarcode')->__('Image'),
            'width' => '90px',
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage',
            'index' => 'product_image',
            'filter' => false
        ));
        
        $this->addColumn('qty', array(
            'header' => Mage::helper('inventorybarcode')->__('Qty'),
            'align' => 'left',
            'type' => 'number',
            'validate_class' => 'validate-number required-entry',
            'index' => 'qty',
            'editable' => true,
            'edit_only' => true,
			'filter' => false,
			'sortable' => false
        ));
        
        $fields = Mage::helper('inventorybarcode/attribute')->getBarcodeProductFields();
        foreach ($fields as $field) {
            $values = explode('_', $field);
            $label = Mage::getModel('inventorybarcode/barcodeattribute')->load($field, 'attribute_code')->getAttributeName();
            if ($values[0] == 'custom') {

                $this->addColumn($field, array(
                    'header' => $label,
                    'width' => '120px',
                    'index' => $field,
                    'type' => 'input',
                    'editable' => true,
                    'edit_only' => true,
                ));
            }
        }

        $this->addColumn('warehouse_warehouse_id', array(
            'header' => Mage::helper('inventorybarcode')->__('Warehouse'),
            'width' => '120px',
            'index' => 'warehouse_warehouse_id',
            'type' => 'select',
            'editable' => true,
            'edit_only' => true,
            'options' => Mage::helper('inventorybarcode')->getWarehouseList(),
            'renderer' => 'inventorybarcode/adminhtml_barcode_edit_renderer_select',
        ));
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {   
            $this->addColumn('supplier_supplier_id', array(
                'header' => Mage::helper('inventorybarcode')->__('Supplier'),
                'width' => '120px',
                'index' => 'supplier_supplier_id',
                'type' => 'select',
                'editable' => true,
                'edit_only' => true,
                'options' => Mage::helper('inventorybarcode')->getSupplierList(),
                'renderer' => 'inventorybarcode/adminhtml_barcode_edit_renderer_select',
            ));

            $this->addColumn('purchaseorder_purchase_order_id', array(
                'header' => Mage::helper('inventorybarcode')->__('Purchase Order'),
                'width' => '120px',
                'index' => 'purchaseorder_purchase_order_id',
                'type' => 'select',
                'editable' => true,
                'edit_only' => true,
                'options' => Mage::helper('inventorybarcode')->getPurchaseOrderList(),
                'renderer' => 'inventorybarcode/adminhtml_barcode_edit_renderer_select',
            ));
        }
        $this->addColumn('barcode_status', array(
            'header' => Mage::helper('inventorybarcode')->__('Status'),
            'width' => '90px',
            'index' => 'barcode_status',
            'type' => 'select',
            'editable' => true,
            'edit_only' => true,
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            'renderer' => 'inventorybarcode/adminhtml_barcode_edit_renderer_select',
        ));


        return parent::_prepareColumns();
    }

    public function _getSelectedProducts() {
        $productArrays = $this->getProducts();
        $products = '';
        $warehouseProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                parse_str(urldecode($productArray), $warehouseProducts);
                if (count($warehouseProducts)) {
                    foreach ($warehouseProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
       
        if (!is_array($products) || Mage::getModel('admin/session')->getData('barcode_product_import')) {
            $products = array_keys($this->getSelectedProducts());
        }

        return $products;
    }

    public function getSelectedProducts() {

        $products = array();
        if ($barcodeProducts = Mage::getModel('admin/session')->getData('barcode_product_import')) {
            foreach ($barcodeProducts as $barcodeProduct) {
                if(isset($barcodeProduct['BARCODE']))
                    $products[$barcodeProduct['PRODUCT_ID']]['barcode'] = $barcodeProduct['BARCODE'];
                if(isset($barcodeProduct['WAREHOUSE_ID']))
                    $products[$barcodeProduct['PRODUCT_ID']]['warehouse_warehouse_id'] = $barcodeProduct['WAREHOUSE_ID'];
                if(isset($barcodeProduct['SUPPLIER_ID']))
                    $products[$barcodeProduct['PRODUCT_ID']]['supplier_supplier_id'] = $barcodeProduct['SUPPLIER_ID'];
                if(isset($barcodeProduct['PURCHASE_ORDER_ID']))
                    $products[$barcodeProduct['PRODUCT_ID']]['purchaseorder_purchase_order_id'] = $barcodeProduct['PURCHASE_ORDER_ID'];
                if(isset($barcodeProduct['BARCODE_STATUS']))
                    $products[$barcodeProduct['PRODUCT_ID']]['barcode_status'] = $barcodeProduct['BARCODE_STATUS'];
                if(isset($barcodeProduct['QTY']))
                    $products[$barcodeProduct['PRODUCT_ID']]['qty'] = $barcodeProduct['QTY'];
                
                $barcodeAttributes = Mage::getModel('inventorybarcode/barcodeattribute')->getCollection()->addFieldToFilter('attribute_display', 1)->addFieldToFilter('attribute_status', 1)->addFieldToFilter('attribute_type', 'custom');
               
                foreach ($barcodeAttributes as $barcodeAttribute) {
                    
                    if (isset($barcodeProduct[strtoupper($barcodeAttribute->getAttributeCode())])) {
                        
                        $products[$barcodeProduct['PRODUCT_ID']] = array($barcodeAttribute->getAttributeCode() => $barcodeProduct[strtoupper($barcodeAttribute->getAttributeCode())]);
                    }
                }
               
            }
            
        }

        return $products;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productsGrid', array(
                    '_current' => true
        ));
    }

    public function getRowUrl($row) {
        return false;
    }

}
