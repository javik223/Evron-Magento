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
 * Inventorywarehouse Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Model_Observer {

    public function add_more_permission($observer) {
        $assignment = $observer->getEvent()->getPermission();
        $data = $observer->getEvent()->getData();
        $adminId = $observer->getEvent()->getAdminId();
        $changePermissions = $observer->getEvent()->getChangePermssions();

        $transfers = array();
        if (isset($data['data']['transfer']) && is_array($data['data']['transfer'])) {
            $transfers = $data['data']['transfer'];
        }

        if ($assignment->getId()) {
            $oldTransfer = $assignment->getCanSendRequestStock();
        }

        if (in_array($adminId, $transfers)) {
            if ($assignment->getId()) {
                if ($oldTransfer != 1) {
                    $changePermissions[$adminId]['old_transfer'] = Mage::helper('inventoryplus')->__('Cannot transfer Warehouse');
                    $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Can transfer Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_transfer'] = '';
                $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Can transfer Warehouse');
            }
            $assignment->setData('can_send_request_stock', 1);
        } else {
            if ($assignment->getId()) {
                if ($oldTransfer != 0) {
                    $changePermissions[$adminId]['old_transfer'] = Mage::helper('inventoryplus')->__('Can transfer Warehouse');
                    $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Cannot transfer Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_transfer'] = '';
                $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Cannot transfer Warehouse');
            }
            $assignment->setData('can_send_request_stock', 0);
        }
    }

    public function column_permission_grid($observer) {
        $columns = $observer->getEvent()->getGrid();
        $disabledvalue = $observer->getEvent()->getDisabled();
        $columns->addColumn('can_send_request_stock', array(
            'header' => Mage::helper('inventorywarehouse')->__('Send/Request Stock'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'transfer[]',
            'values' => $this->_getSelectedCanTransferAdmins()
        ));
    }

    protected function _getSelectedCanTransferAdmins() {
        $warehouse = $this->getWarehouse();

        if ($warehouse->getId())
            $canSendRequestAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse->getId())
                    ->getAllCanSendRequestAdmins();
        else {
            $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
            $canSendRequestAdmins = array($adminId);
        }

        return $canSendRequestAdmins;
    }

    public function getWarehouse() {
        return Mage::getModel('inventoryplus/warehouse')
                        ->load(Mage::app()->getRequest()->getParam('id'));
    }

    /**
     * set template for adjust stock add new
     *
     */
    public function adjust_stock_html($observer) {
        $block = $observer->getEvent()->getBlock();
        $block->setTemplate('inventorywarehouse/adjuststock/new.phtml');
    }

    /**
     * Select warehouse to subtract stock when customers create order
     *
     */
    public function salesOrderPlaceAfter($observer) {

        if (Mage::registry('INVENTORY_CORE_ORDER_PLACE'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_PLACE', true);
        $order = $observer->getOrder();
        if (Mage::app()->getStore()->isAdmin()) {
            $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
            $storeId = $sessionQuote->getStore()->getId();
        } else {
            $storeId = $order->getStoreId();
        }
        $items = $order->getAllItems();
        $selectWarehouse = Mage::getStoreConfig('inventoryplus/general/select_warehouse');
        $ShippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        foreach ($items as $item) {
            $warehouseId = 0;
            $manageStock = Mage::helper('inventoryplus')->getManageStockOfProduct($item->getProductId());
            if (!$manageStock) {
                continue;
            }

            if ($item->getProduct()->isComposite()) {
                continue;
            }

            //integrate with WebPOS 2.0 or later
            if (Mage::helper('core')->isModuleEnabled('Magestore_Webpos') && Mage::helper('core')->isModuleEnabled('Magestore_Inventorywebpos')) {
                if (Mage::helper('inventoryplus')->isWebPOS20Active()) {
                    $warehouseId = Mage::helper('inventorywebpos')->_getCurrentWarehouseId();
                }
            }

            //integrate with Store Pickup
            if (Mage::helper('core')->isModuleEnabled('Magestore_Storepickup')) {
                if ($storeWarehouseId = Mage::helper('inventorywarehouse/storepickup')->getSelectedWarehouseId($order)) {
                    $warehouseId = $storeWarehouseId;
                }
            }

            if (!isset($warehouseId) || !$warehouseId) {
                $warehouseId = Mage::helper('inventorywarehouse/warehouse')->getQtyProductWarehouse($storeId, $item->getProductId(), $selectWarehouse, $ShippingAddress, $billingAddress);
            }
            if (!$warehouseId) {
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
     * Insert new record to warehouse_shipment
     * 
     * @param  Magestore_Inventoryplus_Model_Warehouse_Shipment $model
     */
    public function saveWarehouseShipment($model) {
        $resource = Mage::getModel('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $data = $model->getData();
        if (!$data['warehouse_shipment_id']) {
            $fieldsString = '(`' . implode('`, `', array_keys($data)) . '`)';
            $valuesString = " VALUES ('" . implode("', '", $data) . "')";
            $sql = "INSERT INTO {$resource->getTableName('erp_inventory_warehouse_shipment')} "
                    . $fieldsString
                    . $valuesString;
            $writeConnection->query($sql);
        }
    }

    public function salesOrderShipmentSaveBefore($observer) {
        $data = Mage::app()->getRequest()->getParams();
        if (isset($data['invoice']['do_shipment']) && $data['invoice']['do_shipment'] == 1) {
            $items = $data['invoice']['items'];
            $check = Mage::helper('inventorywarehouse')->checkShipment($items);
            if ($check == 0) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('Can\'t create shipment , there are no warehouse enough stock'));
                throw new Exception("No warehouse enough stock");
            }
        }
    }

    /*
     * catch event sales order shipment save after
     */

    public function salesOrderShipmentSaveAfter($observer) {
        $warehouseRelateds = array();  // Storage all warehouse related for create new warehouse transaction.	
        $transactionProductData = array(); // Storage data for creating new warehouse transaction product.
        $shipmentItemData = array();  // Get data from sales/order_shipment_item_collection to array.
        $helperClass = Mage::helper('inventorywarehouse/warehouseshipment');
        $data = Mage::app()->getRequest()->getParams();
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $shipmentId = $shipment->getId();
        $helperClass->sendObject($observer, $data, $shipment, $order);
        if ($helperClass->isIgnoreObserver() == true)
            return;
        try {
            if (Mage::registry('INVENTORY_WAREHOUSE_ORDER_SHIPMENT_' . $shipmentId))
                return;
            Mage::register('INVENTORY_WAREHOUSE_ORDER_SHIPMENT_' . $shipmentId, true);
            $shipmentItems = Mage::getResourceModel('sales/order_shipment_item_collection')->addFieldToFilter('parent_id', $shipmentId);
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
                    if (!in_array($warehouse_id, $warehouseRelateds, true))
                        $warehouseRelateds[] = $warehouse_id;
                    $helperClass->_saveModelWarehouseShipment($warehouse_id, $item, $product_id, $qtyShipped);
                    $warehouseProduct = $helperClass->_saveModelWarehouseProduct($warehouse_id, $product_id, $qtyShipped);
                    $warehouseOr = $helperClass->_saveModelWarehouseOrder($warehouse_id, $product_id, $qtyShipped, $warehouseProduct);
                    if ($warehouseOr) {
                        if ($warehouseOr->getQty() > 0)
                            $order->setShippingProgress(1);
                        if ($warehouseOr->getQty() == 0)
                            $order->setShippingProgress(2);
                    }else {
                        if (in_array($order->getStatus(), array('canceled', 'closed', 'complete')))
                            $order->setShippingProgress(2);
                        else if ($item->getQtyOrdered() == $item->getQtyShipped())
                            $order->setShippingProgress(2);
                        else
                            $order->setShippingProgress(1);
                    }
                    $transactionProductData[$warehouse_id][$product_id]['qty_shipped'] = $qtyShipped;
                    $transactionProductData[$warehouse_id][$product_id]['product_name'] = $item->getName();
                    $transactionProductData[$warehouse_id][$product_id]['product_sku'] = $item->getSku();
                }//Endif qtyShipped >0
            }// Endforeach $order->getAllItems()
            /* Create send transaction */
            $transactionSendData = $helperClass->_prepareTransactionData($observer);
            foreach ($warehouseRelateds as $warehouseRelated) {
                $totalQty = 0;
                $transactionSendData['warehouse_id_from'] = $warehouseRelated;
                $transactionSendData['warehouse_name_from'] = Mage::helper('inventorywarehouse/warehouse')->getWarehouseNameByWarehouseId($warehouseRelated);
                $transactionSendModel = $helperClass->_saveModelTransaction($transactionSendData);
                foreach ($transactionProductData[$warehouseRelated] as $productId => $transactionProduct) {
                    $helperClass->_saveModelTransactionProduct($transactionSendModel->getId(), $productId, $transactionProduct);
                    $totalQty += $transactionProduct['qty_shipped'];
                }
                $transactionSendModel->setTotalProducts(-$totalQty)->save();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    //add more transaction warehouse tab
    public function addWarehouseTab($observer) {
        $warehouseId = $observer->getEvent()->getWarehouseId();
        if ($warehouseId) {
            $tab = $observer->getTab();
            $tab->addTab('transaction_section', array(
                'label' => Mage::helper('inventorywarehouse')->__('Stock Movements'),
                'title' => Mage::helper('inventorywarehouse')->__('Stock Movements'),
                'url' => $tab->getUrl('adminhtml/inw_warehouse/transaction', array(
                    '_current' => true,
                    'id' => $warehouseId,
                )),
                'class' => 'ajax'
            ));
        }
        $this->addTabStore($observer);
    }

    /**
     * Add tabs to Warehouse page
     * 
     * @param object $observer
     */
    public function inventory_adminhtml_add_top_tab_warehouse($observer) {
        $warehouse = $observer->getEvent()->getWarehouse();
        if (!$warehouse->getId())
            return;
        $tab = $observer->getEvent()->getTab();
        $tab->addTab('dashboard_section', array(
            'label' => Mage::helper('inventorywarehouse')->__('Dashboard'),
            'title' => Mage::helper('inventorywarehouse')->__('Dashboard'),
            'content' => $tab->getLayout()
                    ->createBlock('inventorywarehouse/adminhtml_warehouse_edit_tab_dashboard')
                    ->toHtml(),
        ));
    }

    /**
     * Add store view to warehouse
     *
     * @param $observer
     */
    public function addTabStore($observer) {
        if (Mage::getStoreConfig('inventoryplus/general/select_warehouse') != 4) {
            return;
        }
        $tab = $observer->getEvent()->getTab();
        $tab->addTab('store_section', array(
            'label' => Mage::helper('inventorywarehouse')->__('Associated Stores'),
            'title' => Mage::helper('inventorywarehouse')->__('Associated Stores'),
            'url' => $tab->getUrl('adminhtml/inw_warehouse/store', array(
                '_current' => true,
                'id' => $tab->getRequest()->getParam('id'),
                'store' => $tab->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));
    }

    public function warehouse_controller_index($observer) {

        $controller = $observer->getEvent()->getWarehouseControler();

        $controller->getLayout()->getBlock('head')->addJs('magestore/inventorywarehouse/warehouse.js');
    }

    public function warehouse_add_new_product($observer) {

        $data = $observer->getEvent()->getData();
        $warehouse = $observer->getEvent()->getWarehouse();

        if (isset($data['data']['warehouse_add_products'])) {
            $warehousenewProducts = array();
            $warehousenewProductsExplodes = explode('&', urldecode($data['data']['warehouse_add_products']));
            if (count($warehousenewProductsExplodes) <= 900) {
                parse_str(urldecode($data['data']['warehouse_add_products']), $warehousenewProducts);
            } else {
                foreach ($warehousenewProductsExplodes as $warehouseProductsExplode) {
                    $warehouseProduct = '';
                    parse_str($warehousenewProductsExplodes, $warehouseProduct);
                    $warehousenewProducts = $warehousenewProducts + $warehouseProduct;
                }
            }

            if (count($warehousenewProducts)) {

                $productIds = '';
                foreach ($warehousenewProducts as $pId => $enCoded) {
                    $codeArr = array();
                    parse_str(base64_decode($enCoded), $codeArr);
                    try {
                        $warehouseProductsItem = Mage::getModel('inventoryplus/warehouse_product');
                        $warehouseProductsItem->setData('warehouse_id', $warehouse->getId())
                                ->setData('product_id', $pId)
                                ->setData('total_qty', 0)
                                ->setData('available_qty', 0)
                                ->setData('created_at', now())
                                ->setData('updated_at', now())
                                ->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }

    /* add warehouse and supplier when create product */

    public function catalogProductSaveAfterEvent($observer) {
        if (Mage::registry('INVENTORY_WAREHOUSE_CREATE_PRODUCT'))
            return;
        Mage::register('INVENTORY_WAREHOUSE_CREATE_PRODUCT', true);

        //if import dataflow
        if (Mage::app()->getRequest()->getActionName() == 'batchRun') {
            return;
        }

        $product = $observer->getProduct();
        $productId = $product->getId();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        if (Mage::getModel('admin/session')->getData('inventory_catalog_product_duplicate')) {
            $currentProductId = Mage::getModel('admin/session')->getData('inventory_catalog_product_duplicate');
            $warehouseProducts = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('product_id', $currentProductId);
            foreach ($warehouseProducts as $warehouseProduct) {
                $newWarehouseProduct = Mage::getModel('inventoryplus/warehouse_product');
                $newWarehouseProduct->setData('product_id', $productId)
                        ->setData('warehouse_id', $warehouseProduct->getWarehouseId())
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
        if (isset($post['inventory_select_warehouse'])) {
            $warehouses = $post['inventory_select_warehouse'];
        } else {
            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->getFirstItem();
            if ($warehouseProduct->getId())
                return;
            $firstWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
            if ($firstWarehouse->getId()) {
                $warehouses[] = array('warehouse_id' => $firstWarehouse->getId(), 'qty' => 0);
            }
        }
        try {
            $totalQty = 0;
            foreach ($warehouses as $warehouse) {
                $totalQty += $warehouse['qty'];
		$warehouseModel = Mage::getModel('inventoryplus/warehouse')->load($warehouse['warehouse_id']);
		$warehouseModel->updateStock($product->getId(), $warehouse['qty'], $warehouse['qty'], true);
            }
	
	    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            $stockItem->setData('qty', $totalQty);
            $stockItem->setData('is_in_stock', $isInStock);
            $stockItem->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    public function catalogModelProductDuplicate($observer) {
        $currentProduct = $observer->getCurrentProduct();
        $currentProductId = $currentProduct->getId();
        Mage::getModel('admin/session')->setData('inventory_catalog_product_duplicate', $currentProductId);
    }

    /**
     * Add stock action buttons to inventory listing page
     * 
     * @param Object $observer
     */
    public function inventoryplus_inventory_stock_action_buttons($observer) {
        $actionsObject = $observer->getEvent()->getData('stock_actions_object');
        $actions = $actionsObject->getActions();
        /*
          $actions['request_stock'] = array('params' => array(
          'label' => Mage::helper('inventorywarehouse')->__('Request Stock'),
          'onclick' => 'location.href=\''.Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inw_requeststock/new').'\'',
          'class' => 'save',
          ),
          'position' => -100
          );
         */
        $actions['send_stock'] = array('params' => array(
                'label' => Mage::helper('inventorywarehouse')->__('Send Stock'),
                'onclick' => 'location.href=\'' . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inw_sendstock/new') . '\'',
                'class' => 'add',
            ),
            'position' => -200
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
        if (!Mage::helper('inventoryplus/warehouse')->isAllowAction('send_request_stock', $warehouse))
            return;

        $actionsObject = $observer->getEvent()->getData('stock_actions_object');
        $stockActions = $actionsObject->getActions();

        $stockActions['requeststock'] = array('params' => array(
                'label' => Mage::helper('inventoryplus')->__('Request Stock'),
                'onclick' => 'location.href=\'' . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inw_requeststock/new', array('warehouse_id' => $warehouse->getId())) . '\'',
                'class' => 'add',
            ),
            'position' => -92,
        );

        $stockActions['sendstock'] = array('params' => array(
                'label' => Mage::helper('inventoryplus')->__('Send Stock'),
                'onclick' => 'location.href=\'' . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/inw_sendstock/new', array('warehouse_id' => $warehouse->getId())) . '\'',
                'class' => 'save',
            ),
            'position' => -91,
        );
        $actionsObject->setActions($stockActions);
    }

    /**
     * Check isSalable of product by warehouse
     *
     * @param $observer
     */
    public function checkProductIsSalableInWarehouse($observer) {
        if (Mage::getStoreConfig('inventoryplus/general/select_warehouse') != 4) {
            return;
        }
        $salable = $observer->getEvent()->getSalable()->getIsSalable();

        if (!$salable) {
            return;
        }

        $storeGroupId = Mage::app()->getStore()->getGroupId();
        $storeGroup = Mage::getModel('core/store_group')->load($storeGroupId);
        $warehouseId = $storeGroup->getWarehouseId();

        $product = $observer->getEvent()->getProduct();
        if ($product->isComposite()) {
            $salable = true;
        } else {
            $productId = $product->getId();

            $availableQtyInWarehouse = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId))
                    ->addFieldToFilter('product_id', array('eq' => $productId))
                    ->getFirstItem()
                    ->getAvailableQty();

            if ($availableQtyInWarehouse && $availableQtyInWarehouse > 0) {
                $salable = true;
            } else {
                $salable = false;
            }
        }
        $observer->getEvent()->getSalable()->setIsSalable($salable);
    }

    /**
     * Check when update qty in checkout
     *
     * @param $observer
     */
    public function checkQuoteItemQtyByWarehouse($observer) {
        if (Mage::getStoreConfig('inventoryplus/general/select_warehouse') != 4) {
            return;
        }
        $quoteItem = $observer->getItem();
        if ($quoteItem->getHasError()) {
            return;
        }
        $qty = $quoteItem->getQty();
        $product = $quoteItem->getProduct();
        $items = array();
        if ($product->isComposite()) {
            if ($product->getTypeId() == 'configurable') {
                $simpleProductId = $quoteItem->getOptionByCode('simple_product')->getProduct()->getId();
                $items[] = $simpleProductId;
            } elseif ($product->getTypeId() == 'bundle') {
                $collection = $product->getTypeInstance(true)->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product), $product
                );
                foreach ($collection as $item) {
                    $items[] = $item->product_id;
                }
            } elseif ($product->getTypeId() == 'grouped') {
                $items = $product->getTypeInstance(true)->getAssociatedProducts($product);
            }
        } else {
            $simpleProductId = $quoteItem->getProductId();
            $items[] = $simpleProductId;
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
            $storeId = $sessionQuote->getStore()->getId();
            $storeGroupId = Mage::getModel('core/store')->load($storeId)->getGroupId();
        } else {
            $storeGroupId = Mage::app()->getStore()->getGroupId();
        }
        $storeGroup = Mage::getModel('core/store_group')->load($storeGroupId);
        $warehouseId = $storeGroup->getWarehouseId();

        foreach ($items as $simpleProductId) {
            $availableQtyInWarehouse = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId))
                    ->addFieldToFilter('product_id', array('eq' => $simpleProductId))
                    ->getFirstItem()
                    ->getAvailableQty();
            if ($availableQtyInWarehouse) {
                if ($qty > $availableQtyInWarehouse) {
                    if (Mage::app()->getStore()->isAdmin()) {
                        $quoteItem->setMessage(
                                Mage::helper('inventoryplus')->__('Product "%s" in warehouse is currently out of stock.', $product->getName())
                        );
                    } else {
                        $quoteItem->addErrorInfo(
                                'inventoryplus', Mage_CatalogInventory_Helper_Data::ERROR_QTY, Mage::helper('inventoryplus')->__('Product "%s" in warehouse is currently out of stock.', $product->getName())
                        );
                        $quoteItem->getQuote()->addErrorInfo(
                                'stock', 'inventoryplus', Mage_CatalogInventory_Helper_Data::ERROR_QTY, Mage::helper('inventoryplus')->__('Product "%s" in warehouse is currently out of stock.', $product->getName())
                        );
                    }
                    return;
                }
            }
        }
    }

    /**
     * Save stores in warehouse
     *
     * @param $observer
     * @throws Exception
     */
    public function inventoryplus_warehouse_save_after($observer) {
        $warehouse = $observer->getEvent()->getWarehouse();

        Mage::helper('inventorywarehouse/storepickup')->updateStore($warehouse);
        
        Mage::helper('inventorywarehouse/storelocator')->updateStore($warehouse);

        if (Mage::getStoreConfig('inventoryplus/general/select_warehouse') != 4) {
            return;
        }
        //Get action from observer
        if ($data = Mage::app()->getRequest()->getPost()) {
            $warehouse = $observer->getEvent()->getWarehouse();
            $warehouseId = $warehouse->getId();
            // Get all stores of warehouse
            $existingStoresOfWarehouse = Mage::getModel('core/store_group')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId));

            // Get all unchecked stores
            $uncheckedStoreIds = array();
            foreach ($existingStoresOfWarehouse as $store) {
                if (!isset($data['selected_store']) || !$data['selected_store']) {
                    array_push($uncheckedStoreIds, $store->getId());
                } else {
                    if (!in_array($store->getId(), $data['selected_store'])) {
                        array_push($uncheckedStoreIds, $store->getId());
                    }
                }
            }

            // Save warehouseId in all checked stores
            if (isset($data['selected_store']) && $data['selected_store']) {
                foreach ($data['selected_store'] as $selectedStoreId) {
                    $selectedStore = Mage::getModel('core/store_group')->load($selectedStoreId);
                    $selectedStore->setWarehouseId($warehouseId);
                    try {
                        $selectedStore->save();
                    } catch (Exception $e) {
                        throw $e;
                    }
                }
            }

            // For all unchecked warehouses, set warehouseId to primary  warehouse in system
            $rootWarehouse = Mage::helper('inventoryplus/warehouse')->getPrimaryWarehouse();
            $rootWarehouseId = $rootWarehouse->getId();
            foreach ($uncheckedStoreIds as $uncheckedStoreId) {
                $uncheckedStore = Mage::getModel('core/store_group')->load($uncheckedStoreId);
                $uncheckedStore->setWarehouseId($rootWarehouseId);
                try {
                    $uncheckedStore->save();
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Add warehouse linking form into store pickup
     * 
     * @param Varien_Object $observer
     */
    public function storepickup_general_information_tab_before($observer) {
        $storeId = $observer->getEvent()->getStoreId();
        $tab = $observer->getEvent()->getTab();
        $warehouseForm = Mage::app()->getLayout()->createBlock('inventorywarehouse/adminhtml_storepickup_selectwarehouse');
        $tab->setContent($warehouseForm->toHtml() . $tab->getContent());
    }

    public function storelocator_general_information_tab_before($observer) {
        $storeId = $observer->getEvent()->getStoreId();
        $tab = $observer->getEvent()->getTab();
        $warehouseForm = Mage::app()->getLayout()->createBlock('inventorywarehouse/adminhtml_storelocator_selectwarehouse');
        $tab->setContent($warehouseForm->toHtml() . $tab->getContent());
    }

    /**
     * 
     * @param Varien_Object $observer
     */
    public function storepickup_save_after($observer) {
        Mage::helper('inventorywarehouse/storepickup')->stopSync('storepickup_store');

        if (!Mage::helper('inventorywarehouse/storepickup')->doSync('inventoryplus_warehouse')) {
            return;
        }

        $store = $observer->getEvent()->getStorepickup();
        if ($store->getId()) {
            $beforeWarehouse = Mage::getResourceModel('inventoryplus/warehouse_collection')
                    ->addFieldToFilter('storepickup_id', $store->getId())
                    ->getFirstItem();
            if ($beforeWarehouse->getId() != $store->getWarehouseId()) {
                if ($beforeWarehouse->getId())
                    $beforeWarehouse->setStorepickupId(null)->save();
            } else {
                if (!Mage::app()->getRequest()->getParam('store')) {
                    $beforeWarehouse->addData(Mage::helper('inventorywarehouse/storepickup')->storeToWarehouse($store));
                    if ($beforeWarehouse->getId())
                            $beforeWarehouse->save();
                }
                return;
            }
        }
        if ($store->getWarehouseId()) {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($store->getWarehouseId());
            $warehouse->setStorepickupId($store->getId());
            if (!Mage::app()->getRequest()->getParam('store')) {
                $warehouse->addData(Mage::helper('inventorywarehouse/storepickup')->storeToWarehouse($store));
            }
            if ($warehouse->getId())
                $warehouse->save();
        }
    }

    /**
     * 
     * @param Varien_Object $observer
     */
    public function storelocator_save_after($observer) {

        Mage::helper('inventorywarehouse/storelocator')->stopSync('storelocator_store');

        if (!Mage::helper('inventorywarehouse/storelocator')->doSync('inventoryplus_warehouse')) {
            return;
        }

        $store = $observer->getEvent()->getStorelocator();
        if ($store->getId()) {
            $beforeWarehouse = Mage::getResourceModel('inventoryplus/warehouse_collection')
                    ->addFieldToFilter('storelocator_id', $store->getId())
                    ->getFirstItem();
            if ($beforeWarehouse->getId() != $store->getWarehouseId()) {
                $beforeWarehouse->setStorelocatorId(null);
                if ($beforeWarehouse->getId())
                    $beforeWarehouse->save();
            } else {
                if (!Mage::app()->getRequest()->getParam('store')) {
                    $beforeWarehouse->addData(Mage::helper('inventorywarehouse/storelocator')->storeToWarehouse($store));
                    if ($beforeWarehouse->getId())
                        $beforeWarehouse->save();
                }
                return;
            }
        }


        if ($store->getWarehouseId()) {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($store->getWarehouseId());
            $warehouse->setStorelocatorId($store->getId());
            if (!Mage::app()->getRequest()->getParam('store')) {
                $warehouse->addData(Mage::helper('inventorywarehouse/storelocator')->storeToWarehouse($store));
            }
            if ($warehouse->getId())
                $warehouse->save();
        }
    }

}
