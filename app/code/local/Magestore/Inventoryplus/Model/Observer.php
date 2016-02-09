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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Observer {

    public function controller_action_layout_load_before($observer) {
        $controller = $observer->getEvent()->getAction();
        $layout = $observer->getEvent()->getLayout();
        //update layout of inventory configuration
        Mage::helper('inventoryplus')->updateConfigLayout($controller, $layout);
    }

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    public function controllerActionPredispatch($observer) {
        $refreshCache = false;
        $controller = $observer->getEvent()->getData('controller_action');

        if (Mage::helper('core')->isModuleEnabled('Magestore_Standardinventory')) {
            $this->disableModule('Magestore_Standardinventory');
            $template = file_get_contents(Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Magestore_Standardinventory.xml');
            $standardInventory = Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Magestore_Standardinventory.xml';
            if ($template) {
                $template = str_replace('true', 'false', $template);
            }
            chmod($standardInventory, 0777);
            file_put_contents($standardInventory, $template);
            $refreshCache = true;
        }

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventory')) {
            // set permission for warehouse
            $checkUpdate = Mage::getModel('inventoryplus/checkupdate')->getCollection()->addFieldToFilter('inserted_data', 0)->getFirstItem();
            if ($checkUpdate->getId()) {
                $admins = Mage::getModel('admin/user')->getCollection();
                $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                        ->addFieldToFilter('status', 1);

                foreach ($warehouseCollection as $warehouse) {
                    try {
                        foreach ($admins as $admin) {
                            if ($warehouse->getIsUnwarehouse()) {
                                $checkPermissionExists = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                                        ->addFieldToFilter('admin_id', $admin->getId())
                                        ->addFieldToFilter('warehouse_id', $warehouse->getId())
                                        ->getFirstItem();
                                if ($checkPermissionExists->getId()) {
                                    try {
                                        $checkPermissionExists->setCanEditWarehouse(1)
                                                ->setCanAdjust(1)
                                                ->save();
                                    } catch (Exception $e) {
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                } else {
                                    try {
                                        Mage::getModel('inventoryplus/warehouse_permission')
                                                ->setData('warehouse_id', $warehouse->getId())
                                                ->setData('admin_id', $admin->getId())
                                                ->setData('can_edit_warehouse', 1)
                                                ->setData('can_adjust', 1)
                                                ->save();
                                    } catch (Exception $e) {
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                }
                            } else {
                                $checkPermissionExists = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                                        ->addFieldToFilter('admin_id', $admin->getId())
                                        ->addFieldToFilter('warehouse_id', $warehouse->getId())
                                        ->getFirstItem();

                                $warehousePermission = Mage::getModel('inventory/assignment')->getCollection()
                                        ->addFieldToFilter('warehouse_id', $warehouse->getId())
                                        ->addFieldToFilter('admin_id', $admin->getId())
                                        ->getFirstItem();
                                if ($checkPermissionExists->getId()) {
                                    try {
                                        $checkPermissionExists->setCanEditWarehouse($warehousePermission->getCanEditWarehouse())
                                                ->setCanAdjust($warehousePermission->getCanAdjust())
                                                ->save();
                                    } catch (Exception $e) {
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                } else {
                                    try {
                                        Mage::getModel('inventoryplus/warehouse_permission')
                                                ->setData('warehouse_id', $warehouse->getId())
                                                ->setData('admin_id', $admin->getId())
                                                ->setData('can_edit_warehouse', $warehousePermission->getCanEditWarehouse())
                                                ->setData('can_adjust', $warehousePermission->getCanAdjust())
                                                ->save();
                                    } catch (Exception $e) {
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                    }
                }
                try {
                    $checkUpdate->setInsertedData(1)->save();
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, 'inventory_installation.log');
                }

                $refreshCache = true;
            }

            $this->disableModule('Magestore_Inventory');
            $templateInventory = file_get_contents(Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Magestore_Inventory.xml');
            $inventory = Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Magestore_Inventory.xml';

            if ($templateInventory) {
                $templateInventory = str_replace('true', 'false', $templateInventory);
            }
            chmod($inventory, 0777);
            file_put_contents($inventory, $templateInventory);

            if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorydropship')) {
                $version = Mage::getConfig()->getModuleConfig("Magestore_Inventorydropship")->version;

                if (trim($version) == '0.1.1') {
                    try {
                        $this->disableModule('Magestore_Inventorydropship');
                        $templateDropship = file_get_contents(Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Magestore_Inventorydropship.xml');
                        $dropship = Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Magestore_Inventorydropship.xml';
                        if ($templateDropship) {
                            $templateDropship = str_replace('true', 'false', $templateDropship);
                        }
                        chmod($dropship, 0777);
                        file_put_contents($dropship, $templateDropship);

                        $adminHTMLDropship = Mage::getBaseDir('code') . DS . 'local' . DS . 'Magestore' . DS . 'Inventorydropship' . DS . 'etc' . DS . 'adminhtml.xml';
                        chmod($adminHTMLDropship, 0777);
                        unlink($adminHTMLDropship);
                        $refreshCache = true;
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                    }
                }
            }

            $refreshCache = true;
        }
        //refresh cache
        if ($refreshCache) {
            $types = array('config', 'layout', 'block_html', 'collections', 'eav', 'translate');
            foreach ($types as $type) {
                Mage::app()->getCacheInstance()->cleanType($type);
                Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
            }
        }
    }

    /**
     * disable Module 
     * 
     */
    protected function disableModule($module) {
        $section = 'advanced';
        $groups = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $groups['modules_disable_output']['fields'][$module] = array('value' => 1);

                    try {
                        Mage::getSingleton('adminhtml/config_data')
                                ->setSection($section)
                                ->setWebsite($website->getCode())
                                ->setStore($store->getCode())
                                ->setGroups($groups)
                                ->save();
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                    }
                }
            }
        }
        $groups['modules_disable_output']['fields'][$module] = array('value' => 1);
        try {
            Mage::getSingleton('adminhtml/config_data')
                    ->setSection($section)
                    ->setWebsite(null)
                    ->setStore(null)
                    ->setGroups($groups)
                    ->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_installation.log');
        }
    }

    /**
     * process catalog_product_save_before event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //get old qty of product before update
    public function catalogProductSaveBefore($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_PRODUCT_SAVE_BEFORE'))
            return;
        //edited by Link - Import Dataflow integrate
        if (Mage::app()->getRequest()->getActionName() == 'batchRun') {
            return;
        }
        //end edited by Link
        Mage::register('INVENTORY_CORE_PRODUCT_SAVE_BEFORE', true);
        $product = $observer->getProduct();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        $qty = 0;
        if ($product->getId()) {
            $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $qty = $item->getQty();
        }
        Mage::getModel('admin/session')->setData('inventory_before_update_product_item', $qty);
    }

    /**
     * process catalog_product_save_after event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //update qty product for warehouse when product change
    public function catalogProductSaveAfter($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_PRODUCT_SAVE_AFTER'))
            return;
        //edited by Link - Import Dataflow integrate
        if (Mage::app()->getRequest()->getActionName() == 'batchRun') {
            return;
        }
        //end edited by Link
        Mage::register('INVENTORY_CORE_PRODUCT_SAVE_AFTER', true);
        $product = $observer->getProduct();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        $oldQty = 0;
        if (Mage::getModel('admin/session')->getData('inventory_before_update_product_item')) {
            $oldQty = Mage::getModel('admin/session')->getData('inventory_before_update_product_item');
            Mage::getModel('admin/session')->setData('inventory_before_update_product_item', null);
        }
        $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $newQty = $item->getQty();
        $addQtyMore = $newQty - $oldQty;
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                ->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $product->getId())
                ->getFirstItem();
        try {
            if ($warehouseProduct->getId()) {
                if ($addQtyMore != '0') {
                    $totalQty = $warehouseProduct->getTotalQty();
                    $availableQty = $warehouseProduct->getTotalQty();
                    $warehouseProduct->setTotalQty($totalQty + $addQtyMore)
                            ->setAvailableQty($availableQty + $addQtyMore)
                            ->save();
                }
            } else {
                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product');
                $warehouseProduct->setData('warehouse_id', $warehouseId)
                        ->setData('product_id', $product->getId())
                        ->setTotalQty($newQty)
                        ->setAvailableQty($newQty)
                        ->save();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    //minus qty available warehouse
    public function salesOrderPlaceAfter($observer) {

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_ORDER_PLACE'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_PLACE', true);
        $order = $observer->getOrder();
        $items = $order->getAllItems();
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        if (!$warehouseId) {
            Mage::log($observer->getOrder(), null, 'inventory_management.log');
            return;
        }
        foreach ($items as $item) {
            $manageStock = Mage::helper('inventoryplus')->getManageStockOfProduct($item->getProductId());
            if ($manageStock == '0') {
                continue;
            }

            if ($item->getProduct()->isComposite()) {
                continue;
            }
            $qtyOrdered = Mage::helper('inventoryplus')->getQtyOrderedFromOrderItem($item);
            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouseId)
                    ->addFieldToFilter('product_id', $item->getProductId())
                    ->getFirstItem();
            $currentQty = $warehouseProduct->getAvailableQty() - $qtyOrdered;
            try {
                $warehouseProduct->setAvailableQty($currentQty)
                        ->save();
                $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
                $warehouse->setWarehouseOrderItem($item, $item->getProductId(), $qtyOrdered);
            } catch (Exception $e) {
                
            }
        }
    }

    /**
     * Minus physical qty of downloadable or virtual products after invoice
     *
     * @param $observer
     */
    public function salesOrderInvoicePay($observer) {
        if (Mage::registry('INVENTORY_CORE_ORDER_INVOICE_PAY')) {
            return;
        }
        Mage::register('INVENTORY_CORE_ORDER_INVOICE_PAY', true);
        $orderId = $observer->getInvoice()->getOrder()->getId();

        $warehouseOrderCollection = Mage::getModel('inventoryplus/warehouse_order')
                ->getCollection()
                ->addFieldToFilter('order_id', array('eq' => $orderId));
        foreach ($warehouseOrderCollection as $warehouseOrder) {
            $warehouseId = $warehouseOrder->getWarehouseId();
            $productId = $warehouseOrder->getProductId();
            $productQty = $warehouseOrder->getQty();
            $productType = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToFilter('entity_id', $productId)
                    ->getFirstItem()
                    ->getTypeId();
            if ($productType == 'downloadable' || $productType == 'virtual') {
                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                        ->getCollection()
                        ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId))
                        ->addFieldToFilter('product_id', array('eq' => $productId))
                        ->getFirstItem();
                $newTotalQty = $warehouseProduct->getTotalQty() - $productQty;
                $warehouseProduct->setTotalQty($newTotalQty);
                try {
                    $warehouseProduct->save();
                } catch (Exception $e) {
                    Mage::logException($e->getMessage());
                }
            }
        }
    }

    /**
     * process sales_order_shipment_save_after event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //create shipment
    public function salesOrderShipmentSaveBefore($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        $data = Mage::app()->getRequest()->getParams();
        if (isset($data['invoice']['do_shipment']) && $data['invoice']['do_shipment'] == 1) {
            $items = $data['invoice']['items'];
            $check = Mage::helper('inventoryplus')->checkShipment($items);
            if ($check == 0) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('Can\'t create shipment , there are no warehouse enough stock'));
                throw new Exception("No warehouse enough stock");
            }
        }
    }

    /**
     * process sales_order_shipment_save_after event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //create shipment
    public function salesOrderShipmentSaveAfter($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        $helperClass = Mage::helper('inventoryplus/shipment');
        $data = Mage::app()->getRequest()->getParams();
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $shipmentId = $shipment->getId();
        $transactionProductData = array(); // Storage data for creating new warehouse transaction product.
        $helperClass->sendObject($observer, $data, $shipment, $order);
        if ($helperClass->isIgnoreObserver() == true)
            return;
        try {
            $shipmentItems = Mage::getResourceModel('sales/order_shipment_item_collection')->addFieldToFilter('parent_id', $shipmentId);
            if (Mage::registry('INVENTORY_WAREHOUSE_ORDER_SHIPMENT_' . $shipmentId))
                return;
            Mage::register('INVENTORY_WAREHOUSE_ORDER_SHIPMENT_' . $shipmentId, true);
            $shipmentItemData = array();  // Get data from sales/order_shipment_item_collection to array.
            foreach ($shipmentItems as $shipmentItem) {
                $shipmentItemData[$shipmentItem->getOrderItemId()] = $shipmentItem->getQty();
            }
            foreach ($order->getAllItems() as $item) {
                $qtyShipped = $helperClass->_prepareQtyShipped($item, $shipmentItemData);
                if ($qtyShipped != 0) {
                    if ($helperClass->isIgnoreProduct($item) == true)
                        continue;
                    $product_id = $item->getProductId();
                    $warehouse_id = $helperClass->_getWarehouseIdToShip($item, $data, $qtyShipped);
                    $helperClass->_saveModelWarehouseShipment($warehouse_id, $item, $product_id, $qtyShipped);
                    $warehouseProduct = $helperClass->_saveModelWarehouseProduct($warehouse_id, $product_id, $qtyShipped);
                    $warehouseOr = $helperClass->_saveModelWarehouseOrder($warehouse_id, $product_id, $qtyShipped, $warehouseProduct);
                    if($warehouseOr){
                        if ($warehouseOr->getQty() > 0)
                                $order->setShippingProgress(1);
                        if ($warehouseOr->getQty() == 0)
                                $order->setShippingProgress(2);
                    }else{
                            if(in_array($order->getStatus(),array('canceled','closed','complete')))$order->setShippingProgress(2);
                            else if($item->getQtyOrdered() == $item->getQtyShipped())$order->setShippingProgress(2);
                            else $order->setShippingProgress(1);
                    }
                    $transactionProductData[$warehouse_id][$product_id]['qty_shipped'] = $qtyShipped;
                    $transactionProductData[$warehouse_id][$product_id]['product_name'] = $item->getName();
                    $transactionProductData[$warehouse_id][$product_id]['product_sku'] = $item->getSku();
                }//Endif qtyShipped >0
            }// Endforeach $order->getAllItems()
            /* Create send transaction */
            if (isset($warehouse_id) && $warehouse_id) {
                $transactionSendData = $helperClass->_prepareTransactionData($observer);
                $totalQty = 0;
                $transactionSendData['warehouse_id_from'] = $warehouse_id;
                $transactionSendData['warehouse_name_from'] = Mage::helper('inventoryplus/warehouse')->getWarehouseNameByWarehouseId($warehouse_id);
                $transactionSendModel = $helperClass->_saveModelTransaction($transactionSendData);
                foreach ($transactionProductData[$warehouseRelated] as $productId => $transactionProduct) {
                    $helperClass->_saveModelTransactionProduct($transactionSendModel->getId(), $productId, $transactionProduct);
                    $totalQty += $transactionProduct['qty_shipped'];
                }
                $transactionSendModel->setTotalProducts(-$totalQty)->save();
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     * Process cancel order
     * 
     * @param Varien_Object $observer
     */
    public function orderCancelAfter($observer) {
        $order = $observer->getEvent()->getOrder();
        if (Mage::registry('INVENTORY_WAREHOUSE_ORDER_CANCEL_' . $order->getId()))
            return;
        Mage::register('INVENTORY_WAREHOUSE_ORDER_CANCEL_' . $order->getId(), true);
        
        try {
            $warehouseId = Mage::getModel('inventoryplus/warehouse')->getIdFromOrderId($order->getId());
            if (!$warehouseId) {
                return;
            }
            $warehouse = Mage::getModel('inventoryplus/warehouse')->setId($warehouseId);
            $items = $order->getAllItems();
            foreach ($items as $item) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
                $manageStock = $stockItem->getManageStock();
                if ($stockItem->getUseConfigManageStock()) {
                    $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
                }
                if ($manageStock == 0) {
                    continue;
                }
                if ($item->getProduct()->isComposite()) {
                    continue;
                }
                
                $qtyCanceled = $item->getQtyCanceled();     
                $diffQty = 0;
                if ($item->getParentItemId()) {
                    $parentItem = $order->getItemById($item->getParentItemId());
                    if ($parentItem && $parentItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                        $qtyCanceled = $parentItem->getQtyCanceled();
                        $coreQtyCanceled = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
                        $diffQty = $qtyCanceled - $coreQtyCanceled;
                    }
                }
                /* update stock in warehouse */
                if ($qtyCanceled) {
                    $warehouse->updateOrderItem($item->getId(), -$qtyCanceled);
                    $warehouse->updateStock($item->getProductId(), 0, $qtyCanceled);
                }
                /* update stock in catalog */
                if($diffQty){
                    Mage::helper('inventoryplus/stock')->updateCatalogQty($item->getProduct(), $diffQty);
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    /**
     * Process return stock to warehouse
     * 
     * @param object $observer
     * @return object
     */
    public function salesOrderCreditmemoSaveAfter($observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (Mage::registry('INVENTORY_WAREHOUSE_ORDER_CREDITMEMO_' . $creditmemo->getId()))
            return;
        Mage::register('INVENTORY_WAREHOUSE_ORDER_CREDITMEMO_' . $creditmemo->getId(), true);
        $data = Mage::app()->getRequest()->getParams();
        $dataObject = new Varien_Object($data);
        $this->_refundHelper()->prepareCreditMemoData($dataObject);
        $order = $creditmemo->getOrder();
        $dataObject->setOrder($order)
                ->setCreditmemoObject($creditmemo);
        try {
            foreach ($order->getAllItems() as $orderItem) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($orderItem->getProductId());
                $product = $orderItem->getProduct();
                $manageStock = $stockItem->getManageStock();
                if ($stockItem->getUseConfigManageStock()) {
                    $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
                }
                if (!$manageStock) {
                    /* ignore products with out manage stock */
                    continue;
                }
                if ($product->isComposite()) {
                    /* do nothing if this is configurable, bunded, groupred product */
                    continue;
                }
                $this->_refundHelper()->processRefund($dataObject, $orderItem);
            }
            $this->_refundHelper()->logTransaction($dataObject);
        } catch (Exception $e) {
            Mage::log($e->getTraceAsString(), null, 'inventory_management.log');
        }
    }

    public function productPrepareSave($observer) {
        $product = $observer->getEvent()->getProduct();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped'))) {
            return;
        }
        $productId = $product->getId();
        $productData = $observer->getRequest()->getPost('product');
        $stockData = $productData['stock_data'];
        $manageStock = isset($stockData['manage_stock'])?$stockData['manage_stock']:'0';
        $installer = Mage::getModel('core/resource');
        $writeConnection = $installer->getConnection('core_write');
        if (isset($stockData['use_config_manage_stock'])) {
            $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
        }
        if ($manageStock == '0') {
            $warehouseProducts = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $productId);
            foreach ($warehouseProducts as $whp) {
                try {
                    $whp->setTotalQty(0)
                            ->setAvailableQty(0)
                            ->save();
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
    }

    public function adminLoginSuccess($observer) {
        return;
        // Save inventory_permission vao session
        // muc dich cai thien toc do.
        //Hien tai dang loi - fix sau.
        $permissionArray = array();
        $admin = $observer->getUser();
        $adminPermission = Mage::getModel('inventoryplus/warehouse_permission')
                ->getCollection();
        foreach ($adminPermission as $permission) {
            if (!array_key_exists($permission->getAdminId(), $permissionArray)) {
                $permissionArray[$permission->getAdminId()] = '';
            }
            if (!array_key_exists($permission->getWarehouseId(), $permissionArray[$permission->getAdminId()])) {
                $permissionArray[$permission->getAdminId()][$permission->getWarehouseId()] = '';
            }
            if ($permissionArray[$permission->getAdminId()][$permission->getWarehouseId()] == '') {
                $permissionArray[$permission->getAdminId()][$permission->getWarehouseId()]['can_edit'] = !$permission->getCanEdit() ? '0' : $permission->getCanEdit();
                $permissionArray[$permission->getAdminId()][$permission->getWarehouseId()]['can_adjust'] = !$permission->getCanAdjust() ? '0' : $permission->getCanAdjust();
                $permissionArray[$permission->getAdminId()][$permission->getWarehouseId()]['can_send_request_stock'] = !$permission->getCanSendRequestStock() ? '0' : $permission->getCanSendRequestStock();
                $permissionArray[$permission->getAdminId()][$permission->getWarehouseId()]['can_physical'] = !$permission->getCanPhysical() ? '0' : $permission->getCanPhysical();
                $permissionArray[$permission->getAdminId()][$permission->getWarehouseId()]['can_purchase_product'] = !$permission->getCanPurchaseProduct() ? '0' : $permission->getCanPurchaseProduct();
            }
        }
        Mage::getSingleton('adminhtml/session')->setData('inventory_permission', $permissionArray);
    }

    public function salesOrderDeleteAfter($observer) {
        try {
            $order = $observer->getOrder();
            $items = $order->getAllItems();
            $warehouse_orders = Mage::getModel('inventoryplus/warehouse_order')
                    ->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());
            foreach ($warehouse_orders as $warehouse_order) {
                $qty_onhold = $warehouse_order->getQty();
                $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                        ->getCollection()
                        ->addFieldToFilter('product_id', $warehouse_order->getProductId())
                        ->addFieldToFilter('warehouse_id', $warehouse_order->getWarehouseId())
                        ->getFirstItem();
                if ($warehouse_product->getId()) {
                    //return available qty
                    $warehouse_product->setData('total_qty', $warehouse_product->getData('total_qty') - $qty_onhold)
                            ->save();
                    $warehouse_order->setData('qty', 0)
                            ->save();
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    protected function _refundHelper() {
        return Mage::helper('inventoryplus/refund');
    }
}
