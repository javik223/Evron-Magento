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
 * Inventorybarcode Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Helper_Data extends Mage_Core_Helper_Data {

    /**
     * get Warehouse list
     * 
     * return Array
     */
    public function getWarehouseList() {
        $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->setOrder('warehouse_name', 'ASC');
        $values = array();
        $values[0] = $this->__('Choose Warehouse');
        foreach ($warehouses as $warehouse) {
            $values[$warehouse->getId()] = $warehouse->getWarehouseName();
        }

        return $values;
    }

    /**
     * get Supplier list
     * 
     * return Array
     */
    public function getSupplierList() {
        $suppliers = Mage::getModel('inventorypurchasing/supplier')->getCollection()
                ->setOrder('supplier_name', 'ASC');
        $values = array();
        $values[0] = $this->__('Choose Supplier');
        foreach ($suppliers as $supplier) {
            $values[$supplier->getId()] = $supplier->getSupplierName();
        }

        return $values;
    }

    /**
     * get validate for barcode field
     * 
     * return String
     */
    public function getBarcodeName() {
        $barcodeType = Mage::getStoreConfig('inventoryplus/barcode/barcode_type');
        $barcodes = Mage::getModel('inventorybarcode/barcodetypes')->toOptionArray();
        $return = '';

        foreach ($barcodes as $id => $name) {
            if ($name['value'] == $barcodeType) {
                $return = $name['label'];
            }
        }

        return $return;
    }

    /**
     * get validate for barcode field
     * 
     * return String
     */
    public function getValidateBarcode() {
//        $barcodeType = Mage::getStoreConfig('inventoryplus/barcode/barcode_type');
//        $return = '';
//        switch ($barcodeType){
//            case 'code128':
//                $return = 'required-entry';
//                break;
//            case 'upc':
//                $return = 'required-entry validate-number validate-length minimum-length-12 maximum-length-12';
//                break;
//            case 'ean':
//                $return = 'required-entry validate-number validate-length minimum-length-8 maximum-length-14';
//                break;
//            case 'code39':
//                $return = 'required-entry';
//                break;
//            case 'interleaved-2-of-5':
//                $return = 'required-entry validate-number';
//                break;
//            case 'codabar':
//                $return = 'required-entry';
//                break;
//            default:
        $return = 'required-entry';
//                break;
//        }

        return $return;
    }

    /**
     * get Purchase Order list
     * 
     * return Array
     */
    public function getPurchaseOrderList() {
        $purchaseorders = Mage::getModel('inventorypurchasing/purchaseorder')->getCollection()
                ->setOrder('purchase_order_id', 'DESC');
        $values = array();
        $values[0] = $this->__('Choose Purchase Order');
        foreach ($purchaseorders as $purchaseorder) {
            $values[$purchaseorder->getId()] = $this->__('PO #,%s', $purchaseorder->getId());
        }

        return $values;
    }

    /**
     * get value for barcode
     * 
     * param String $table, String $column, int $productId, array $data
     * return Array
     */
    public function getValueForBarcode($table, $column, $productId, $data) {

        if ($table == 'product') {

            $model = Mage::getModel('catalog/product')->load($productId);
            return $model->getData($column);
        }

        if ($table == 'warehouse') {
            $array = array();
            foreach ($data['warehouse_warehouse_id'] as $data['warehouse_warehouse_id']) {
                $model = Mage::getModel('inventoryplus/warehouse')->load($data['warehouse_warehouse_id']);
                $array[] = array($column => $model->getData($column));
            }
            return $array;
        }
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {   
            if ($table == 'supplier') {

                $model = Mage::getModel('inventorypurchasing/supplier')->load($data['supplier_supplier_id']);
                return $model->getData($column);
            }

            if ($table == 'purchaseorder') {

                $model = Mage::getModel('inventorypurchasing/purchaseorder')->load($data['purchaseorder_purchase_order_id']);
                return $model->getData($column);
            }
        }
    }

    public function generateCode($string) {
        $barcode = preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $string);
        $checkBarcodeExist = Mage::getModel('inventorybarcode/barcode')->load($barcode, 'barcode');

        if ($checkBarcodeExist->getId()) {
            $barcode = $this->generateCode($string);
        }

        return $barcode;
    }

    public function convertExpression($param) {
        $alphabet = (strpos($param[1], 'A')) === false ? '' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= (strpos($param[1], 'N')) === false ? '' : '0123456789';
        return $this->getRandomString($param[2], $alphabet);
    }

    public function importProduct($data) {

        if (count($data)) {
            Mage::getModel('admin/session')->setData('barcode_product_import', $data);
            Mage::getModel('admin/session')->setData('null_barcode_product_import', 0);
        } else {
            Mage::getModel('admin/session')->setData('null_barcode_product_import', 1);
            Mage::getModel('admin/session')->setData('barcode_product_import', null);
        }
    }

    public function selectboxBarcodeByPid($productId, $orderItemId, $orderId = null, $_warehouseId = null, $creditmemo = null) {

        if ($_warehouseId == null) {
            $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('product_id', $productId);

            $allWarehouse = Mage::helper('inventoryplus/warehouse')->getAllWarehouseNameEnable();
            $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->setOrder('total_qty', 'DESC');
            $warehouseHaveProduct = array();
            $_warehouseId = 0;
            $firstWarehouse = $warehouseOrder->getFirstItem()->getWarehouseId();
            foreach ($warehouseProductModel as $model) {
                $warehouseId = $model->getWarehouseId();
                if (!isset($allWarehouse[$warehouseId]))
                    continue;
                $warehouseName = $allWarehouse[$warehouseId];
                if ($warehouseName != '') {
                    if ($warehouseId == $firstWarehouse) {
                        $_warehouseId = $warehouseId;
                    }
                }
            }
        }


        if (!$creditmemo) {
            $return = "<select class='warehouse-shipment' name='barcode-shipment[items][$orderItemId]' onchange='changebarcode(this,$orderItemId);' id='barcode-shipment[items][$orderItemId]'>";
        } else {

            $return = '<label id="creditmemo[barcode-label][' . $orderItemId . ']">' . $this->__('Barcode') . '</label>';
            $return .= '<select id="creditmemo[barcode-select][' . $orderItemId . ']" name="creditmemo[barcode-select][' . $orderItemId . ']">';
        }

        $barcodes = Mage::getModel('inventorybarcode/barcode')->getCollection()
                ->addFieldToFilter('product_entity_id', $productId)
                ->addFieldToFilter('barcode_status', 1)
                ->addFieldToFilter('qty', array('gt' => 0));
        $i = 0;
        $return .= "<option value=''>" . $this->__('Select Barcode') . "</option>";
        foreach ($barcodes as $barcode) {
            $barcodeWarehouseId = explode(',', $barcode->getWarehouseWarehouseId());

            if ($_warehouseId && !in_array($_warehouseId, $barcodeWarehouseId)) {
                continue;
            } else {


                $return .= "<option value='" . $barcode->getId() . "' ";
                $return .= ">" . $barcode->getBarcode() . "(" . $barcode->getQty() . " product(s))</option>";
                $i++;
            }
        }
        $return .= "</select><br />";
        if ($i == 0) {
            $return = '<label id="creditmemo[barcode-label][' . $orderItemId . ']"></label><div id="creditmemo[barcode-select][' . $orderItemId . ']">' . $this->__('No barcode of this item found!') . '</div>';
        }


        return $return;
    }

    /*
     * 
     * get barcode for order refund
     * 
     * @return String
     */

    public function getCreditmemoBarcode($productId, $orderItemId, $orderId = null, $_warehouseId = null) {

        $barcodeShipment = Mage::getModel('inventorybarcode/barcode_shipment')->getCollection()
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('item_id', $orderItemId)
                        ->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('warehouse_id', $_warehouseId)->getFirstItem();

        if ($barcodeShipment->getId()) {
            $barcode = Mage::getModel('inventorybarcode/barcode')->load($barcodeShipment->getBarcodeId());

            $return = '<label id="creditmemo[barcode-label][' . $orderItemId . ']">' . $barcode->getBarcode() . '</label>';
            $return .= '<input id="creditmemo[barcode-value][' . $orderItemId . ']" type="hidden" name="creditmemo[barcode-value][' . $orderItemId . ']" value="' . $barcode->getId() . '"/>';
        } else {
            $return = '<label id="creditmemo[barcode-label][' . $orderItemId . ']"></label><div id="creditmemo[barcode-select][' . $orderItemId . ']">' . $this->__('No barcode of this item found!') . '</div>';
        }


        return $return;
    }

}
