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
 * Inventorybarcode Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Model_Observer {

    /**
     * process inventory_menu_list event
     *
     * @return Magestore_Inventorybarcode_Model_Observer
     */
    public function inventorybarcodeMenu($observer) {
        $menu = $observer->getEvent()->getMenus()->getMenu();

        $menu['barcode'] = array('label' => Mage::helper('inventoryplus')->__('Barcodes'),
            'sort_order' => 700,
            'url' => '',
            'active' => (in_array(Mage::app()->getRequest()->getControllerName(), array('adminhtml_searchbarcode', 'adminhtml_barcode', 'adminhtml_barcodeattribute'))) ? true : false,
            'level' => 0,
            'children' => array(
                'search_barcode' => array('label' => Mage::helper('inventoryplus')->__('Scan Barcodes'),
                    'sort_order' => 100,
                    'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inb_searchbarcode/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => (Mage::app()->getRequest()->getControllerName() == 'system_config') ? true : false,
                    'level' => 0),
//                'barcode_atribute' => array('label' => Mage::helper('inventoryplus')->__('Manage Barcode Attributes'),
//                    'sort_order' => 110,
//                    'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inb_barcodeattribute/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
//                    'active' => false,
//                    'level' => 1),
                'manage_barcode' => array('label' => Mage::helper('inventoryplus')->__('Manage Barcodes'),
                    'sort_order' => 120,
                    'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inb_barcode/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => (Mage::app()->getRequest()->getControllerName() == 'system_config') ? true : false,
                    'level' => 0)
            )
        );
        $observer->getEvent()->getMenus()->setData('menu', $menu);
    }

    /**
     * process inventorybarcode_barcodeattribute_save_after event
     *
     * @return Magestore_Inventorybarcode_Model_Observer
     */
    public function addBarcodeColumn($observer) {

        $barcodeAttribute = $observer->getEvent()->getBarcodeattribute();
        $tablename = 'inventorybarcode/barcode';
        $resource = Mage::getSingleton('core/resource');
        $tablename = $resource->getTableName($tablename);
        $readConnection = $resource->getConnection('core_read');
        $results = $readConnection->fetchAll("SHOW COLUMNS FROM " . $tablename . ";");

        $return = array();
        foreach ($results as $result) {
            $return[] = $result['Field'];
        }
        $db = Mage::getSingleton('core/resource')->getConnection('core_setup');
        if (!in_array($barcodeAttribute->getAttributeCode(), $return)) {
            $sql = 'ALTER TABLE ' . $tablename . ' ADD COLUMN `' . $barcodeAttribute->getAttributeCode() . '` varchar(255) default \'\';';

            if ($db->getTransactionLevel() > 0) {
                $db->commit();
            }

            $db->query($sql);
        }
    }

    public function saveDeliveryBefore($observer) {
        $data = $observer->getEvent()->getProducts();
        $purchaseOrderId = $observer->getEvent()->getPurchaseOrderId();
        //check dupplicate
        $warehouseIds = '';
        $barcode = array();
        $i = 0;

        foreach ($data as $productId => $enCoded) {
            $codeArr = array();
            parse_str(base64_decode($enCoded), $codeArr);

            if (!isset($codeArr['barcode']) || !$codeArr['barcode'])
                continue;

            foreach ($codeArr as $warehouse => $value) {
                if ($i > 0)
                    break;
                $id = explode('_', $warehouse);
                if ($id[0] == 'warehouse') {
                    if (!$warehouseIds) {
                        $warehouseIds = $id[1];
                    } else {
                        $warehouseIds .= ',' . $id[1];
                    }
                }
                $i++;
            }
//check dupplicate
            if (in_array($codeArr['barcode'], $barcode)) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('inventoryplus')->__('The barcode "%s" was already duplicate!', $codeArr['barcode'])
                );
                $url = Mage::helper("adminhtml")->getUrl("adminhtml/inpu_purchaseorders/newdelivery/", array('purchaseorder_id' => $purchaseOrderId, 'warehouse_ids' => $warehouseIds, 'action' => 'newdelivery',
                    'active' => 'delivery'));
                header('Location:' . $url);
                exit;
            } else {
                $barcode[] = $codeArr['barcode'];
            }
//check exist
            $checkBarcodeExist = Mage::getModel('inventorybarcode/barcode')->load($codeArr['barcode'], 'barcode');
            if ($checkBarcodeExist->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('inventorybarcode')->__('The barcode "%s" was already exist!', $codeArr['barcode'])
                );
                $url = Mage::helper("adminhtml")->getUrl("adminhtml/inpu_purchaseorders/newdelivery/", array('purchaseorder_id' => $purchaseOrderId, 'warehouse_ids' => $warehouseIds, 'action' => 'newdelivery',
                    'active' => 'delivery'));
                header('Location:' . $url);

                exit;
            }
        }
    }

    public function purchaseorderDeliverySaveAfter($observer) {
        if (!Mage::getStoreConfig('inventoryplus/barcode/createbarcode_afterdelivery'))
            return;

        $purchaseOrderId = $observer->getEvent()->getPurchaseOrderId();
        $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);


        $suppliererId = $purchaseOrder->getSupplierId();
        $productId = $observer->getEvent()->getProductId();
        $purchaseOrderProduct = Mage::getModel('inventorypurchasing/purchaseorder_product')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('purchase_order_id', $purchaseOrderId)
                ->getFirstItem();

        $deliverys = $observer->getEvent()->getData('data');

        $warehouseIds = array();
        foreach ($deliverys as $warehouse => $value) {
            if ($value) {
                $id = explode('_', $warehouse);
                if ($id[0] == 'warehouse') {
                    $warehouseIds[] = $id[1];
                }
            }
        }


        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $tablename = 'inventorybarcode/barcode';

        $results = Mage::helper('inventorybarcode/attribute')->getAllColumOfTable($tablename);

        $columns = array();
        $string = '';
        $type = '';

        foreach ($results as $result) {
            $fields = explode('_', $result);
            if ($fields[0] == 'barcode' || $fields[0] == 'qty')
                continue;
            foreach ($fields as $id => $field) {
                if ($id == 0)
                    $type = $field;
                if ($id == 1) {
                    $string = $field;
                }
                if ($id > 1)
                    $string = $string . '_' . $field;
            }
            $columns[] = array($type => $string);
            $string = '';
            $type = '';
        }




        $sqlNews = array();


        $codeArr = array();

        $codeArr['purchaseorder_purchase_order_id'] = $purchaseOrderId;
        $codeArr['supplier_supplier_id'] = $suppliererId;
        $codeArr['barcode_status'] = 1;
        $codeArr['warehouse_warehouse_id'] = $warehouseIds;

        //auto generate barcode
        if (!isset($deliverys['barcode']) || $deliverys['barcode'] == '') {
            $codeArr['barcode'] = Mage::helper('inventorybarcode')->generateCode(Mage::getStoreConfig('inventoryplus/barcode/pattern'));
        } else {
            $codeArr['barcode'] = $deliverys['barcode'];
        }

		$delivery = $observer->getEvent()->getDelivery();
		$delivery->setBarcode($codeArr['barcode']);
		$delivery->save();
        $sqlNews['barcode'] = $codeArr['barcode'];
        $sqlNews['barcode_status'] = $codeArr['barcode_status'];
        $sqlNews['qty'] = $deliverys['qty_delivery'];
       

        foreach ($columns as $id => $column) {
            $i = 0;
            $columnName = '';

            foreach ($column as $id => $key) {
                if ($i == 0)
                    $columnName = $id . '_' . $key;
                if ($i > 0)
                    $columnName = $columnName . '_' . $key;

                $i++;
            }

            if ($id != 'custom') {
                $return = Mage::helper('inventorybarcode')->getValueForBarcode($id, $key, $productId, $codeArr);
                if (is_array($return)) {
                    foreach ($return as $columns) {
                        foreach ($columns as $column => $value) {
                            if (!isset($sqlNews[$id . '_' . $column])) {
                                $sqlNews[$id . '_' . $column] = $value;
                            } else {
                                $sqlNews[$id . '_' . $column] .= ',' . $value;
                            }
                        }
                    }
                } else {
                    $sqlNews[$columnName] = $return;
                }
            }
        }
        $sqlNews['created_date'] = now();
        $sqlNews['qty_original'] = $deliverys['qty_delivery'];

        $admin = Mage::getModel('admin/session')->getUser()->getUsername();
        //create action log
        Mage::getModel('inventorybarcode/barcode_actionlog')->setData('barcode_action', Mage::helper('inventorybarcode')->__('Barcode "%s" was created automatically for Purchase order id #%s', $codeArr['barcode'], $codeArr['purchaseorder_purchase_order_id']))
                ->setData('created_at', now())
                ->setData('created_by', $admin)
                ->setData('barcode', $codeArr['barcode'])
                ->save();


        $writeConnection->insertMultiple($resource->getTableName('inventorybarcode/barcode'), $sqlNews);
        $sqlNews = array();



        try {
            $purchaseOrderProduct->setData('barcode', $codeArr['barcode'])
                    ->save();
        } catch (Exception $e) {
            
        }
    }

    public function saveAllDeliveryAfter($observer) {
        if (!Mage::getStoreConfig('inventoryplus/barcode/createbarcode_afterdelivery'))
            return;

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $tablename = 'inventorybarcode/barcode';

        $results = Mage::helper('inventorybarcode/attribute')->getAllColumOfTable($tablename);

        $columns = array();
        $string = '';
        $type = '';

        foreach ($results as $result) {
            $fields = explode('_', $result);
            if ($fields[0] == 'barcode' || $fields[0] == 'qty')
                continue;
            foreach ($fields as $id => $field) {
                if ($id == 0)
                    $type = $field;
                if ($id == 1) {
                    $string = $field;
                }
                if ($id > 1)
                    $string = $string . '_' . $field;
            }
            $columns[] = array($type => $string);
            $string = '';
            $type = '';
        }

        $warehouseIds = $observer->getEvent()->getWarehouseId();
        $purchaseOrderId = $observer->getEvent()->getPurchaseOrderId();
        $productId = $observer->getEvent()->getProductId();

        $purchaseOrderProduct = Mage::getModel('inventorypurchasing/purchaseorder_product')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('purchase_order_id', $purchaseOrderId)
                ->getFirstItem();
//        if ($purchaseOrderProduct->getId() && $purchaseOrderProduct->getBarcode()) {
//            return;
//        }

        $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);


        $suppliererId = $purchaseOrder->getSupplierId();

        $sqlNews = array();
        $codeArr = array();

        $codeArr['purchaseorder_purchase_order_id'] = $purchaseOrder->getId();
        $codeArr['supplier_supplier_id'] = $suppliererId;
        $codeArr['barcode_status'] = 1;
        $codeArr['warehouse_warehouse_id'] = $warehouseIds;


        //auto generate barcode
        $codeArr['barcode'] = Mage::helper('inventorybarcode')->generateCode(Mage::getStoreConfig('inventoryplus/barcode/pattern'));


        $sqlNews['barcode'] = $codeArr['barcode'];
        $sqlNews['barcode_status'] = $codeArr['barcode_status'];
        $sqlNews['qty'] = $observer->getEvent()->getQtyReceived();
       
        

        foreach ($columns as $id => $column) {
            $i = 0;
            $columnName = '';

            foreach ($column as $id => $key) {
                if ($i == 0)
                    $columnName = $id . '_' . $key;
                if ($i > 0)
                    $columnName = $columnName . '_' . $key;

                $i++;
            }

            if ($id != 'custom') {
                $return = Mage::helper('inventorybarcode')->getValueForBarcode($id, $key, $productId, $codeArr);
                if (is_array($return)) {
                    foreach ($return as $columns) {
                        foreach ($columns as $column => $value) {
                            if (!isset($sqlNews[$id . '_' . $column])) {
                                $sqlNews[$id . '_' . $column] = $value;
                            } else {
                                $sqlNews[$id . '_' . $column] .= ',' . $value;
                            }
                        }
                    }
                } else {
                    $sqlNews[$columnName] = $return;
                }
            }
        }
        $sqlNews['created_date'] = now();
        $sqlNews['qty_original'] = $observer->getEvent()->getQtyReceived();
        
        $writeConnection->insertMultiple($resource->getTableName('inventorybarcode/barcode'), $sqlNews);
        $sqlNews = array();


        try {
            $purchaseOrderProduct->setData('barcode', $codeArr['barcode'])
                    ->save();
        } catch (Exception $e) {
            
        }



        $admin = Mage::getModel('admin/session')->getUser()->getUsername();
        //create action log
        Mage::getModel('inventorybarcode/barcode_actionlog')->setData('barcode_action', Mage::helper('inventorybarcode')->__('Barcode "%s" was created automatically for Purchase order id #%s', $codeArr['barcode'], $codeArr['purchaseorder_purchase_order_id']))
                ->setData('created_at', now())
                ->setData('created_by', $admin)
                ->setData('barcode', $codeArr['barcode'])
                ->save();
    }

    /**
     * process delivery_product_grid_after event
     *
     * @return Magestore_Inventorybarcode_Model_Observer
     */
    public function deliveryProductGridAfter($observer) {
        if (!Mage::getStoreConfig('inventoryplus/barcode/createbarcode_afterdelivery'))
            return;
        $grid = $observer->getEvent()->getGrid();
        $grid->addColumn('barcode', array(
            'header' => Mage::helper('inventorypurchasing')->__('Barcode'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'barcode',
            'type' => 'input',
            'editable' => true,
            'edit_only' => true,
            'filter_condition_callback' => array($this, '_filterBarcode'),
            'renderer' => 'inventorybarcode/adminhtml_barcode_edit_renderer_custompo',
        ));
    }

    /**
     * process controller_action_predispatch_adminhtml_sales_order_shipment_new event
     *
     * @return Magestore_Inventorybarcode_Model_Observer
     */
    /*
      public function shipmentNew($observer) {
      $block = $observer->getEvent()->getBlock();

      if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Items' && $block->getRequest()->getControllerName() == 'sales_order_shipment' && $block->getRequest()->getActionName() == 'new') {
      $block->setTemplate('inventorybarcode/shipment/sales/order/shipment/create/items.phtml');
      }
      if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Items_Renderer_Default' && $block->getRequest()->getControllerName() == 'sales_order_shipment' && $block->getRequest()->getActionName() == 'new') {
      $block->setTemplate('inventorybarcode/shipment/sales/order/shipment/create/items/renderer/default.phtml');
      }
      if (get_class($block) == 'Mage_Bundle_Block_Adminhtml_Sales_Order_Items_Renderer' && $block->getRequest()->getControllerName() == 'sales_order_shipment' && $block->getRequest()->getActionName() == 'new') {
      $block->setTemplate('inventorybarcode/bundle/sales/shipment/create/items/renderer.phtml');
      }

      }
     *
     */

    /**
     * process sales_order_shipment_save_after event
     *
     * @return Magestore_Inventorybarcode_Model_Observer
     */
    public function shipmentAfterSave($observer) {
        try {
            if (Mage::registry('INVENTORY_BARCODE_ORDER_SHIPMENT'))
                return;
            Mage::register('INVENTORY_BARCODE_ORDER_SHIPMENT', true);
            $barcodeData = array();
            $data = Mage::app()->getRequest()->getParams();

            $shipment = $observer->getEvent()->getShipment();
            $orderId = $shipment->getOrder()->getId();
            foreach ($shipment->getAllItems() as $_item) {

                $item = Mage::getModel('sales/order_item')->load($_item->getOrderItemId());
                if (!isset($data['warehouse-shipment']['items'][$item->getItemId()])) {
                    continue;
                }
                if (in_array($item->getProductType(), array('bundle', 'grouped', 'virtual', 'downloadable')))
                    continue;

                $productId = $item->getProductId();
                if ($item->getProductType() == 'configurable') {
                    $itemData = unserialize($item->getData('product_options'));
                    $productSku = $itemData['simple_sku'];
                    $productId = Mage::getModel('catalog/product')->getIdBySku($productSku);
                }
                //row_total_incl_tax       
                $barcodeData[$item->getItemId()]['qty'] = '';

                if ($item->getParentItemId()) {

                    if (isset($data['shipment']['items'][$item->getParentItemId()])) {

                        $item_parrent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                        $options = $item->getProductOptions();
                        if (isset($options['bundle_selection_attributes'])) {

                            $option = unserialize($options['bundle_selection_attributes']);

                            $parentQty = $data['shipment']['items'][$item->getParentItemId()];

                            $itemQty = (int) $option['qty'] * (int) $parentQty;

                            $barcodeData[$item->getItemId()]['qty'] = $itemQty;
                            if (isset($data['barcode-shipment']['items'][$item->getItemId()]))
                                $barcodeData[$item->getItemId()]['barcode_id'] = $data['barcode-shipment']['items'][$item->getItemId()];

                            $barcodeData[$item->getItemId()]['warehouse_id'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                            $barcodeData[$item->getItemId()]['product_id'] = $productId;
                            $barcodeData[$item->getItemId()]['item_id'] = $item->getItemId();
                            $barcodeData[$item->getItemId()]['order_id'] = $orderId;
                        }else {
                            $barcodeData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getParentItemId()];
                            if (isset($data['barcode-shipment']['items'][$item->getParentItemId()]))
                                $barcodeData[$item->getItemId()]['barcode_id'] = $data['barcode-shipment']['items'][$item->getParentItemId()];
                            $barcodeData[$item->getItemId()]['warehouse_id'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                            $barcodeData[$item->getItemId()]['product_id'] = $productId;
                            $barcodeData[$item->getItemId()]['item_id'] = $item->getItemId();
                            $barcodeData[$item->getItemId()]['order_id'] = $orderId;
                        }
                    } else {
                        $barcodeData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getItemId()];
                        if (isset($data['barcode-shipment']['items'][$item->getItemId()]))
                            $barcodeData[$item->getItemId()]['barcode_id'] = $data['barcode-shipment']['items'][$item->getItemId()];
                        $barcodeData[$item->getItemId()]['warehouse_id'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                        $barcodeData[$item->getItemId()]['product_id'] = $productId;
                        $barcodeData[$item->getItemId()]['item_id'] = $item->getItemId();
                        $barcodeData[$item->getItemId()]['order_id'] = $orderId;
                    }
                } else {


                    if (isset($data['shipment']['items'][$item->getItemId()])) {
                        $barcodeData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getItemId()];
                        if (isset($data['barcode-shipment']['items'][$item->getItemId()]))
                            $barcodeData[$item->getItemId()]['barcode_id'] = $data['barcode-shipment']['items'][$item->getItemId()];
                    }elseif (isset($data['shipment']['items'][$item->getParentItemId()])) {
                        $barcodeData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getParentItemId()];
                        if (isset($data['barcode-shipment']['items'][$item->getParentItemId()]))
                            $barcodeData[$item->getItemId()]['barcode_id'] = $data['barcode-shipment']['items'][$item->getParentItemId()];
                    }

                    $barcodeData[$item->getItemId()]['warehouse_id'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                    $barcodeData[$item->getItemId()]['product_id'] = $productId;
                    $barcodeData[$item->getItemId()]['item_id'] = $item->getItemId();
                    $barcodeData[$item->getItemId()]['order_id'] = $orderId;
                }
                if ($barcodeData[$item->getItemId()]['qty'] > ($item->getQtyOrdered() - $item->getQtyRefunded())) {
                    $barcodeData[$item->getItemId()]['qty'] = ($item->getQtyOrdered() - $item->getQtyRefunded());
                }
            }

            foreach ($barcodeData as $_barcodeData) {
                if (!isset($_barcodeData['barcode_id']) || !$_barcodeData['barcode_id'])
                    continue;
                $barcode = Mage::getModel('inventorybarcode/barcode')->load($_barcodeData['barcode_id']);
                $qty = $barcode->getQty() - $_barcodeData['qty'];
                try {
                    $barcode->setQty($qty)->save();
                    $barcodeShipment = Mage::getModel('inventorybarcode/barcode_shipment')->getCollection()
                            ->addFieldToFilter('barcode_id', $_barcodeData['barcode_id'])
                            ->addFieldToFilter('order_id', $_barcodeData['order_id'])
                            ->addFieldToFilter('item_id', $_barcodeData['item_id'])
                            ->addFieldToFilter('product_id', $_barcodeData['product_id'])
                            ->addFieldToFilter('warehouse_id', $_barcodeData['warehouse_id'])
                            ->getFirstItem();
                    if ($barcodeShipment->getId()) {
                        $barcodeShipment->setQtyShipped($barcodeShipment->getQtyShipped() + $_barcodeData['qty'])->save();
                    } else {

                        Mage::getModel('inventorybarcode/barcode_shipment')
                                ->setData('barcode_id', $_barcodeData['barcode_id'])
                                ->setData('order_id', $_barcodeData['order_id'])
                                ->setData('item_id', $_barcodeData['item_id'])
                                ->setData('product_id', $_barcodeData['product_id'])
                                ->setData('warehouse_id', $_barcodeData['warehouse_id'])
                                ->setData('qty_shipped', $_barcodeData['qty'])
                                ->save();
                    }
                } catch (Exception $e) {

                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     * process sales_order_creditmemo_save_after event
     *
     * @return Magestore_Inventorybarcode_Model_Observer
     */
    public function orderCreditmemoSaveAfter($observer) {

        if (Mage::registry('INVENTORY_BARCODE_ORDER_CREDITMEMO'))
            return;
        Mage::register('INVENTORY_BARCODE_ORDER_CREDITMEMO', true);

        $data = Mage::app()->getRequest()->getParams();
        $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();
        $inventoryCreditmemoData = array();

        $order_id = $order->getId();
        $creditmemo_id = $creditmemo->getId();

        foreach ($creditmemo->getAllItems() as $creditmemo_item) {

            if (isset($data['creditmemo']['select-warehouse-supplier'][$creditmemo_item->getOrderItemId()]) && $data['creditmemo']['select-warehouse-supplier'][$creditmemo_item->getOrderItemId()] == 2) {
                continue;
            }

            $item = Mage::getModel('sales/order_item')->load($creditmemo_item->getOrderItemId());
            if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped')))
                continue;


            //row_total_incl_tax  

            if ($item->getParentItemId()) {

                if (isset($data['creditmemo']['items'][$item->getParentItemId()])) {

                    if (isset($data['creditmemo']['select-warehouse-supplier'][$item->getParentItemId()]) && $data['creditmemo']['select-warehouse-supplier'][$item->getParentItemId()] == 2) {
                        continue;
                    }

                    $item_parrent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                    $options = $item->getProductOptions();
                    if (isset($options['bundle_selection_attributes'])) {
                        $option = unserialize($options['bundle_selection_attributes']);

                        $parentQty = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                        $qtyRefund = (int) $option['qty'] * (int) $parentQty;
                        $qtyShipped = $item->getQtyShipped();
                        $qtyRefunded = $item->getQtyRefunded();
                        $qtyOrdered = $item->getQtyOrdered();

                        $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                        $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();

                        //////////
                        //if return to stock
                        /*
                         * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                         * available qty will be returned = qtyRefund
                         */

                        if (isset($data['creditmemo']['items'][$item->getParentItemId()]['back_to_stock'])) {
                            $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getParentItemId()];

                            $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            if ($qtyChecked >= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyRefund;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                            }
                        } else {
                            continue;
                        }

                        //////////
                    } else {

                        $qtyRefund = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                        $qtyShipped = $item->getQtyShipped();
                        $qtyRefunded = $item->getQtyRefunded();
                        $qtyOrdered = $item->getQtyOrdered();

                        //////////
                        //if return to stock
                        /*
                         * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                         * available qty will be returned = qtyRefund
                         */


                        if (isset($data['creditmemo']['items'][$item->getParentItemId()]['back_to_stock'])) {


                            $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getParentItemId()];
                            if ($qtyChecked >= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyRefund;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                            }
                        } else {
                            continue;
                        }


                        $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                        $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
                    }
                } else {

                    $qtyRefund = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                    $qtyShipped = $item->getQtyShipped();
                    $qtyRefunded = $item->getQtyRefunded();
                    $qtyOrdered = $item->getQtyOrdered();

                    //////////
                    //if return to stock
                    /*
                     * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                     * available qty will be returned = qtyRefund
                     */


                    if (isset($data['creditmemo']['items'][$item->getItemId()]['back_to_stock'])) {


                        $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                        if ($qtyChecked >= 0) {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyRefund;
                        } else {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                        }

                        $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getItemId()];
                    } else {
                        continue;
                    }
                    $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                    $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
                }
            } else {
                $qtyRefund = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                $qtyShipped = $item->getQtyShipped();
                $qtyRefunded = $item->getQtyRefunded();
                $qtyOrdered = $item->getQtyOrdered();

                //////////
                //if return to stock
                /*
                 * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                 * available qty will be returned = qtyRefund
                 */

                if (isset($data['creditmemo']['items'][$item->getItemId()]['back_to_stock'])) {

                    $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                    if ($qtyChecked >= 0) {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyRefund;
                    } else {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                    }

                    $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getItemId()];
                } else {
                    continue;
                }

                $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                $inventoryCreditmemoData[$item->getItemId()]['item_id'] = $item->getItemId();
            }
        }

        foreach ($inventoryCreditmemoData as $id => $value) {

            try {
                $barcodeShipment = Mage::getModel('inventorybarcode/barcode_shipment')->getCollection()
                        ->addFieldToFilter('order_id', $order_id)
                        ->addFieldToFilter('product_id', $value['product'])
                        ->addFieldToFilter('warehouse_id', $value['warehouse'])
                        ->getFirstItem();
                if ($barcodeShipment->getId()) {
                    if ($value['qty_total'] > ($barcodeShipment->getQtyShipped() + $barcodeShipment->getQtyRefunded())) {
                        $qty = $barcodeShipment->getQtyShipped() + $barcodeShipment->getQtyRefunded();
                    } else {
                        $qty = $value['qty_total'];
                    }
                    $barcodeShipment->setQtyRefunded($barcodeShipment->getQtyRefunded() + $qty)->save();
                    $barcode = Mage::getModel('inventorybarcode/barcode')->load($barcodeShipment->getBarcodeId());
                    $barcode->setQty($barcode->getQty() + $qty)->save();
                }
            } catch (Exception $e) {

                Mage::log($e->getTraceAsString(), null, 'inventory_management.log');
            }
        }
    }

}
