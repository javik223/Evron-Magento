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
 * @package     Magestore_Inventorydashboard
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
class Magestore_Inventorydashboard_Helper_Constant extends Mage_Core_Helper_Abstract {

    public function getChartTypes() {
        return array(
            "chart_line" => $this->__("Line Chart"),
            "chart_pie" => $this->__("Pie Chart"),
            "chart_column" => $this->__("Column Chart"),
            "chart_table" => $this->__("Table Chart")
        );
    }

    public function getReportTypes() {
        return array(
            "sales" => array(
                "title" => $this->__("Sales"),
                "report_code" => array(
                    "days_of_month" => array(
                        "name" => $this->__("Day of month"),
                        "default_time_range" => "last_30_days"
                    ),
                    "days_of_week" => array(
                        "name" => $this->__("Day of Week"),
                        "default_time_range" => "last_30_days"
                    ),
                    "hours_of_day" => array(
                        "name" => $this->__("Hour of day"),
                        "default_time_range" => "last_30_days"
                    ),
                    "sales_days" => array(
                        "name" => $this->__("Day"),
                        "default_time_range" => "last_30_days"
                    ),
                    "shipping_method" => array(
                        "name" => $this->__("Shipping Method"),
                        'default_time_range' => 'last_30_days',
                    ),
                    "payment_method" => array(
                        "name" => $this->__("Payment Method"),
                        "default_time_range" => "last_30_days"
                    ),
                    "sales_sku" => array(
                        "name" => $this->__("SKU"),
                        "default_time_range" => "last_30_days"
                    ),
                    "status" => array(
                        "name" => $this->__("Order Status"),
                        "default_time_range" => "last_30_days"
                    ),
                    "sales_warehouse" => array(
                        "name" => $this->__("Warehouse"),
                        "default_time_range" => "last_30_days"
                    ),
                    "sales_supplier" => array(
                        "name" => $this->__("Supplier"),
                        "default_time_range" => "last_30_days"
                    )
                )
            ),
            "purchaseorder" => array(
                "title" => $this->__("Purchase"),
                "report_code" => array(
                    "po_supplier" => array(
                        "name" => $this->__("Supplier"),
                        "default_time_range" => "last_30_days",
                    ),
                    "po_warehouse" => array(
                        "name" => $this->__("Warehouse"),
                        "default_time_range" => "last_30_days",
                    ),
                    "po_sku" => array(
                        "name" => $this->__("SKU"),
                        "default_time_range" => "last_30_days",
                    )
                )
            ),
            "stockonhand" => array(
                "title" => $this->__("Stock On-Hand"),
                "report_code" => array(
                    "stockonhand" => array(
                        "name" => $this->__("Stock On-Hand"),
                        "default_time_range" => "last_30_days",
                    ),
                )
            ),
            'stockmovement' => array(
                "title" => $this->__("Stock Movement"),
                "report_code" => array(
                    "stock_in" => array(
                        "name" => $this->__("Stock Received"),
                        "default_time_range" => "last_30_days",
                    ),
                    "stock_out" => array(
                        "name" => $this->__("Stock Issued"),
                        "default_time_range" => "last_30_days",
                    )
                )
            ),
            "customer" => array(
                "title" => $this->__("Customer"),
                "report_code" => array(
                    "customer" => array(
                        "name" => $this->__("Customer Purchasing"),
                        "default_time_range" => "last_30_days",
                    )
                )
            )
        );
    }

    public function getChartReport() {
        return array(
            "sales_days" => array(
                "chart_line"
            ),
            "days_of_month" => array(
                "chart_column",
                "chart_pie"
            ),
            "days_of_week" => array(
                "chart_column",
                "chart_pie"
            ),
            "hours_of_day" => array(
                "chart_column",
                "chart_pie"
            ),
            "shipping_method" => array(
                "chart_column",
                "chart_pie"
            ),
            "payment_method" => array(
                "chart_column",
                "chart_pie"
            ),
            "sales_sku" => array(
                "chart_column",
                "chart_pie"
            ),
            "status" => array(
                "chart_column",
                "chart_pie"
            ),
            "sales_warehouse" => array(
                "chart_column",
                "chart_pie"
            ),
            "sales_supplier" => array(
                "chart_column",
                "chart_pie"
            ),
            "order_attribute" => array(
                "chart_column",
                "chart_pie"
            ),
            "po_supplier" => array(
                "chart_column",
                "chart_pie"
            ),
            "po_warehouse" => array(
                "chart_column",
                "chart_pie"
            ),
            "po_sku" => array(
                "chart_column",
                "chart_pie"
            ),
            "stockonhand" => array(
                "chart_column",
                "chart_pie"
            ),
            "stock_in" => array(
                "chart_column",
                "chart_pie"
            ),
            "stock_out" => array(
                "chart_column",
                "chart_pie"
            ),
            "customer" => array(
                "chart_column",
                "chart_pie"
            ),
        );
    }

}
