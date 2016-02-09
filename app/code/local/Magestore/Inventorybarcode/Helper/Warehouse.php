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
class Magestore_Inventorybarcode_Helper_Warehouse extends Mage_Core_Helper_Data {

   public function getAllWarehouseNameEnable() {
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection()
                ->addFieldToFilter('status', 1);
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        return $warehouses;
    }
    
    public function selectboxWarehouseShipmentByPid($productId, $minQty, $orderItemId, $orderId = null) {
        $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('product_id', $productId);



        $allWarehouse = $this->getAllWarehouseNameEnable();
        $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->setOrder('total_qty', 'DESC');
        $warehouseHaveProduct = array();
        $return = "<select class='warehouse-shipment' name='warehouse-shipment[items][$orderItemId]' onchange='changeviewwarehouse(this,$orderItemId);checkStatusAvailableAOrderItemByEvent(this.value,$productId,0,$orderItemId);' id='warehouse-shipment[items][$orderItemId]'>";
        $firstWarehouse = $warehouseOrder->getFirstItem()->getWarehouseId();
        foreach ($warehouseProductModel as $model) {
            $warehouseId = $model->getWarehouseId();
            $warehouseName = $allWarehouse[$warehouseId];
            $productQty = $model->getTotalQty();
            if ($warehouseName != '') {
                if (!$firstWarehouse)
                    $firstWarehouse = $warehouseId;
                $return .= "<option value='$warehouseId' ";
                if ($warehouseId == $firstWarehouse) {
                    $return .= ' selected';
                }
                $return .= ">$warehouseName($productQty product(s))</option>";
                $warehouseHaveProduct[] = $allWarehouse[$warehouseId];
            }
        }
        foreach ($allWarehouse as $warehouseIdKey => $warehouseNameValue) {
            if ($warehouseNameValue != '') {
                if (in_array($allWarehouse[$warehouseIdKey], $warehouseHaveProduct) == false) {
                    if (!$firstWarehouse)
                        $firstWarehouse = $warehouseIdKey;
                    $return .= "<option value='$warehouseIdKey' ";
                    $return .= ">$warehouseNameValue(0 product(s))</option>";
                }
            }
        }

        $return .= "</select><br />";
        $return .= "<div style='float:right;'><a id='view_warehouse-shipment[items][$orderItemId]' target='_blank' href='" . Mage::getBlockSingleton('inventoryplus/adminhtml_warehouse')->getUrl('adminhtml/inp_warehouse/edit') . 'id/' . $firstWarehouse . "'>" . $this->__('view') . "</a></div>";
        return $return;
    }
    
    /*
     * get the firrst warehouse ha most this product
     */

    public function getFirstWarehouseHaveMostOfAProduct($productId) {

        $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->setOrder('total_qty', 'DESC');
        if (count($warehouseProductModel) > 1) {
            $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->setOrder('total_qty', 'DESC');
        }
        if ($warehouseProductModel->getFirstItem()->getData()) {
            $warehouseId = $warehouseProductModel->getFirstItem()->getWarehouseId();
        } else {
            $allWarehouse = $this->getAllWarehouseNameEnable();
            foreach ($allWarehouse as $warehouseIdKey => $warehouseNameValue) {
                if ($warehouseNameValue != '') {
                    $warehouseId = $warehouseIdKey;
                    break;
                }
            }
        }
        return $warehouseId;
    }
    
    public function checkTheFirstWarehouseAvailableProduct($productId, $minQty, $orderId) {
        $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('product_id', $productId);
        $firstWarehouse = $warehouseOrder->getFirstItem()->getWarehouseId();

        $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('warehouse_id', $firstWarehouse)
                ->addFieldToFilter('total_qty', array('gteq' => $minQty))
                ->setOrder('total_qty', 'DESC');

        if ($warehouseProductModel->getFirstItem()->getData()) {
            return true;
        } else {
            return false;
        }
    }
    
     public function checkWarehouseAvailableProduct($warehouseId, $productId, $qty) {
        $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('total_qty', array('gteq' => $qty));
        if ($warehouseProductModel->getFirstItem()->getData()) {
            return true;
        } else {
            return false;
        }
    }


}
