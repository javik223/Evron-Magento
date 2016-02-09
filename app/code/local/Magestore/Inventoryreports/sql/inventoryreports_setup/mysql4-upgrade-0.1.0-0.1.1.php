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
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create inventoryreports table
 */
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('erp_inventory_report_type')};
	CREATE TABLE {$this->getTable('erp_inventory_report_type')} (
		`report_type_id` int(11) unsigned NOT NULL auto_increment,
		`type` varchar(255) default '',
		`code` varchar(255) default '',
        `title` varchar(255) default'',
        `default_time_range` varchar(255) default 'last_30_days',
		PRIMARY KEY  (`report_type_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('sales','hours_of_day','Hours');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('sales','days_of_week','Days');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('sales','order_attribute','Order Attributes');
        insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('sales','invoice','Invoices');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('sales','refund','Refunds');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','total_qty_adjuststock','Total Qty Adjusted');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','number_of_product_adjuststock','Product Adjusted');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','total_order_by_warehouse','Sales Orders');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','sales_by_warehouse_revenue','Revenue');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','sales_by_warehouse_item_shipped','Item Shipped');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','total_stock_transfer_send_stock','Stock Sending');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','total_stock_transfer_request_stock','Stock Request');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','supply_needs_by_warehouse_products','Supply Needs');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('warehouse','total_stock_different_when_physical_stocktaking_by_warehouse','Stocktaking Variance');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('product','best_seller','Bestsellers');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('product','most_stock_remain','Stock Remaining');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('product','warehousing_time_longest','Warehousing Time');
	insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('supplier','purchase_order_to_supplier','Purchase Orders');
        
        update {$this->getTable('erp_inventory_report_type')} SET `default_time_range` = 'next_30_days' WHERE `code` = 'supply_needs_by_warehouse_products';
");
$installer->endSetup();


