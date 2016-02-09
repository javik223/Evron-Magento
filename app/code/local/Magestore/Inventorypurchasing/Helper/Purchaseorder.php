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
 * @package     Magestore_Inventorypurchasing
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorypurchasing Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorypurchasing
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Helper_Purchaseorder extends Mage_Core_Helper_Abstract {
    /* check this purchase order has delivery or not */

    public function haveDelivery($id = null) {
        if (!$id) {
            $id = Mage::app()->getRequest()->getParam('id');
        }
        if ($purchaseOrderId = $id) {
            $delivery = Mage::getModel('inventorypurchasing/purchaseorder_delivery')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrderId)
                    ->getFirstItem();
            if ($delivery->getId())
                return true;
        }
        return false;
    }

    public function canCancel($id = null) {
        if (!$id) {
            $id = Mage::app()->getRequest()->getParam('id');
        }
        if ($purchaseOrderId = $id) {
            $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
            $cancelDate = $purchaseOrder->getCanceledDate();
            $canCancel = 0;
            if (strtotime($cancelDate) <= strtotime(now())) {
                return false;
            }
            if ($this->haveDelivery($purchaseOrderId)) {
                return false;
            }
            $poStatus = $purchaseOrder->getStatus();
            if ($poStatus == 6) {
                return false;
            }
        }
        return true;
    }

    public function canWaittingDelivery($id = null) {
        if (!$id) {
            $id = Mage::app()->getRequest()->getParam('id');
        }
        if ($purchaseOrderId = $id) {
            $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
            $poStatus = $purchaseOrder->getStatus();
            if ($poStatus == 6) {
                return false;
            }
        }
        return true;
    }

    public function getReturnOrderStatus() {
        return array(
//            1 => Mage::helper('inventorypurchasing')->__('New'),
//            2 => Mage::helper('inventorypurchasing')->__('Inquiry'),
//            3 => Mage::helper('inventorypurchasing')->__('Awaiting payment'),
//            4 => Mage::helper('inventorypurchasing')->__('Awaiting supplier'),
            Magestore_Inventorypurchasing_Model_Purchaseorder::AWAITING_DELIVERY_STATUS => Mage::helper('inventorypurchasing')->__('Awaiting delivery'),
            Magestore_Inventorypurchasing_Model_Purchaseorder::COMPLETE_STATUS => Mage::helper('inventorypurchasing')->__('Completed'),
            Magestore_Inventorypurchasing_Model_Purchaseorder::CANCELED_STATUS => Mage::helper('inventorypurchasing')->__('Canceled'),
            Magestore_Inventorypurchasing_Model_Purchaseorder::PENDING_STATUS => Mage::helper('inventorypurchasing')->__('Pending'),
        );
    }

    public function getMassPOStatus() {
        return array(
            Magestore_Inventorypurchasing_Model_Purchaseorder::AWAITING_DELIVERY_STATUS => Mage::helper('inventorypurchasing')->__('Awaiting delivery'),
            Magestore_Inventorypurchasing_Model_Purchaseorder::CANCELED_STATUS => Mage::helper('inventorypurchasing')->__('Canceled')
        );
    }

    public function getShippingMethod() {
        $shippingMethods = Mage::getModel('inventorypurchasing/shippingmethod')
                ->getCollection()
                ->addFieldToFilter('shipping_method_status', 1);
        $shippingArray = array();
        $shippingArray[0] = $this->__('Select shipping method');
        if (count($shippingMethods)) {

            foreach ($shippingMethods as $shipping) {
                $shippingArray[$shipping->getId()] = $shipping->getShippingMethodName();
            }
        }
        $shippingArray['new'] = $this->__('Create a new shipping method');
        return $shippingArray;
    }

    public function getPaymentTerms() {
        $paymentTerms = Mage::getModel('inventorypurchasing/paymentterm')
                ->getCollection()
                ->addFieldToFilter('payment_term_status', 1);
        $paymentArray = array();
        $paymentArray[0] = $this->__('Select payment term');
        if (count($paymentTerms)) {

            foreach ($paymentTerms as $payment) {
                $paymentArray[$payment->getId()] = $payment->getPaymentTermName();
            }
        }
        $paymentArray['new'] = $this->__('Create a new payment term');
        return $paymentArray;
    }

    public function getShippingMethodForSupplier() {
        $shippingMethods = Mage::getModel('inventorypurchasing/shippingmethod')
                ->getCollection()
                ->addFieldToFilter('shipping_method_status', 1);
        if (count($shippingMethods)) {
            $shippingArray = array();
            $shippingArray[0] = $this->__('Select shipping method');
            foreach ($shippingMethods as $shipping) {
                $shippingArray[$shipping->getId()] = $shipping->getShippingMethodName();
            }
            return $shippingArray;
        } else {
            return '';
        }
    }

    public function getPaymentTermsForSupplier() {
        $paymentTerms = Mage::getModel('inventorypurchasing/paymentterm')
                ->getCollection()
                ->addFieldToFilter('payment_term_status', 1);
        if (count($paymentTerms)) {
            $paymentArray = array();
            $paymentArray[0] = $this->__('Select payment term');
            foreach ($paymentTerms as $payment) {
                $paymentArray[$payment->getId()] = $payment->getPaymentTermName();
            }
            return $paymentArray;
        } else {
            return '';
        }
    }

    public function getOrderPlaced() {
        return array(
            1 => Mage::helper('inventorypurchasing')->__('Email'),
            2 => Mage::helper('inventorypurchasing')->__('Fax'),
            3 => Mage::helper('inventorypurchasing')->__('N/A'),
            4 => Mage::helper('inventorypurchasing')->__('Phone'),
            5 => Mage::helper('inventorypurchasing')->__('Vender website')
        );
    }

    public function getPurchaseOrderStatus() {
        return array(
            //1 => 'New',
            //2 => 'Inquiry',
            //3 => 'Awaiting payment',
            //4 => 'Awaiting supplier',
            5 => 'Awaiting delivery',
            6 => 'Complete',
            7 => 'Canceled'
        );
    }

    public function getDataByPurchaseOrderId($purchaseOrderId, $column) {
        $purchaseOrderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
        $return = $purchaseOrderModel->getData($column);
        return $return;
    }

    public function getSupplierInfoByPurchaseOrderId($purchaseOrderId) {
        $purchaseOrderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
        $supplierId = $purchaseOrderModel->getSupplierId();
        $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $supplierField = '';
        if ($supplierModel->getId()) {
            $data = $supplierModel->getData();

            $supplierField = "<br/>" . $data['street'];
            $supplierField .= "<br/>" . $data['city'] . ',';
            if (!$data['state']) {
                if ($data['state_id']) {
                    $state = Mage::getModel('directory/region')->load($data['state_id']);
                    $data['state'] = $state->getName();
                }
            }
            if ($data['state']) {
                $supplierField .= " " . $data['state'];
            }
            $supplierField .= ", " . $data['postcode'];

            $country = Mage::getModel('directory/country')->loadByCode($data['country_id']);

            $supplierField .= "<br/>" . $country->getName() . '.';

            $supplierField .= "<br/>" . $this->__('Telephone: ') . $data['telephone'];
            $supplierField .= "<br/>" . $this->__('Email: ') . $data['supplier_email'];
        }
        return $supplierField;
    }

    public function getWarehouseOption() {
        $adminId = Mage::getModel('admin/session')->getUser()->getId();
        if (!$adminId)
            return null;
        $warehouseAssigneds = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('can_purchase_product', 1);
        $warehouseIds = array();
        foreach ($warehouseAssigneds as $warehouseAssigned) {
            $warehouseIds[] = $warehouseAssigned->getWarehouseId();
        }
        if (!count($warehouseIds) || count($warehouseIds) <= 0)
            return null;
        $warehouseCollections = Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->addFieldToFilter('warehouse_id', array('in' => $warehouseIds))
                ->addFieldToFilter('status', 1)
        ;
        $warehouseArr = array();
        foreach ($warehouseCollections as $warehouse) {
            $warehouseArr[] = array('value' => $warehouse->getId(), 'label' => $warehouse->getWarehouseName());
        }
        return $warehouseArr;
    }

    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('purchaseorder_product_import', $data);
        }
    }

    public function getBilingAddressByPurchaseOrderId($purchaseOrderId) {
        $purchaseOrderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
        $supplierId = $purchaseOrderModel->getSupplierId();
        $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $supplierField = '';
        if ($supplierModel->getId()) {
            $data = $supplierModel->getData();
            $countryLists = Mage::getModel('directory/country')->getResourceCollection()->loadByStore()->toOptionArray(true);
            $countryList = array();
            foreach ($countryLists as $county) {
                $countryList[$county['value']] = $county['label'];
            }
            $supplierField = "<br/>" . $data['street'];
            $supplierField .= "<br/>" . $data['city'];
            if (isset($data['state'])) {
                $supplierField .= ", " . $data['state'];
            }
            $supplierField .= ", " . $data['postcode'];
            $supplierField .= "<br/>" . $countryList[$data['country_id']];
            $supplierField .= "<br />" . $this->__('T: ') . $data['telephone'];
        }
        return $supplierField;
    }

    public function importDeliveryProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('delivery_purchaseorder_product_import', $data);
        }
    }

    public function importReturnOrderProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('returnorder_product_import', $data);
        }
    }

    /**
     * Check if PO is out date or not (split canCancel function)
     * @param int , PO model
     * @return boolean
     */
    public function isPOCancelOutdate(Magestore_Inventorypurchasing_Model_Purchaseorder $po) {
        if (empty($po)) {
            return false;
        }
        if (get_class($po) == "Magestore_Inventorypurchasing_Model_Purchaseorder") {
            $cancelDate = $po->getCanceledDate();
            if (strtotime($cancelDate) > strtotime(now())) {
                return true;
            }
        } else {
            $purchase_order = Mage::getModel('inventorypurchasing/purchaseorder')
                    ->load($po);
            $cancelDate = $po->getCanceledDate();
            if (strtotime($cancelDate) > strtotime(now())) {
                return true;
            }
        }
        return false;
    }

}
