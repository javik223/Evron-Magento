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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Helper_Warehouse extends Mage_Core_Helper_Abstract {
    /*
     * get available qty of the first warehouse for shipment
     */

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
        $return = "<select class='warehouse-shipment' name='warehouse-shipment[items][$orderItemId]' onchange='changeviewwarehouse(this,$orderItemId);' id='warehouse-shipment[items][$orderItemId]'>";
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

    public function getAvailQtyinWarehouseBySku($sku, $warehouseId) {
        $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
        $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem();
        if ($warehouseProduct->getId()) {
            return $warehouseProduct->getAvailableQty();
        } else {
            return 0;
        }
    }

    public function getQtyProductWarehouse($storeId, $productId, $selectWarehouse, $ShippingAddress, $billingAddress) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        $distance = -1;
        $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection();
        foreach ($warehouses as $warehouse) {
            switch ($selectWarehouse) {
                case 1:
                    $sql = "SELECT `warehouse_id` FROM " . $resource->getTableName('inventoryplus/warehouse_product') . " WHERE `product_id` = " . $productId . " AND `available_qty` = (SELECT MAX(`available_qty`) FROM " . $resource->getTableName('inventoryplus/warehouse_product') . " WHERE `product_id` = " . $productId . ")";
                    $result = $readConnection->fetchOne($sql);
                    if ($result) {
                        $warehouseId = $result;
                    }
                    break;
                case 2:
                    $sql = "SELECT `warehouse_id` FROM " . $resource->getTableName('inventoryplus/warehouse_product') . " WHERE `product_id` = " . $productId . " AND `available_qty` = (SELECT MIN(`available_qty`) FROM " . $resource->getTableName('inventoryplus/warehouse_product') . " WHERE `product_id` = " . $productId . ")";
                    $result = $readConnection->fetchOne($sql);
                    if ($result) {
                        $warehouseId = $result;
                    }
                    break;
                case 3:
                    if (!isset($ShippingAddress) || !$ShippingAddress) {
                        $ShippingAddress = $billingAddress;
                    }

                    if (!isset($ShippingAddress) || !$ShippingAddress) {
                        Mage::log('Do not find Shippig address and Billing address ---------- app\code\local\Magestore\Inventorywarehouse\Helper\Warehouse.php - Line 171', null, 'inventory_management.log');
                        $warehouseId = $warehouse->getId();
                        return $warehouseId;
                    }

                    $source_address = $warehouse->getStreet() . " " . $warehouse->getCity() . " " . $warehouse->getCountryId(); //." ".$warehouse->getPostcode();
                    $street = $ShippingAddress->getStreet();
                    $destination_address = $street[0] . " " . $ShippingAddress->getCity() . " " . $ShippingAddress->getCountryId(); //." ".$ShippingAddress->getPostcode();
                    $newDistance = $this->calculateDistance($source_address, $destination_address);
                    if ($distance == -1) {
                        $warehouseId = $warehouse->getId();
                        $distance = $newDistance;
                    } else {
                        if ($newDistance && ($distance > $newDistance)) {
                            $warehouseId = $warehouse->getId();
                            $distance = $newDistance;
                        }
                    }
                    break;
                case 4:
                    $groupId = Mage::getModel('core/store')->load($storeId)->getGroupId();
                    $warehouseId = Mage::getModel('core/store_group')->load($groupId)->getWarehouseId();
                    break;
                default:
                    break;
            }
        }

        return $warehouseId;
    }

    /*
     * get distance between shipping address and warehouse address by google
     */

    public function calculateDistance($source_address, $destination_address) {
        $url = "http://maps.googleapis.com/maps/api/directions/json?origin=" . str_replace(' ', '+', $source_address) . "&destination=" . str_replace(' ', '+', $destination_address) . "&sensor=false";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_all = json_decode($response);
        $distance = $response_all->routes[0]->legs[0]->distance->value;
        return $distance;
    }

    /*
     * get warehouse name by warehouse id in model inventory/warehouse
     */

    public function getWarehouseNameByWarehouseId($warehouseId) {
        $warehouseModel = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
        $warehouseName = $warehouseModel->getWarehouseName();
        return $warehouseName;
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

    public function getWarehouseProductIds($warehouseId) {
        $productIds = array();
        $warehouses = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId);
        foreach ($warehouses as $warehouse) {
            $productIds[] = $warehouse->getProductId();
        }
        return $productIds;
    }

    //get warehouse by product id
    public function getWarehouseByProductId($productId, $checkqtyZero = true) {
        if ($checkqtyZero == true) {
            $warehouseProducts = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('qty', array('gt' => 0));
        } else {
            $warehouseProducts = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $productId);
        }
        if (count($warehouseProducts)) {
            return $warehouseProducts;
        } else {
            return null;
        }
    }

    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('warehouseaddmore_product_import', $data);
            Mage::getModel('admin/session')->setData('null_warehouseaddmore_product_import', 0);
        } else {
            Mage::getModel('admin/session')->setData('null_warehouseaddmore_product_import', 1);
            Mage::getModel('admin/session')->setData('warehouseaddmore_product_import', null);
        }
    }

}
