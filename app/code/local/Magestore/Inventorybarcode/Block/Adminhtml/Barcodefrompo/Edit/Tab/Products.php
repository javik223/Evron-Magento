<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcodefrompo_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('product_id');
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
                $this->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));
            elseif ($productIds)
                $this->getCollection()->addFieldToFilter('product_id', array('nin' => $productIds));
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection() {
        $purchaseOrderId = $this->getRequest()->getParam('po_id');
        $collection = Mage::getModel('inventorypurchasing/purchaseorder_product')->getCollection()
                ->addFieldToFilter('purchase_order_id', $purchaseOrderId);
        $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);

        foreach ($collection as $item) {
            $item->setData('supplier_supplier_id', $purchaseOrders->getSupplierId());
            $item->setData('purchaseorder_purchase_order_id', $purchaseOrders->getId());
            $warehouse = $purchaseOrders->getWarehouseId();
            $warehouseIds = explode(',', $warehouse);
            $item->setData('warehouse_warehouse_id', $warehouseIds[0]);
        }

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
            'index' => 'product_id',
            'use_index' => 'product_id'
        ));


        $this->addColumn('product_id', array(
            'header' => Mage::helper('inventorybarcode')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type' => 'number',
            'index' => 'product_id',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('inventorybarcode')->__('Name'),
            'align' => 'left',
            'index' => 'product_name'
        ));


        $this->addColumn('barcode', array(
            'header' => Mage::helper('inventorybarcode')->__('Barcode'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'barcode',
            'type' => 'input',
            'editable' => true,
            'edit_only' => true,
            'renderer' => 'inventorybarcode/adminhtml_barcodefrompo_edit_renderer_custom',
        ));


        $this->addColumn('product_sku', array(
            'header' => Mage::helper('inventorybarcode')->__('SKU'),
            'width' => '80px',
            'index' => 'product_sku'
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
            'renderer' => 'inventorybarcode/adminhtml_barcodefrompo_edit_renderer_select',
        ));
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {   
            $this->addColumn('supplier_supplier_id', array(
                'header' => Mage::helper('inventorybarcode')->__('Supplier'),
                'width' => '120px',
                'index' => 'supplier_supplier_id',
                'type' => 'text',
                'renderer' => 'inventorybarcode/adminhtml_barcodefrompo_edit_renderer_text',
            ));

            $this->addColumn('purchaseorder_purchase_order_id', array(
                'header' => Mage::helper('inventorybarcode')->__('Purchase Order'),
                'width' => '120px',
                'index' => 'purchaseorder_purchase_order_id',
                'type' => 'text',
                'options' => Mage::helper('inventorybarcode')->getPurchaseOrderList(),
                'renderer' => 'inventorybarcode/adminhtml_barcodefrompo_edit_renderer_text',
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
            'renderer' => 'inventorybarcode/adminhtml_barcodefrompo_edit_renderer_select',
        ));


        return parent::_prepareColumns();
    }

    public function _getSelectedProducts() {
        $productArrays = $this->getBarcodeProducts();
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
        } else {
            $purchaseOrderId = $this->getRequest()->getParam('po_id');
            $productDatas = Mage::getModel('inventorypurchasing/purchaseorder_product')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrderId);
            $warehouseIds = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId)->getWarehouseId();
            $warehouse = explode(',', $warehouseIds);
            $barcodeAttributes = Mage::getModel('inventorybarcode/barcodeattribute')->getCollection()->addFieldToFilter('attribute_display', 1)->addFieldToFilter('attribute_status', 1)->addFieldToFilter('attribute_type', 'custom');
                foreach ($productDatas as $productData) {
                    $products[$productData->getProductId()] = array('barcode_status' => 1,
                        'barcode' => '',
                        'warehouse_warehouse_id' => $warehouse[0],
                        'qty' => $productData->getQty());
                    
                    foreach ($barcodeAttributes as $barcodeAttribute) {
                        $products[$productData->getProductId()][$barcodeAttribute->getAttributeCode()] = '';
                    }
                }
            

            
        }


        return $products;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productsGridfrompo', array(
                    '_current' => true
        ));
    }

    public function getRowUrl($row) {
        return false;
    }

}
