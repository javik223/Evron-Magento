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
 * Inventorypurchasing Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorypurchasing
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Model_Observer {
    /* add warehouse and supplier when create product */

    public function catalogProductSaveAfterEvent($observer) {
        if (Mage::registry('INVENTORY_SUPPLIER_CREATE_PRODUCT'))
            return;
        Mage::register('INVENTORY_SUPPLIER_CREATE_PRODUCT', true);

        $product = $observer->getProduct();
        $productId = $product->getId();

        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        if (Mage::getModel('admin/session')->getData('inventory_catalog_product_duplicate')) {
            $currentProductId = Mage::getModel('admin/session')->getData('inventory_catalog_product_duplicate');
            $supplierProducts = Mage::getModel('inventorypurchasing/supplier_product')->getCollection()
                    ->addFieldToFilter('product_id', $currentProductId);
            foreach ($supplierProducts as $supplierProduct) {

                $newSupplierProduct = Mage::getModel('inventorypurchasing/supplier_product');
                $newSupplierProduct->setData('product_id', $productId)
                        ->setData('supplier_id', $supplierProduct->getSupplierId())
                        ->setData('cost', $supplierProduct->getCost())
                        ->setData('discount', $supplierProduct->getDiscount())
                        ->setData('tax', $supplierProduct->getTax())
                        ->setData('supplier_sku', $supplierProduct->getSupplierSku())
                        ->save();
            }
            Mage::getModel('admin/session')->setData('inventory_catalog_product_duplicate', false);
        }

        $post = Mage::app()->getRequest()->getPost();
        $isInStock = 0;

        if (isset($post['product']['stock_data']['is_in_stock']))
            $isInStock = $post['product']['stock_data']['is_in_stock'];
        if (isset($post['simple_product']))
            if (isset($post['simple_product']['stock_data']['is_in_stock']))
                $isInStock = $post['simple_product']['stock_data']['is_in_stock'];
        if (isset($post['inventory_select_supplier'])) {
            $suppliers = $post['inventory_select_supplier'];
            try {
                foreach ($suppliers as $supplier) {
                    $warehouseProductModel = Mage::getModel('inventorypurchasing/supplier_product');
                    $warehouseProductModel->setSupplierId($supplier['supplier_id'])
                            ->setProductId($productId)
                            ->setCost($supplier['cost'])
                            ->setDiscount($supplier['discount'])
                            ->setTax($supplier['tax'])
                            ->setSupplierSku($supplier['supplier_sku'])
                            ->save();
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'inventory_management.log');
            }
        }
    }

    public function catalogModelProductDuplicate($observer) {
        $currentProduct = $observer->getCurrentProduct();
        $currentProductId = $currentProduct->getId();
        Mage::getModel('admin/session')->setData('inventory_catalog_product_duplicate', $currentProductId);
    }

    public function supplierSaveAfter($observer) {
        if (Mage::registry('UPDATE_SUPPLIER'))
            return;
        Mage::register('UPDATE_SUPPLIER', true);
        $supplier = $observer->getInventorypurchasingSupplier();
        $supplierId = $supplier->getId();
        $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                ->getCollection()
                ->addFieldToFilter('status', array('nin' => array('7')))
                ->addFieldToFilter('supplier_id', $supplierId)
                ->setOrder('purchase_on', 'DESC');
        $total_order = 0;
        $purchase_order = 0;
        $return_order = 0;
        $last_purchase_order = null;
        if ($total_order = count($purchaseOrders)) {
            $purchase_order = 0;
            foreach ($purchaseOrders as $purchaseOrder) {
                if (!$last_purchase_order)
                    $last_purchase_order = $purchaseOrder->getPurchaseOn();
                $purchase_order += $purchaseOrder->getTotalAmount();
                $returnOrders = Mage::getModel('inventorypurchasing/purchaseorder_refund')
                        ->getCollection()
                        ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());
                if (count($returnOrders)) {
                    foreach ($returnOrders as $returnOrder)
                        $return_order += $returnOrder->getTotalAmount();
                }
            }
        }
        $supplier->setTotalOrder($total_order)
                ->setPurchaseOrder($purchase_order)
                ->setReturnOrder($return_order)
                ->setLastPurchaseOrder($last_purchase_order)
                ->save();
    }

    public function deliverySaveAfter($observer) {
        if (Mage::registry('UPDATE_SUPPLIER_DELIVERY'))
            return;
        Mage::register('UPDATE_SUPPLIER_DELIVERY', true);
        $delivery = $observer->getInventorypurchasingPurchaseorderDelivery();
        $purchaseOrderId = $delivery->getPurchaseOrderId();
        //zend_debug::dump($delivery->getData());die();
        $transactionData = array();
        if ($purchaseOrderId) {
            $purchaseOrderDelivery = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);

            $supplierId = $purchaseOrderDelivery->getSupplierId();
            $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);

            $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                    ->getCollection()
                    ->addFieldToFilter('supplier_id', $supplierId)
                    ->setOrder('purchase_on', 'DESC');
            $total_order = 0;
            $purchase_order = 0;
            $return_order = 0;
            $last_purchase_order = null;
            if ($total_order = count($purchaseOrders)) {
                $purchase_order = 0;
                foreach ($purchaseOrders as $purchaseOrder) {
                    if (!$last_purchase_order)
                        $last_purchase_order = $purchaseOrder->getPurchaseOn();
                    $purchase_order += $purchaseOrder->getTotalAmount();
                    $returnOrders = Mage::getModel('inventorypurchasing/purchaseorder_refund')
                            ->getCollection()
                            ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());
                    if (count($returnOrders)) {
                        foreach ($returnOrders as $returnOrder)
                            $return_order += $returnOrder->getTotalAmount();
                    }
                }
            }
            $supplier->setTotalOrder($total_order)
                    ->setPurchaseOrder($purchase_order)
                    ->setReturnOrder($return_order)
                    ->setLastPurchaseOrder($last_purchase_order)
                    ->save();
        }
    }

    public function purchaseorderSaveAfter($observer) {
        if (Mage::registry('UPDATE_SUPPLIER_PURCHASEORDER'))
            return;
        Mage::register('UPDATE_SUPPLIER_PURCHASEORDER', true);
        $purchaseOrder = $observer->getInventorypurchasingPurchaseorder();
        $purchaseOrderId = $purchaseOrder->getId();

        if ($purchaseOrderId) {
            $supplierId = $purchaseOrder->getSupplierId();
            $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
            $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                    ->getCollection()
                    ->addFieldToFilter('supplier_id', $supplierId)
                    ->setOrder('purchase_on', 'DESC');
            $total_order = 0;
            $purchase_order = 0;
            $return_order = 0;
            $last_purchase_order = null;
            if ($total_order = count($purchaseOrders)) {
                $purchase_order = 0;
                foreach ($purchaseOrders as $purchaseOrder) {
                    if (!$last_purchase_order)
                        $last_purchase_order = $purchaseOrder->getPurchaseOn();
                    $purchase_order += $purchaseOrder->getTotalAmount();
                    $returnOrders = Mage::getModel('inventorypurchasing/purchaseorder_refund')
                            ->getCollection()
                            ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());
                    if (count($returnOrders)) {
                        foreach ($returnOrders as $returnOrder)
                            $return_order += $returnOrder->getTotalAmount();
                    }
                }
            }
            $supplier->setTotalOrder($total_order)
                    ->setPurchaseOrder($purchase_order)
                    ->setReturnOrder($return_order)
                    ->setLastPurchaseOrder($last_purchase_order)
                    ->save();
        }
    }

    public function returnorderSaveAfter($observer) {
        if (Mage::registry('UPDATE_SUPPLIER_RETURNORDER'))
            return;
        Mage::register('UPDATE_SUPPLIER_RETURNORDER', true);

        $returnOrder = $observer->getInventorypurchasingPurchaseorderRefund();
        $supplierId = $returnOrder->getSupplierId();
        if ($supplierId) {
            $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
            $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                    ->getCollection()
                    ->addFieldToFilter('supplier_id', $supplierId)
                    ->setOrder('purchase_on', 'DESC');
            $total_order = 0;
            $purchase_order = 0;
            $return_order = 0;
            $last_purchase_order = '';
            if ($total_order = count($purchaseOrders)) {
                $purchase_order = 0;
                foreach ($purchaseOrders as $purchaseOrder) {
                    if (!$last_purchase_order)
                        $last_purchase_order = $purchaseOrder->getPurchaseOn();
                    $purchase_order += $purchaseOrder->getTotalAmount();
                    $returnOrders = Mage::getModel('inventorypurchasing/purchaseorder_refund')
                            ->getCollection()
                            ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());
                    if (count($returnOrders)) {
                        foreach ($returnOrders as $returnOrder)
                            $return_order += $returnOrder->getTotalAmount();
                    }
                }
            }
            $supplier->setTotalOrder($total_order)
                    ->setPurchaseOrder($purchase_order)
                    ->setReturnOrder($return_order)
                    ->setLastPurchaseOrder($last_purchase_order)
                    ->save();
        }
    }

    public function addColumnPermission($observer) {
        $grid = $observer->getEvent()->getGrid();
        $disabledvalue = $observer->getEvent()->getDisabled();
        $grid->addColumn('can_purchase_product', array(
            'header' => Mage::helper('inventorypurchasing')->__('Purchase Stock'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'purchase[]',
            'values' => $this->_getSelectedCanPurchaseAdmins($grid)
        ));
    }

    protected function _getSelectedCanPurchaseAdmins($grid) {
        $warehouse = $grid->getWarehouse();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $array = array();
        if ($warehouse->getId()) {
            $canPurchaseAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse->getId())
                    ->addFieldToFilter('can_purchase_product', 1);
            foreach ($canPurchaseAdmins as $canPurchaseAdmin) {
                $array[] = $canPurchaseAdmin->getAdminId();
            }
        } else {
            $array = array($adminId);
        }
        return $array;
    }

    public function addMorePermission($observer) {
        $event = $observer->getEvent();
        $assignment = $event->getPermission();
        $datas = $event->getData();
        $data = $datas['data'];
        $adminId = $event->getAdminId();
        $changePermissions = $event->getChangePermssions();
        $purchaseAdmins = array();
        if (isset($data['purchase']) && is_array($data['purchase'])) {
            $purchaseAdmins = $data['purchase'];
        }
        if ($assignment->getId()) {
            $oldPurchase = $assignment->getCanPurchaseProduct();
        }
        if (in_array($adminId, $purchaseAdmins)) {
            if ($assignment->getId()) {
                if ($oldPurchase != 1) {
                    $changePermissions[$adminId]['old_purchase'] = Mage::helper('inventorypurchasing')->__('Cannot purchase products');
                    $changePermissions[$adminId]['new_purchase'] = Mage::helper('inventorypurchasing')->__('Can purchase products');
                }
            } else {
                $changePermissions[$adminId]['old_purchase'] = '';
                $changePermissions[$adminId]['new_purchase'] = Mage::helper('inventorypurchasing')->__('Can purchase products');
            }
            $assignment->setData('can_purchase_product', 1);
        } else {
            if ($assignment->getId()) {
                if ($oldPurchase != 0) {
                    $changePermissions[$adminId]['old_purchase'] = Mage::helper('inventorypurchasing')->__('Can purchase products');
                    $changePermissions[$adminId]['new_purchase'] = Mage::helper('inventorypurchasing')->__('Cannot purchase products');
                }
            } else {
                $changePermissions[$adminId]['old_purchase'] = '';
                $changePermissions[$adminId]['new_purchase'] = Mage::helper('inventorypurchasing')->__('Can purchase products');
            }
            $assignment->setData('can_purchase_product', 0);
        }
    }

    /**
     * Add stock action buttons to inventory listing page
     * 
     * @param Object $observer
     */
    public function inventoryplus_inventory_stock_action_buttons($observer) {
        $actionsObject = $observer->getEvent()->getData('stock_actions_object');
        $actions = $actionsObject->getActions();
        $actions['purchase_stock'] = array('params' => array(
                'label' => Mage::helper('inventorypurchasing')->__('Purchase Stock'),
                'onclick' => 'location.href=\'' . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inpu_purchaseorders/new') . '\'',
                'class' => 'add',
            ),
            'position' => -300
        );

        $actionsObject->setActions($actions);
    }

    /**
     * Add stock action buttons to warehouse page
     * 
     * @param Object $observer
     */
    public function inventoryplus_warehouse_stock_action_buttons($observer) {
        $warehouse = $observer->getEvent()->getData('warehouse');
        if (!Mage::helper('inventoryplus/warehouse')->isAllowAction('purchase_product', $warehouse))
            return;
        $actionsObject = $observer->getEvent()->getData('stock_actions_object');
        $actions = $actionsObject->getActions();
        $actions['purchase_stock'] = array('params' => array(
                'label' => Mage::helper('inventorypurchasing')->__('Purchase Stock'),
                'onclick' => 'location.href=\'' . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inpu_purchaseorders/new', array('warehouse_ids' => $warehouse->getId())) . '\'',
                'class' => 'add',
            ),
            'position' => -95
        );
        $actionsObject->setActions($actions);
    }

    /**
     * Add dashboard tab to supplier
     * 
     * @param Object $observer
     */
    public function addDashboardTabSupplier($observer) {
        $supplier = $observer->getEvent()->getData('supplier');
        if (!$supplier->getId())
            return;
        $tab = $observer->getEvent()->getTab();
        $tab->addTab('dashboard_section', array(
            'label' => Mage::helper('inventorypurchasing')->__('Dashboard'),
            'title' => Mage::helper('inventorypurchasing')->__('Dashboard'),
            'content' => $tab->getLayout()
                    ->createBlock('inventorypurchasing/adminhtml_supplier_edit_tab_dashboard')
                    ->toHtml(),
        ));
    }

}
