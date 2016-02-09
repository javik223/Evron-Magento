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
class Magestore_Inventoryreports_Helper_Order extends Mage_Core_Helper_Abstract {

    public function getTimezoneOffset($timezone) {
        //get offset for site timezone
        $time = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone($timezone));
        $timezoneOffset = $time->format('P');
        return $timezoneOffset;
    }

    /**
     * Get requested time range
     * 
     * @param array $requestData
     * @return array
     */
    public function getTimeRange($requestData) {
        return Mage::helper('inventoryreports')->getTimeRange($requestData);
    }

    public function filterWithSupplier($supplier) {
        $arrayCollection = array();
        //filter with supplier
        $orderItems = Mage::getModel('sales/order_item')->getCollection()
                ->addFieldToFilter('parent_item_id', array('null' => true))
                ->addFieldToFilter('product_type', array('nin' => array("configurable", "bundle", "grouped")))
        ;
        $orderItems->getSelect()
                ->join(array(
                    'supplierproduct' => $orderItems->getTable('inventorypurchasing/supplier_product')), 'main_table.product_id = supplierproduct.product_id and supplierproduct.supplier_id=' . $supplier, array("*")
                )
                ->group('order_id');
        $orderIdsSupplier = array();
        foreach ($orderItems as $orderItem) {
            $orderIdsSupplier[$orderItem->getOrderId()] = $orderItem->getOrderId();
        }
        return $orderIdsSupplier;
    }

    /**
     * Get order collection resource
     * 
     * @return \Magestore_Inventoryreports_Model_Mysql4_Sales_Order_Collection
     */
    public function getOrderCollection() {
        return Mage::getResourceModel('inventoryreports/sales_order_collection');
    }

    /**
     * Get order item collection resource
     * 
     * @return \Magestore_Inventoryreports_Model_Mysql4_Sales_Order_Item_Collection
     */
    public function getOrderItemCollection() {
        return Mage::getResourceModel('inventoryreports/sales_order_item_collection');
    }

    public function getHoursofdayReportCollection($datefrom, $dateto, $supplier, $source) {
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $collection = $this->getOrderCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        if ($supplier) {
            $orderIds = $this->filterWithSupplier($supplier);
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        }
        if ($source) {
            $collection->getSelect()
                    ->joinLeft(
                            array('source' => $collection->getTable('webpos/survey')), 'main_table.entity_id = source.order_id', array('*')
                    )
                    ->where("source.value='" . $source . "'")
            ;
        }
        $toTimezone = Mage::getSingleton('core/date')->getGmtOffset('hours') . ':00';
        if (Mage::getSingleton('core/date')->getGmtOffset('hours') >= 0)
            $toTimezone = '+' . $toTimezone;
        $fromTimezone = '+7:00';
        $fromTimezone = (date("Z") >= 0) ? '+' . date("Z") . ':00' : date("Z") . ':00';

        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")'),
            'time_range' => new Zend_Db_Expr("hour(CONVERT_TZ(main_table.created_at,'$fromTimezone','$toTimezone'))"),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
            'sum_base_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_tax_amount),0)'),
            'sum_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.tax_amount),0)'),
            'sum_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.subtotal),0)'),
            'sum_base_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_subtotal),0)'),
            'grand_total_per_hour' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'
            . ' / DATEDIFF(\'' . $dateto . '\', \'' . $datefrom . '\' )'),
            'base_grand_total_per_hour' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'
            . ' / DATEDIFF(\'' . $dateto . '\', \'' . $datefrom . '\' )'),
            'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'),
            'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_qty_ordered),0)'),
            'qty_ordered_per_hour' => new Zend_Db_Expr('ROUND(IFNULL(SUM(main_table.total_qty_ordered),0)'
            . ' / DATEDIFF(\'' . $dateto . '\', \'' . $datefrom . '\' ), 2)'),
            'total_order_per_hour' => new Zend_Db_Expr('ROUND(COUNT(DISTINCT main_table.entity_id)'
            . ' / DATEDIFF(\'' . $dateto . '\', \'' . $datefrom . '\' ), 2)'),
        ));

        $collection->getSelect()->group(new Zend_Db_Expr("hour(CONVERT_TZ(main_table.created_at,'$fromTimezone','$toTimezone'))"));
        //$collection->setOrder(("hour(CONVERT_TZ(main_table.created_at,'$fromTimezone','$toTimezone'))"), 'ASC');
        $collection->setOrder(("hour(CONVERT_TZ(main_table.created_at,'$fromTimezone','$toTimezone'))"), 'ASC');
        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
            'count_item_id' => 'orderitem',
        );
        return $arrayCollection;
    }

    public function getDaysofweekReportCollection($datefrom, $dateto, $supplier, $source) {
        $arrayCollection = array();
        //get timezone
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $defaulttimezone = date_default_timezone_get();
        //get offset
        $timezoneoffset = $this->getTimezoneOffset($timezone);
        $defaulttimezoneoffset = $this->getTimezoneOffset($defaulttimezone);
        $collection = $this->getOrderCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        if ($supplier) {
            $orderIds = $this->filterWithSupplier($supplier);
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        }
        if ($source) {
            $collection->getSelect()
                    ->joinLeft(
                            array('source' => $collection->getTable('webpos/survey')), 'main_table.entity_id = source.order_id', array('*')
                    )
                    ->where("source.value='" . $source . "'")
            ;
        }
        $totalWeek = 'TIMESTAMPDIFF(WEEK, \'' . $datefrom . '\', \'' . $dateto . '\' )';
        $collection->getSelect()->group(new Zend_Db_Expr("dayofweek(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"));
        $collection->setOrder(("dayofweek(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"), 'ASC');
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")'),
            'time_range' => new Zend_Db_Expr("dayofweek(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
            'sum_base_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_tax_amount),0)'),
            'sum_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.tax_amount),0)'),
            'sum_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.subtotal),0)'),
            'sum_base_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_subtotal),0)'),
            'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'),
            'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_qty_ordered),0)'),
            'base_grand_total_per_day' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'
            . " / IF($totalWeek > 0, $totalWeek, 1)"),
            'qty_ordered_per_day' => new Zend_Db_Expr('ROUND(IFNULL(SUM(main_table.total_qty_ordered),0)'
            . " / IF($totalWeek > 0, $totalWeek, 1), 2)"),
            'total_order_per_day' => new Zend_Db_Expr('ROUND(COUNT(DISTINCT main_table.entity_id)'
            . " / IF($totalWeek > 0, $totalWeek, 1), 2)"),
        ));

        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
            'count_item_id' => 'orderitem',
        );
        return $arrayCollection;
    }

    public function getDaysofmonthReportCollection($datefrom, $dateto, $supplier, $group, $source) {
        $arrayCollection = array();
        //get timezone
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $defaulttimezone = date_default_timezone_get();
        //get offset
        $timezoneoffset = $this->getTimezoneOffset($timezone);
        $defaulttimezoneoffset = $this->getTimezoneOffset($defaulttimezone);
        $collection = $this->getOrderCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        if ($supplier) {
            $orderIds = $this->filterWithSupplier($supplier);
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        }
        if ($source) {
            $collection->getSelect()
                    ->joinLeft(
                            array('source' => $collection->getTable('webpos/survey')), 'main_table.entity_id = source.order_id', array('*')
                    )
                    ->where("source.value='" . $source . "'")
            ;
        }
        if ($group) {
            $totalMonth = 'TIMESTAMPDIFF(MONTH, \'' . $datefrom . '\', \'' . $dateto . '\' )';

            $collection->getSelect()->group(new Zend_Db_Expr("dayofmonth(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"));
            $collection->setOrder(("dayofmonth(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"), 'ASC');
            $collection->getSelect()->columns(array(
                'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")'),
                'time_range' => new Zend_Db_Expr("dayofmonth(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"),
                'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
                'sum_base_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_tax_amount),0)'),
                'sum_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.tax_amount),0)'),
                'sum_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.subtotal),0)'),
                'sum_base_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_subtotal),0)'),
                'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'),
                'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'),
                'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_qty_ordered),0)'),
                'total_order_per_day' => new Zend_Db_Expr('ROUND(COUNT(DISTINCT main_table.entity_id)'
                . " / IF($totalMonth > 0, $totalMonth, 1), 2)"),
                'qty_ordered_per_day' => new Zend_Db_Expr('ROUND(IFNULL(SUM(main_table.total_qty_ordered),0)'
                . " / IF($totalMonth > 0, $totalMonth, 1), 2)"),
                'base_grand_total_per_day' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'
                . " / IF($totalMonth > 0, $totalMonth, 1)"),
            ));
        } else {
            $collection->getSelect()->group(new Zend_Db_Expr("DATE(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"));
            $collection->setOrder(("DATE(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"), 'ASC');
            $collection->getSelect()->columns(array(
                'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")'),
                'time_range' => new Zend_Db_Expr("DATE(CONVERT_TZ(main_table.created_at,'$defaulttimezoneoffset','$timezoneoffset'))"),
                'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
                'sum_base_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_tax_amount),0)'),
                'sum_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.tax_amount),0)'),
                'sum_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.subtotal),0)'),
                'sum_base_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_subtotal),0)'),
                'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'),
                'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'),
                'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_qty_ordered),0)')
            ));
        }
        $arrayCollection['collection'] = $collection;
        
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
            'count_item_id' => 'orderitem',
        );
        return $arrayCollection;
    }

    public function getInvoiceReportCollection($datefrom, $dateto, $supplier, $source) {
        $collection = $this->getOrderCollection();
        $collection
                ->addFieldToFilter('main_table.created_at', array(
                    'from' => $datefrom,
                    'to' => $dateto,
                    'date' => true,
                ))
                ->addFieldToFilter('base_subtotal_invoiced', array('gt' => 0));

        if ($supplier) {
            $orderIds = $this->filterWithSupplier($supplier);
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        }
        if ($source) {
            $collection->getSelect()
                    ->joinLeft(
                            array('source' => $collection->getTable('webpos/survey')), 'main_table.entity_id = source.order_id', array('*')
                    )
                    ->where("source.value='" . $source . "'")
            ;
        }
        $collection->getSelect()->joinLeft(array(
            'orderinvoice' => $collection->getTable('sales/invoice')), 'main_table.entity_id = orderinvoice.order_id', array('count_invoice_id' => 'IFNULL(COUNT( DISTINCT orderinvoice.entity_id),0)')
        );

        $collection->getSelect()->joinLeft(array(
            'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id AND orderitem.parent_item_id IS NULL', array('orderitem.item_id')
        );

        $collection->getSelect()->joinLeft(array(
            'invoiceitem' => $collection->getTable('sales/invoice_item')), 'orderinvoice.entity_id = invoiceitem.parent_id AND orderitem.item_id = invoiceitem.order_item_id', array('sum_invoice_item_qty' => 'IFNULL(SUM(invoiceitem.qty),0)')
        );

        $collection->getSelect()->group('main_table.entity_id');

        $collection->getSelect()->columns(array(
            'all_order_id' => 'GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")',
            'order_id' => 'main_table.increment_id',
            'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
            'sum_base_tax_amount_invoiced' => 'IFNULL(SUM(main_table.base_tax_invoiced),0)',
            'sum_tax_amount_invoiced' => 'IFNULL(SUM(main_table.tax_invoiced),0)',
            'sum_subtotal_invoiced' => 'IFNULL(SUM(main_table.subtotal_invoiced),0)',
            'sum_base_subtotal_invoiced' => 'IFNULL(SUM(main_table.base_subtotal_invoiced),0)',
            'sum_grand_total_invoiced' => 'IFNULL(SUM(main_table.total_invoiced),0)',
            'sum_base_grand_total_invoiced' => 'IFNULL(SUM(main_table.base_total_invoiced),0)',
            'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
            'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
            'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)',
            'sum_qty_ordered' => 'IFNULL(SUM(main_table.total_qty_ordered),0)'
        ));

        return $collection;
    }

    public function getCreditmemoReportCollection($datefrom, $dateto, $supplier, $source) {
        $collection = $this->getOrderCollection();
        $collection
                ->addFieldToFilter('main_table.created_at', array(
                    'from' => $datefrom,
                    'to' => $dateto,
                    'date' => true,
                ))
                ->addFieldToFilter('base_subtotal_refunded', array('gt' => 0));
        if ($supplier) {
            $orderIds = $this->filterWithSupplier($supplier);
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        }
        if ($source) {
            $collection->getSelect()
                    ->joinLeft(
                            array('source' => $collection->getTable('webpos/survey')), 'main_table.entity_id = source.order_id', array('*')
                    )
                    ->where("source.value='" . $source . "'")
            ;
        }
        $collection->getSelect()->joinLeft(array(
            'ordercreditmemo' => $collection->getTable('sales/creditmemo')), 'main_table.entity_id = ordercreditmemo.order_id', array('count_creditmemo_id' => 'IFNULL(COUNT( DISTINCT ordercreditmemo.entity_id),0)')
        );

        $collection->getSelect()->joinLeft(array(
            'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id AND orderitem.parent_item_id IS NULL', array('orderitem.item_id')
        );

        $collection->getSelect()->joinLeft(array(
            'creditmemoitem' => $collection->getTable('sales/creditmemo_item')), 'ordercreditmemo.entity_id = creditmemoitem.parent_id AND orderitem.item_id = creditmemoitem.order_item_id', array('sum_creditmemo_item_qty' => 'IFNULL(SUM(creditmemoitem.qty),0)')
        );

        $collection->getSelect()->group('main_table.entity_id');

        $collection->getSelect()->columns(array(
            'all_order_id' => 'GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")',
            'order_id' => 'main_table.increment_id',
            'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
            'sum_base_tax_amount_refunded' => 'IFNULL(SUM(main_table.base_tax_refunded),0)',
            'sum_tax_amount_refunded' => 'IFNULL(SUM(main_table.tax_refunded),0)',
            'sum_subtotal_refunded' => 'IFNULL(SUM(main_table.subtotal_refunded),0)',
            'sum_base_subtotal_refunded' => 'IFNULL(SUM(main_table.base_subtotal_refunded),0)',
            'sum_grand_total_refunded' => 'IFNULL(SUM(main_table.total_refunded),0)',
            'sum_base_grand_total_refunded' => 'IFNULL(SUM(main_table.base_total_refunded),0)',
            'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
            'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
            'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)',
            'sum_qty_ordered' => 'IFNULL(SUM(main_table.total_qty_ordered),0)'
        ));
        return $collection;
    }

    /**
     * Get sales by warehouses
     * 
     * @param string $datefrom
     * @param string $dateto
     * @param string $source
     */
    public function getSalesWarehouseReportCollection1($datefrom, $dateto, $source) {
        $collection = $this->getOrderCollection();
        $collection->getSelect()->join(
                array('orderItem' => $collection->getTable('sales/order_item')), 'main_table.entity_id= orderItem.order_id', array('base_row_total_incl_tax', 'row_total_incl_tax', 'qty_ordered')
        ); 
        $collection->getSelect()->joinLeft(
                array('productSuper' => $collection->getTable('catalog/product_super_link')), 'orderItem.product_id= productSuper.parent_id', array('product_id')
        );              
        $collection->getSelect()->joinLeft(
                array('warehouseShip' => $collection->getTable('inventoryplus/warehouse_shipment')), 
                'IFNULL(productSuper.product_id, orderItem.product_id) = warehouseShip.product_id '
                . ' AND orderItem.order_id = warehouseShip.order_id', 
                array('qty_shipped', 'qty_refunded', 'warehouse_name')
        );
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        $collection->getSelect()->where('`warehouseShip`.`warehouse_id` is NULL');
        //$collection->getSelect()->group("warehouseShip.warehouse_id");
        //$collection->setOrder(("IFNULL(SUM(warehouseShip.subtotal_shipped),0)"), 'DESC');
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT `main_table`.`entity_id` SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
            'sum_base_tax_amount' => '0',
            'sum_tax_amount' => '0',
            'sum_grand_total' => new Zend_Db_Expr('IFNULL( SUM( orderItem.row_total_incl_tax * (warehouseShip.qty_shipped - warehouseShip.qty_refunded) / orderItem.qty_ordered ),'
            . ' SUM(orderItem.row_total_incl_tax))'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL( SUM( orderItem.base_row_total_incl_tax * (warehouseShip.qty_shipped - warehouseShip.qty_refunded) / orderItem.qty_ordered ),'
            . ' SUM(orderItem.base_row_total_incl_tax))'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`main_table`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`main_table`.`order_currency_code`,'" . $currencyCode . "')"),
            'sum_qty_ordered' => new Zend_Db_Expr('SUM(IFNULL(warehouseShip.qty_shipped, orderItem.qty_ordered))'),
            'warehouse_name' => new Zend_Db_Expr('IFNULL(warehouseShip.warehouse_name,\'' . $this->__('Unassigned Warehouse') . '\')')
        ));
        $collection->getSelect()->distinct();
        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
        );

        return $arrayCollection;
    }
    
    public function getUnassignedWarehouseSales($requestData) {
        if(Mage::registry('unassigned_warehouse_sales')){
            return Mage::registry('unassigned_warehouse_sales');
        }
        $dateRange = $this->getTimeRange($requestData);
        $collection = $this->getOrderItemCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $dateRange['from'],
            'to' => $dateRange['to'],
            'date' => true,
        ));
        
        $collection->getSelect()->joinLeft(
                array('productSuper' => $collection->getTable('catalog/product_super_link')), 'main_table.product_id= productSuper.parent_id', array('product_id')
        );              
        $collection->getSelect()->joinLeft(
                array('warehouseShip' => $collection->getTable('inventoryplus/warehouse_shipment')), 
                'IFNULL(productSuper.product_id, main_table.product_id) = warehouseShip.product_id '
                . ' AND main_table.order_id = warehouseShip.order_id', 
                array('qty_shipped', 'qty_refunded', 'warehouse_name')
        );
        
        $collection->getSelect()->joinLeft(
                array('order' => $collection->getTable('sales/order')), '`main_table`.`order_id` = `order`.`entity_id`', array('status')
        );
        
        $collection->getSelect()->where('`warehouseShip`.`warehouse_id` is NULL');

        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT `order`.`entity_id` SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT order.entity_id)'),
            'sum_base_tax_amount' => '0',
            'sum_tax_amount' => '0',
            'sum_grand_total' => new Zend_Db_Expr('IFNULL( SUM( orderItem.row_total_incl_tax * (warehouseShip.qty_shipped - warehouseShip.qty_refunded) / orderItem.qty_ordered ),'
            . ' SUM(orderItem.row_total_incl_tax))'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL( SUM( orderItem.base_row_total_incl_tax * (warehouseShip.qty_shipped - warehouseShip.qty_refunded) / orderItem.qty_ordered ),'
            . ' SUM(orderItem.base_row_total_incl_tax))'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"),
            'sum_qty_ordered' => new Zend_Db_Expr('SUM(IFNULL(warehouseShip.qty_shipped, orderItem.qty_ordered))'),
        ));
        //Filter by order status        
        if (isset($requestData['order_status'])) {
            $collectionData = array('collection' => $collection);
            $this->_filterByOrderStatus($collectionData, $requestData['order_status']);
        }
        $item = $collection->getFirstItem();
        Mage::register('unassigned_warehouse_sales', $item);
        return $item;
    }
    
    public function getSalesWarehouseReportCollection($datefrom, $dateto, $source) {

        $collection = Mage::getResourceModel('inventoryplus/warehouse_shipment_collection');
        $collection->getSelect()->joinLeft(
                array('productSuper' => $collection->getTable('catalog/product_super_link')), 'main_table.product_id= productSuper.product_id', array('parent_id')
        );               
        $collection->getSelect()->joinLeft(
                array('orderItem' => $collection->getTable('sales/order_item')), 
                'IFNULL(productSuper.parent_id,main_table.product_id) = orderItem.product_id '
                . ' AND main_table.order_id = orderItem.order_id',
                array('base_row_total_incl_tax', 'row_total_incl_tax', 'qty_ordered')
        );
        $collection->getSelect()->join(
                array('order' => $collection->getTable('sales/order')), 'orderItem.order_id = order.entity_id', array('status', 'created_at')
        );
        $collection->addFieldToFilter('order.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        $collection->getSelect()->group("main_table.warehouse_id");
        $collection->setOrder(("IFNULL(SUM(main_table.subtotal_shipped),0)"), 'DESC');
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT `order`.`entity_id` SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT `order`.entity_id)'),
            'sum_base_tax_amount' => '0',
            'sum_tax_amount' => '0',
            'sum_grand_total' => new Zend_Db_Expr('IFNULL( SUM( orderItem.row_total_incl_tax * (main_table.qty_shipped - main_table.qty_refunded) / orderItem.qty_ordered ),'
            . ' SUM(orderItem.row_total_incl_tax))'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL( SUM( orderItem.base_row_total_incl_tax * (main_table.qty_shipped - main_table.qty_refunded) / orderItem.qty_ordered ),'
            . ' SUM(orderItem.base_row_total_incl_tax))'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"),
            'sum_qty_ordered' => new Zend_Db_Expr('SUM(IFNULL(main_table.qty_shipped, orderItem.qty_ordered))'),
            'warehouse_name' => new Zend_Db_Expr('IFNULL(main_table.warehouse_name,\'' . $this->__('Unassigned Warehouse') . '\')')
        ));
        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
        );

        return $arrayCollection;
    }    

    /**
     * Get sales by supplier
     * 
     * @param string $datefrom
     * @param string $dateto
     * @param string $source
     */
    public function getSalesSupplierReportCollection($datefrom, $dateto, $source) {
        $collection = Mage::getResourceModel('inventorypurchasing/supplier_collection');
        $collection->getSelect()->joinLeft(
                array('supplierProduct' => $collection->getTable('inventorypurchasing/supplier_product')), '`main_table`.`supplier_id` = `supplierProduct`.`supplier_id`', array('product_id')
        );
        $collection->getSelect()->joinLeft(
                array('orderItem' => $collection->getTable('sales/order_item')), '`supplierProduct`.`product_id` = `orderItem`.`product_id`', array('qty_ordered', 'row_total', 'base_row_total')
        );
        $collection->getSelect()->joinLeft(
                array('order' => $collection->getTable('sales/order')), '`orderItem`.`order_id` = `order`.`entity_id`', array('status')
        );
        $collection->addFieldToFilter('order.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        $collection->getSelect()->where('`orderItem`.`parent_item_id` is NULL');
        $collection->getSelect()->group("main_table.supplier_id");
        $collection->setOrder(("IFNULL(SUM(`orderItem`.`row_total`),0)"), 'DESC');
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT `order`.`entity_id` SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT `order`.`entity_id`)'),
            'sum_base_tax_amount' => '0',
            'sum_tax_amount' => '0',
            'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(`orderItem`.`row_total_incl_tax`),0)'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(`orderItem`.`base_row_total_incl_tax`),0)'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"),
            'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(`orderItem`.`qty_ordered`),0)'),
        ));

        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
        );
        return $arrayCollection;
    }
    
    /**
     * Get unassigned supplier sales data
     * 
     * @param array $requestData
     * @return Varien_Object
     */
    public function getUnassignedSupplierSales($requestData) {
        if(Mage::registry('unassigned_supplier_sales')){
            return Mage::registry('unassigned_supplier_sales');
        }
        $dateRange = $this->getTimeRange($requestData);
        $collection = $this->getOrderItemCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $dateRange['from'],
            'to' => $dateRange['to'],
            'date' => true,
        ));
        $collection->addFieldToFilter('main_table.sku', array('nin' => $this->getAssignedSupplierProductSkus()));
        $collection->getSelect()->joinLeft(
                array('order' => $collection->getTable('sales/order')), '`main_table`.`order_id` = `order`.`entity_id`', array('status')
        );
        
        $collection->getSelect()->where('`main_table`.`parent_item_id` is NULL');

        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT `order`.`entity_id` SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT `main_table`.`order_id`)'),
            'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(`main_table`.`row_total_incl_tax`),0)'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(`main_table`.`base_row_total_incl_tax`),0)'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"),
            'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(`main_table`.`qty_ordered`),0)'),
        ));
        //Filter by order status        
        if (isset($requestData['order_status'])) {
            $collectionData = array('collection' => $collection);
            $this->_filterByOrderStatus($collectionData, $requestData['order_status']);
        }
        $item = $collection->getFirstItem();
        Mage::register('unassigned_supplier_sales', $item);
        return $item;
    }
    
    /**
     * Get Assigned supplier product ids
     * 
     * @return array
     */
    public function getAssignedSupplierProductSkus(){
        $supplierProductIds = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $supplierProductSql = "SELECT DISTINCT `product_id` FROM ".$resource->getTableName('erp_inventory_supplier_product');
        $supplierProducts = $readConnection->fetchAll($supplierProductSql);
        foreach($supplierProducts as $result){
            $supplierProductIds[] = $result['product_id'];
        }   
        $skus = array();
        $products = Mage::getResourceModel('catalog/product_collection')
                        ->addFieldToFilter('entity_id', array('in' => $supplierProductIds));
        foreach($products as $product){
            $skus[] = $product->getSku();
        }
        return $skus;
    }

    /**
     * Get sales by SKU
     * 
     * @param string $datefrom
     * @param string $dateto
     * @param string $source
     */
    public function getSalesSKUReportCollection($datefrom, $dateto, $source) {
        $collection = $this->getOrderItemCollection();
        $collection->getSelect()->joinLeft(
                array('order' => $collection->getTable('sales/order')), 'main_table.order_id = order.entity_id', array('status')
        );
        $collection->addFieldToFilter('order.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        /*
        $collection->getSelect()->joinLeft(
                array('productSuper' => $collection->getTable('catalog/product_super_link')), 'main_table.product_id= productSuper.product_id', array('parent_id')
        );
         */
        $collection->getSelect()->joinLeft(
                array('product' => $collection->getTable('catalog/product')), 'main_table.product_id = product.entity_id', array('sku')
        );

        $collection->getSelect()->where('`main_table`.`parent_item_id` is NULL');
        $collection->getSelect()->group('product.entity_id');
        $collection->setOrder(('IFNULL(SUM(main_table.row_total),0)'), 'DESC');
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.order_id SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT `main_table`.`order_id`)'),
            'sum_base_tax_amount' => '0',
            'sum_tax_amount' => '0',
            'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.row_total_incl_tax),0)'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_row_total_incl_tax),0)'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"),
            'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(main_table.qty_ordered),0)'),
            'product_sku' => 'product.sku',
            //'child_product_qty' => 'GROUP_CONCAT(CONCAT_WS(":", main_table.sku, main_table.qty_ordered) SEPARATOR ",")',
        ));

        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
        );

        return $arrayCollection;
    }

    /**
     * Get collection of sales reports
     * 
     * @param array $requestData
     * @return array
     */
    public function getOrderReportCollection($requestData) {
	$coreResource = Mage::getSingleton('core/resource');
        $coreResource->getConnection('core_write')->query('SET SESSION group_concat_max_len = 1000000;');
        $source = isset($requestData['source_select']) ? $requestData['source_select'] : null;
        $supplier = isset($requestData['supplier_select']) ? $requestData['supplier_select'] : null;
        $report_type = isset($requestData['report_radio_select']) ? $requestData['report_radio_select'] : null;
        $arrayCollection = array();
        $timeRange = $this->getTimeRange($requestData);
        $datefrom = $timeRange['from'];
        $dateto = $timeRange['to'];
        /* Prepare Collection */
        //switch report type
        switch ($report_type) {
            case 'hours_of_day':
                $arrayCollection = $this->getHoursofdayReportCollection($datefrom, $dateto, $supplier, $source);
                break;
            case 'days_of_week':
                $arrayCollection = $this->getDaysofweekReportCollection($datefrom, $dateto, $supplier, $source);
                break;
            case 'days_of_month':
                $arrayCollection = $this->getDaysofmonthReportCollection($datefrom, $dateto, $supplier, true, $source);
                break;
            case 'sales_days':
                $arrayCollection = $this->getDaysofmonthReportCollection($datefrom, $dateto, $supplier, false, $source);
                break;
            case 'sales_warehouse':
                $arrayCollection = $this->getSalesWarehouseReportCollection($datefrom, $dateto, $source);
                break;
            case 'sales_supplier':
                $arrayCollection = $this->getSalesSupplierReportCollection($datefrom, $dateto, $source);
                break;
            case 'sales_sku':
                $arrayCollection = $this->getSalesSKUReportCollection($datefrom, $dateto, $source);
                break;
            //report by order attributes
            case 'shipping_method':
            case 'payment_method':
            case 'status':
                $collection = Mage::getResourceModel('sales/order_collection');
                $collection->addFieldToFilter('main_table.created_at', array(
                    'from' => $datefrom,
                    'to' => $dateto,
                    'date' => true,
                ));
                //check if colllection have no record
                if (count($collection) == 0) {
                    return $collection;
                }
                $attribute = $report_type;
                $cData = clone $collection;
                $cData = $cData->getFirstItem()->getData();
                if (!isset($cData[$attribute])) {
                    //sales by payment method
                    $collection = $this->prepareOrderAttributeCollection($attribute, $datefrom, $dateto, $supplier, $source);
                    $arrayCollection['collection'] = $collection;
                    $arrayCollection['filter'] = array(
                        'default' => 'main_table',
                        'count_item_id' => 'orderitem',
                        'count_entity_id' => 'order',
                    );
                } else {
                    $collection = $this->getOrderCollection();
                    $collection->addFieldToFilter('main_table.created_at', array(
                        'from' => $datefrom,
                        'to' => $dateto,
                        'date' => true,
                    ));
                    if ($supplier) {
                        $orderIds = $this->filterWithSupplier($supplier);
                        $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
                    }
                    if ($source) {
                        $collection->getSelect()
                                ->joinLeft(
                                        array('source' => $collection->getTable('webpos/survey')), 'main_table.entity_id = source.order_id', array('*')
                                )
                                ->where("source.value='" . $source . "'")
                        ;
                    }
                    $collection->getSelect()->columns(array(
                        'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT main_table.entity_id SEPARATOR ",")'),
                        'att_' . $attribute => $attribute,
                        'att_shipping_method' => new Zend_Db_Expr('IFNULL(main_table.shipping_description,"No Shipping")'),
                        'count_entity_id' => new Zend_Db_Expr('COUNT(DISTINCT main_table.entity_id)'),
                        'sum_base_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_tax_amount),0)'),
                        'sum_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(main_table.tax_amount),0)'),
                        'sum_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.subtotal),0)'),
                        'sum_base_subtotal' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_subtotal),0)'),
                        'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.grand_total),0)'),
                        'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(main_table.base_grand_total),0)'),
                        'sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(main_table.total_qty_ordered),0)')
                    ));
                    $collection->getSelect()->group('main_table.' . $attribute);
                    $arrayCollection['collection'] = $collection;
                    $arrayCollection['filter'] = array(
                        'default' => 'main_table',
                        'count_item_id' => 'orderitem',
                    );
                }
        }
        //Filter by order status        
        if (isset($requestData['order_status'])) {
            $this->_filterByOrderStatus($arrayCollection, $requestData['order_status']);
        }

        /* end Prepare Collection */
        return $arrayCollection;
    }

    public function getOrderAttributeClass($class) {
        return 'sales/order_' . $class;
    }

    /**
     * Get order attribute collection class path
     * 
     * @param string $attribute
     * @return string
     */
    public function getOrderAttributeCollection($attribute) {
        return 'inventoryreports/sales_order_' . $attribute . '_collection';
    }

    /**
     * Sales by payment method
     * 
     * @param string $attribute
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $supplier
     * @param string $source
     * @return collection
     */
    public function prepareOrderAttributeCollection($attribute, $dateFrom, $dateTo, $supplier, $source) {
        $elements = explode('_', $attribute, 2);
        $attribute = $elements[0];
        $field = $elements[1];
        $collection = Mage::getResourceModel($this->getOrderAttributeCollection($attribute));
        $orderField = 'order_id';
        if ($attribute == 'payment') {
            $orderField = 'parent_id';
            $collection->getSelect()->join(
                    array('core_config' => $collection->getTable('core/config_data')), 'core_config.path LIKE ' . new Zend_Db_Expr('CONCAT("payment/",`main_table`.`method`,"/title")'), array('att_payment_method' => 'core_config.value')
            );
        }
        $collection->getSelect()
                ->joinLeft(array(
                    'order' => $collection->getTable('sales/order')), '`main_table`.`' . $orderField . '` = `order`.`entity_id`', array("*")
                )
        ;
        $collection->addFieldToFilter('order.created_at', array(
            'from' => $dateFrom,
            'to' => $dateTo,
            'date' => true,
        ));
        $collection->getSelect()->group("main_table.$field");
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'all_order_id' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT order.entity_id SEPARATOR ",")'),
            'count_entity_id' => new Zend_Db_Expr('IFNULL(COUNT(DISTINCT `order`.`entity_id`),0)'),
            'sum_base_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(`order`.`base_tax_amount`),0)'),
            'sum_tax_amount' => new Zend_Db_Expr('IFNULL(SUM(`order`.`tax_amount`),0)'),
            'sum_subtotal' => new Zend_Db_Expr('IFNULL(SUM(`order`.`subtotal`),0)'),
            'sum_base_subtotal' => new Zend_Db_Expr('IFNULL(SUM(`order`.`base_subtotal`),0)'),
            'sum_grand_total' => new Zend_Db_Expr('IFNULL(SUM(`order`.`grand_total`),0)'),
            'sum_base_grand_total' => new Zend_Db_Expr('IFNULL(SUM(`order`.`base_grand_total`),0)'),
            'base_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')"),
            'order_currency_code' => new Zend_Db_Expr("IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"),
        ));

        $collection->getSelect()->columns(array('sum_qty_ordered' => new Zend_Db_Expr('IFNULL(SUM(order.total_qty_ordered),0)')));

        return $collection;
    }

    /**
     * Add filter by order statuses
     * 
     * @param array $arrayCollection
     * @param string | array $statuses
     * @return array
     */
    protected function _filterByOrderStatus(&$arrayCollection, $statuses) {
        $statuses = is_array($statuses) ? $statuses : explode(',', $statuses);
        $collection = $arrayCollection['collection'];
        $query = $collection->getSelect()->__toString();
        if (strpos($query, 'sales_flat_order` AS `order`') === false) {
            $collection->addFieldToFilter('main_table.status', array('in' => $statuses));
        } else {
            $collection->addFieldToFilter('order.status', array('in' => $statuses));
        }
        return $arrayCollection;
    }

}
