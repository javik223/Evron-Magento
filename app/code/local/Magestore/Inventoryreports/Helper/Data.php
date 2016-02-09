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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Helper_Data extends Mage_Core_Helper_Abstract {

    //get stockmovement type
    public function getMovementType() {
        return array(
            1 => $this->__('Send stock to another Warehouse or other destination'),
            2 => $this->__('Receive stock from another Warehouse or other source'),
            3 => $this->__('Receive stock from Purchase Order Delivery'),
            4 => $this->__('Send stock to Supplier for Return Order'),
            5 => $this->__('Send stock to Customer for Shipment'),
            6 => $this->__('Receive stock from Customer Refund'),
        );
    }

    public function getMovementTypeText($type) {
        switch ($type) {
            case '1' : return $this->__('Send stock to another Warehouse or other destination');
                break;
            case '2' : return $this->__('Receive stock from another Warehouse or other source');
                break;
            case '3' : return $this->__('Receive stock from Purchase Order Delivery');
                break;
            case '4' : return $this->__('Send stock to Supplier for Return Order');
                break;
            case '5' : return $this->__('Send stock to Customer for Shipment');
                break;
            case '6' : return $this->__('Receive stock from Customer Refund');
                break;
        }
    }

    /**
     * Get reports by type
     * 
     * @param string $type
     * @return array
     */
    public function getReports($type = null) {
        $reports = array(
            'sales' => array(
                'title' => $this->__('Sales Reports'),
                'description' => $this->__('Evaluate your sales history from different aspects, such as best-selling times, payments, shipments, warehouses, suppliers and more.'),
                'reports' => array(
                    'sales_days' => array(
                        'code' => 'sales_days',
                        'title' => 'Day',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'days_of_month' => array(
                        'code' => 'days_of_month',
                        'title' => 'Day of month',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'days_of_week' => array(
                        'code' => 'days_of_week',
                        'title' => 'Day of Week',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'hours_of_day' => array(
                        'code' => 'hours_of_day',
                        'title' => 'Hour of day',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'shipping_method' => array(
                        'code' => 'shipping_method',
                        'title' => 'Shipping Method',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'payment_method' => array(
                        'code' => 'payment_method',
                        'title' => 'Payment Method',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'sales_sku' => array(
                        'code' => 'sales_sku',
                        'title' => 'SKU',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'status' => array(
                        'code' => 'status',
                        'title' => 'Order Status',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'sales_warehouse' => array(
                        'code' => 'sales_warehouse',
                        'title' => 'Warehouse',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                    'sales_supplier' => array(
                        'code' => 'sales_supplier',
                        'title' => 'Supplier',
                        'default_time_range' => 'last_30_days',
                        'order_status' => Mage_Sales_Model_Order::STATE_COMPLETE,
                    ),
                ),
            ),
            /*
              'warehouse' => array(
              'title' => $this->__('Warehouse Reports'),
              'description' => $this->__('View reports on your warehouse status such as: revenue, stock on-hand and supply needs. '),
              'reports' => array(
              'total_qty_adjuststock' => array(
              'code' => 'total_qty_adjuststock',
              'title' => 'Total Qty Adjusted',
              'default_time_range' => 'last_30_days',
              'sort' => 1,
              ),
              'number_of_product_adjuststock' => array(
              'code' => 'number_of_product_adjuststock',
              'title' => 'Product Adjusted',
              'default_time_range' => 'last_30_days',
              'sort' => 2,
              ),
              'total_order_by_warehouse' => array(
              'code' => 'total_order_by_warehouse',
              'title' => 'Sales Orders',
              'default_time_range' => 'last_30_days',
              'sort' => 3,
              ),
              'sales_by_warehouse_revenue' => array(
              'code' => 'sales_by_warehouse_revenue',
              'title' => 'Revenue',
              'default_time_range' => 'last_30_days',
              'sort' => 4,
              ),
              'sales_by_warehouse_item_shipped' => array(
              'code' => 'sales_by_warehouse_item_shipped',
              'title' => 'Item Shipped',
              'default_time_range' => 'last_30_days',
              'sort' => 5,
              ),
              'total_stock_transfer_send_stock' => array(
              'code' => 'total_stock_transfer_send_stock',
              'title' => 'Stock Sending',
              'default_time_range' => 'last_30_days',
              'sort' => 6,
              ),
              'total_stock_transfer_request_stock' => array(
              'code' => 'total_stock_transfer_request_stock',
              'title' => 'Stock Request',
              'default_time_range' => 'last_30_days',
              'sort' => 7,
              ),
              'supply_needs_by_warehouse_products' => array(
              'code' => 'supply_needs_by_warehouse_products',
              'title' => 'Supply Needs',
              'default_time_range' => 'next_30_days',
              'sort' => 8,
              ),
              'total_stock_different_when_physical_stocktaking_by_warehouse' => array(
              'code' => 'total_stock_different_when_physical_stocktaking_by_warehouse',
              'title' => 'Stocktaking Variance',
              'default_time_range' => 'last_30_days',
              'sort' => 9,
              ),
              ),
              ),
             */
            'customer' => array(
                'title' => $this->__('Customer Reports'),
                'description' => $this->__('Identify your best customers with total purchased orders & values.'),
                'reports' => array(
                    'customer' => array(
                        'code' => 'customer',
                        'title' => 'Customer Purchasing',
                        'default_time_range' => 'last_30_days',
                        'sort' => 1,
                    ),
                ),
            ),
            'purchaseorder' => array(
                'title' => $this->__('Purchase Report'),
                'description' => $this->__('View your purchase order history and rank your top suppliers, purchased warehouses & products.'),
                'reports' => array(
                    'po_supplier' => array(
                        'code' => 'po_supplier',
                        'title' => 'Supplier',
                        'default_time_range' => 'last_30_days',
                        'sort' => 1,
                    ),
                    'po_warehouse' => array(
                        'code' => 'po_warehouse',
                        'title' => 'Warehouse',
                        'default_time_range' => 'last_30_days',
                        'sort' => 1,
                    ),
                    'po_sku' => array(
                        'code' => 'po_sku',
                        'title' => 'SKU',
                        'default_time_range' => 'last_30_days',
                        'sort' => 1,
                    ),
                ),
            ),
            'stockonhand' => array(
                'title' => $this->__('Stock On-Hand Report'),
                'description' => $this->__('Follow current inventory level of products based on warehouse or supplier.'),
                'reports' => array(
                    'stockonhand' => array(
                        'code' => 'stockonhand',
                        'title' => 'Stock On-Hand',
                        'default_time_range' => 'last_30_days',
                        'sort' => 1,
                    )
                ),
            ),            
            'stockmovement' => array(
                'title' => $this->__('Stock Movement Reports'),
                'description' => $this->__('Provide the historical records of all inventory audit trails in each warehouse, including inward and outward inventory.'),
                'reports' => array(
                    'stock_in' => array(
                        'code' => 'stock_in',
                        'title' => 'Stock Received',
                        'default_time_range' => 'last_30_days',
                        'sort' => 1,
                    ),
                    'most_stock_remain' => array(
                        'code' => 'stock_out',
                        'title' => 'Stock Issued',
                        'default_time_range' => 'last_30_days',
                        'sort' => 2,
                    ),
                ),
            ),
        );

        return $type ? (isset($reports[$type]) ? $reports[$type] : null) : $reports;
    }

    //get all supplier name
    public function getAllSupplierName() {
        $suppliers = array();
        $model = Mage::getModel('inventorypurchasing/supplier');
        $collection = $model->getCollection();
        foreach ($collection as $supplier) {
            $suppliers[$supplier->getId()] = $supplier->getSupplierName();
        }
        return $suppliers;
    }

    //get all warehouse name
    public function getAllWarehouseName() {
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection();
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        return $warehouses;
    }

    //get time inventory for each product
    public function getTimeInventory($item, $filterData) {
        $time = '';
        $count = 0;
        $totalTime = 0;
        $now = time(); // or your date as well

        $block = new Magestore_Inventoryreports_Block_Adminhtml_Supplier_Product_Grid;
        $filter = $block->getParam($block->getVarNameFilter(), null);
        $condorder = '';
        if ($filter) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
                if ($value == 'supplier_id') {
                    $condorder = $key;
                }
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorybarcode')) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $results = '';
            $purchaseOrderIds = array();
            if ($condorder) {
                $sql = 'SELECT distinct(`purchaseorder_purchase_order_id`) FROM ' . $resource->getTableName('inventorybarcode/barcode') . ' where (`product_entity_id` = ' . $item->getId() .
                        ') and (`supplier_supplier_id` = ' . $condorder . ') and (`qty` > ' . 0 . ')';
            } else {
                $sql = 'SELECT distinct(`purchaseorder_purchase_order_id`) FROM ' . $resource->getTableName('inventorybarcode/barcode') . ' where (`product_entity_id` = ' . $item->getId() .
                        ') and (`qty` > ' . 0 . ')';
            }
            $results = $readConnection->query($sql);
            if ($results) {
                foreach ($results as $result) {
                    $purchaseOrderIds[] = $result['purchaseorder_purchase_order_id'];
                }
            }
            $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                    ->getCollection()
                    ->addFieldToFilter('purchase_order_id', array('in' => $purchaseOrderIds));
            $count += $purchaseOrders->getSize();
            $notPurchases = Mage::getModel('inventorybarcode/barcode')
                    ->getCollection()
                    ->addFieldToFilter('purchaseorder_purchase_order_id', '')
                    ->addFieldToFilter('qty', array('gt' => 0));
            $count += $notPurchases->getSize();
            foreach ($purchaseOrders as $purchaseOrder) {
                $your_date = strtotime($purchaseOrder->getPurchaseOn());
                $datediff = $now - $your_date;
                $totalTime += floor($datediff / (60 * 60 * 24));
                $time = 1;
            }

            if ($time == '') {
                return '';
            }
            $time = round($totalTime / $count, 1);
            return $time;
        }
        $deliveries = Mage::getModel('inventorypurchasing/purchaseorder_delivery')
                ->getCollection()
                ->addFieldToFilter('product_id', $row->getId());
        foreach ($deliveries as $delivery) {
            $count++;
            $your_date = strtotime($delivery->getDeliveryDate());
            $datediff = $now - $your_date;
            $time = 1;
            $totalTime += floor($datediff / (60 * 60 * 24));
        }
        if ($time == '') {
            return 'N/A';
        }
        $time = round($totalTime / $count, 1);
        return $time;
    }

    //reset collection
    public function _tempCollection() {
        $collection = new Varien_Data_Collection();
        return $collection;
    }

    //get days of week
    public function getDaysOfWeek() {
        return array(
            '1' => $this->__('Sunday'),
            '2' => $this->__('Monday'),
            '3' => $this->__('Tuesday'),
            '4' => $this->__('Wednesday'),
            '5' => $this->__('Thusday'),
            '6' => $this->__('Friday'),
            '7' => $this->__('Saturday'),
        );
    }

    //get period for reports
    public function getPeriodOptions() {
        $options = array();
        $options = array(
            '1' => $this->__('Day'),
            '2' => $this->__('Month'),
            '3' => $this->__('Year'),
        );
        return $options;
    }

    public function checkDisplay($report_radio_select) {
        if ($report_radio_select == 'warehousing_time_longest') {
            return 1;
        }
        if ($report_radio_select == 'total_stock_different_when_physical_stocktaking_by_warehouse') {
            return 2;
        } else {
            return 1;
        }
    }

    private function getQuarterTime($current_month) {
        if ($current_month >= 1 && $current_month <= 3) {
            $date_from = new DateTime('first day of January');
            $from = $date_from->format('Y-m-d') . ' 00:00:00';
            $date_to = new DateTime('last day of March');
            $to = $date_to->format('Y-m-d') . ' 23:59:59';
        } else if ($current_month >= 4 && $current_month <= 6) {
            $date_from = new DateTime('first day of April');
            $from = $date_from->format('Y-m-d') . ' 00:00:00';
            $date_to = new DateTime('last day of June');
            $to = $date_to->format('Y-m-d') . ' 23:59:59';
        } else if ($current_month >= 7 && $current_month <= 9) {
            $date_from = new DateTime('first day of July');
            $from = $date_from->format('Y-m-d') . ' 00:00:00';
            $date_to = new DateTime('last day of September');
            $to = $date_to->format('Y-m-d') . ' 23:59:59';
        } else if ($current_month >= 10 && $current_month <= 12) {
            $date_from = new DateTime('first day of October');
            $from = $date_from->format('Y-m-d') . ' 00:00:00';
            $date_to = new DateTime('last day of December');
            $to = $date_to->format('Y-m-d') . ' 23:59:59';
        } else { // If current_month is wrong, time is today
            $date_from = new DateTime();
            $from = $date_from->format('Y-m-d') . ' 00:00:00';
            $date_to = new DateTime();
            $to = $date_to->format('Y-m-d') . ' 23:59:59';
        }
        $result = array(
            'from' => $from,
            'to' => $to
        );
        return $result;
    }
    
    private function getYearTime() {
        $date_from = new DateTime('first day of January');
        $from = $date_from->format('Y-m-d') . ' 00:00:00';
        $date_to = new DateTime('last day of December');
        $to = $date_to->format('Y-m-d') . ' 23:59:59';
        $result = array(
            'from' => $from,
            'to' => $to
        );
        return $result;
    }

    public function getTimeSelected($time_request) {
        $result_time = array();
        $number = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $first_day = date('Y-m-01 00:00:00');
        $last_day = date('Y-m-' . $number . ' 23:59:59', time());
        $start_day = date('Y-m-d 00:00:00', time());
        $end_day = date('Y-m-d 23:59:59');
        $current_month = date('m');
        if (isset($time_request['select_time'])) {
            $select_time = $time_request['select_time'];
            switch ($select_time) {
                default:
                    $time = 30;
                    $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
                    $to = $end_day;
                    break;
                case "last_7_days":
                    $time = 7;
                    $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
                    $to = $end_day;
                    break;
                case "last_30_days":
                    $time = 30;
                    $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
                    $to = $end_day;
                    break;
                case "next_7_days":
                    $time = 7;
                    $from = $start_day;
                    $to = strftime('%Y-%m-%d 23:59:59', strtotime(date("Y-m-d", strtotime($start_day)) . " +$time day"));
                    break;
                case "next_30_days":
                    $time = 30;
                    $from = $start_day;
                    $to = strftime('%Y-%m-%d 23:59:59', strtotime(date("Y-m-d", strtotime($start_day)) . " +$time day"));
                    break;
                case "this_week":
                    $day = date('w');
                    $week_start = date('Y-m-d 00:00:00', strtotime('-' . $day . ' days'));
                    $week_end = date('Y-m-d 23:59:59', strtotime('+' . (6 - $day) . ' days'));
                    $from = $week_start;
                    $to = $week_end;
                    break;
                case "this_month":
                    $from = $first_day;
                    $to = $last_day;
                    break;  
                case "this_quarter":
                    $thisQuarter = $this->getQuarterTime($current_month);
                    $from = $thisQuarter['from'];
                    $to = $thisQuarter['to'];
                    break;
                case "this_year":
                    $thisYear = $this->getYearTime();
                    $from = $thisYear['from'];
                    $to = $thisYear['to'];
                    break;
                case "range":
                    $from = $time_request['date_from'] . ' 00:00:00';
                    $to = $time_request['date_to'] . ' 23:59:59';
                    break;
            }
        } else {
            $time = 30;
            $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
            $to = $end_day;
        }
        $result_time['date_from'] = $from;
        $result_time['date_to'] = $to;
        return $result_time;
    }

    /**
     * Get header text of report
     * 
     * @return string
     */
    public function getHeaderText() {
        $requestData = Mage::helper('adminhtml')->prepareFilterString(Mage::app()->getRequest()->getParam('top_filter'));
        $reportcode = isset($requestData['report_radio_select']) ? $requestData['report_radio_select'] : null;
        $type_id = Mage::app()->getRequest()->getParam('type_id');
        switch ($reportcode) {
            case 'sales_days':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Day');
            case 'days_of_month':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Day of Month');
            case 'hours_of_day':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Hour Of Day');
            case 'days_of_week':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Day Of Week');
            case 'shipping_method':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Shipping Method');
            case 'payment_method':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Payment Method');
            case 'tax_code':
                return $this->__(ucwords($type_id) . ' Report by ' . 'Tax');
            case 'status':
                return $this->__(ucwords($type_id) . ' Report by ' . ' Order Status');
            case 'sales_sku':
                return $this->__('Sales Report by SKU');
            case 'sales_supplier':
                return $this->__('Sales Report by Supplier');
            case 'sales_warehouse':
                return $this->__('Sales Report by Warehouse');                
            case 'total_qty_adjuststock':
                return $this->__('Report on Total Adjusted Qty by Warehouse');
            case 'number_of_product_adjuststock':
                return $this->__('Report on Number of Products Being Adjusted Qty by Warehouse');
            case 'total_order_by_warehouse':
                return $this->__('Report on Total Sales Orders by Warehouse');
            case 'sales_by_warehouse_revenue':
                return $this->__('Warehouse Revenue Report');
            case 'sales_by_warehouse_item_shipped':
                return $this->__('Report on Number of Items Shipped by Warehouse');
            case 'total_stock_transfer_send_stock':
                return $this->__('Report on Total Qty Sent by Warehouse');
            case 'total_stock_transfer_request_stock':
                return $this->__('Report on Total Qty Requested by Warehouse');
            case 'supply_needs_by_warehouse_products':
                return $this->__('Report on Supply Needs by Warehouse');
            case 'total_stock_different_when_physical_stocktaking_by_warehouse':
                return $this->__('Report on Stocktake Qty Variances by Warehouse');
            case 'best_seller':
                return $this->__('Bestseller Report');
            case 'most_stock_remain':
                return $this->__('Stock On-Hand Report');
            case 'warehousing_time_longest':
                return $this->__('Stock On-Hand Report');
            case 'purchase_order_to_supplier':
                return $this->__('Report on Total Qty Purchased by Supplier');
            case 'stock_in':
                return $this->__('Report on Stock Received');
            case 'stock_out':
                return $this->__('Report on Stock Issued');
            case 'customer':
                return $this->__('Customer Report');
            case 'po_supplier':
                return $this->__('Purchase Report by Supplier');  
            case 'po_warehouse':
                return $this->__('Purchase Report by Warehouse');   
            case 'po_sku':
                return $this->__('Purchase Report by SKU');                   
            default :
                if (strcmp($type_id, 'stockonhand') == 0) {
                    return $this->__('Stock On-Hand Report');
                }
                break;
        }
    }

    public function getSupplierName($id) {
        return Mage::getModel('inventorypurchasing/supplier')->load($id)->getSupplierName();
    }

    public function getSupplierReportCollection($requestData) {
        if ($requestData['select_time']) {
            $gettime = $this->getTimeSelected($requestData);
            $purchase_ids = array();
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $installer = Mage::getModel('core/resource');
            $sql = 'SELECT distinct(`purchase_order_id`) from ' . $installer->getTableName("erp_inventory_purchase_order") . ' WHERE (purchase_on BETWEEN "' . $gettime['date_from'] . '" and "' . $gettime['date_to'] . '")';
            $results = $readConnection->fetchAll($sql);
            foreach ($results as $result) {
                $purchase_ids[] = $result['purchase_order_id'];
            }
            $ids = join(',', $purchase_ids);
            if (isset($requestData['supplier_select']) && $supplierId = $requestData['supplier_select']) {
                $productAttribute = $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');
                $resource = Mage::getSingleton('core/resource');
                $collection = Mage::getModel('inventorypurchasing/supplier_product')
                        ->getCollection()
                        ->addFieldToFilter('supplier_id', $supplierId);
                if ($ids) {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.product_id=barcode.product_entity_id and barcode.qty > 0 and barcode.supplier_supplier_id = ' . $supplierId . ' and barcode.purchaseorder_purchase_order_id IN (' . $ids . ')', //.' and barcode.purchaseorder_purchase_order_id IN '.$stringpurchase
                                    array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->joinLeft(array('product_attribute' => $resource->getTableName('catalog_product_entity_' . $productAttribute->getData('backend_type'))), 'main_table.product_id=product_attribute.entity_id and product_attribute.attribute_id = ' . $productAttribute->getData('attribute_id'), array('product_name' => 'product_attribute.value')
                            )
                            ->columns(array(
                                'all_purchase_order_id' => 'GROUP_CONCAT(DISTINCT barcode.purchaseorder_purchase_order_id SEPARATOR ",")'
                            ))
                            ->group(array('main_table.product_id'));
                } else {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.product_id=barcode.product_entity_id and barcode.qty > 0 and barcode.supplier_supplier_id = ' . $supplierId . ' and barcode.purchaseorder_purchase_order_id IN (0)', //.' and barcode.purchaseorder_purchase_order_id IN '.$stringpurchase
                                    array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->joinLeft(array('product_attribute' => $resource->getTableName('catalog_product_entity_' . $productAttribute->getData('backend_type'))), 'main_table.product_id=product_attribute.entity_id and product_attribute.attribute_id = ' . $productAttribute->getData('attribute_id'), array('product_name' => 'product_attribute.value')
                            )
                            ->columns(array(
                                'all_purchase_order_id' => 'GROUP_CONCAT(DISTINCT barcode.purchaseorder_purchase_order_id SEPARATOR ",")'
                            ))
                            ->group(array('main_table.product_id'));
                }
                $collection->setIsGroupCountSql(true);
            } else {
                $collection = Mage::getModel('inventorypurchasing/supplier')
                        ->getCollection();
                if (!empty($ids)) {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.supplier_id=barcode.supplier_supplier_id and barcode.qty > 0 and barcode.purchaseorder_purchase_order_id IN (' . $ids . ')', array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->columns(array(
                                'all_purchase_order_id' => 'GROUP_CONCAT(DISTINCT barcode.purchaseorder_purchase_order_id SEPARATOR ",")'
                            ))
                            ->group(array('main_table.supplier_id'));
                }
                if (empty($ids)) {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.supplier_id=barcode.supplier_supplier_id and barcode.qty > 0 and barcode.purchaseorder_purchase_order_id IN (0)', array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->columns(array(
                                'all_purchase_order_id' => 'GROUP_CONCAT(DISTINCT barcode.purchaseorder_purchase_order_id SEPARATOR ",")'
                            ))
                            ->group(array('main_table.supplier_id'));
                }
            }
        }
        //$collection->addFieldToFilter('total_inventory',array('gt'=>0));
        return $collection;
    }

    public function getDefaultOptionsWarehouse() {
        $options = array();
        $options["select_time"] = "last_30_days";
        $options["report_radio_select"] = "total_qty_adjuststock";
        $options["select_warehouse"] = "0";
        return $options;
    }

    public function getDefaultOptionsSupplier() {
        $options = array();
        $options["select_time"] = "last_30_days";
        $options["report_radio_select"] = "purchase_order_to_supplier";
        $options["select_warehouse"] = "0";
        return $options;
    }

    public function getWarehouseName($id) {
        return Mage::getModel('inventoryplus/warehouse')->load($id)->getWarehouseName();
    }

    public function checkProductInWarehouse($data, $warehouse_id) {
        $product_ids = array();
        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToFilter('warehouse_id', $warehouse_id);
        $collection->addFieldToFilter('product_id', array('in' => $data));
        foreach ($collection as $value) {
            $product_ids[] = $value->getProductId();
        }
        return $product_ids;
    }

    public function checkNullDataChart($series) {
        $seriesCheckNull = explode(',', $series);
        $seriesCheckNull = array_filter($seriesCheckNull);
        $newSeries = implode(',', $seriesCheckNull);
        return $newSeries;
    }

    public function isDisplayChartOf($reportcode) {
        $disableArr = array('total_stock_different_when_physical_stocktaking_by_warehouse');
        if (in_array($reportcode, $disableArr))
            return false;
        else
            return true;
    }
    
    /**
     * Get requested time range
     * 
     * @param array $requestData
     * @return array
     */
    public function getTimeRange($requestData) {
        $time_request = isset($requestData['select_time']) ? $requestData['select_time'] : 'last_7_days';
        $datefrom = '';
        $dateto = '';
        //get time range
        if ($time_request == 'range') {
            if (isset($requestData['date_from'])) {
                $datefrom = $requestData['date_from'];
            } else {
                $now = now();
                $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
            }
            if (isset($requestData['date_to'])) {
                $dateto = $requestData['date_to'];
            } else {
                $now = now();
                $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
            }
            $datefrom = $datefrom . ' 00:00:00';
            $dateto = $dateto . ' 23:59:59';
        } else {
            $time_range = $this->getTimeSelected($requestData);
            if (isset($time_range['date_from']) && isset($time_range['date_to'])) {
                $datefrom = $time_range['date_from'];
                $dateto = $time_range['date_to'];
            }
        }
        return array('from' => $datefrom, 'to' => $dateto);
    }    

}
