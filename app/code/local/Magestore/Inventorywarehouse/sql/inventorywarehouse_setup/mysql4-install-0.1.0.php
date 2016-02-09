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
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$sqlAddColumn = "
drop procedure if exists AddColumnUnlessExists;
create procedure AddColumnUnlessExists(
	IN dbName tinytext,
	IN tableName tinytext,
	IN fieldName tinytext,
	IN fieldDef text)
begin
	IF NOT EXISTS (
		SELECT * FROM information_schema.COLUMNS
		WHERE column_name=fieldName
		and table_name=tableName
		and table_schema=dbName
		)
	THEN
		set @ddl=CONCAT('ALTER TABLE ',tableName,
			' ADD COLUMN ',fieldName,' ',fieldDef);
		prepare stmt from @ddl;
		execute stmt;
	END IF;
end
";

$write = Mage::getSingleton('core/resource')->getConnection('core_write');
$write->exec($sqlAddColumn);
/**
 * create inventorywarehouse table
 */
$installer->run("
    call AddColumnUnlessExists(Database(), '{$this->getTable('erp_inventory_warehouse_permission')}', 'can_send_request_stock', 'tinyint(1) NOT NULL');
        
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_warehouse_sendstock')};
    CREATE TABLE {$this->getTable('erp_inventory_warehouse_sendstock')} (
        `warehouse_sendstock_id` int(11) unsigned NOT NULL auto_increment,        
        `warehouse_id_from` int(11) unsigned default NULL,
        `warehouse_name_from` varchar(255) default '',
        `warehouse_id_to` int(11) unsigned default NULL,
        `warehouse_name_to` varchar(255) default '',
        `total_products` decimal(10,0) default '0',
        `created_at` date default NULL,
        `created_by` varchar(255) default '',
        `status` tinyint(2) NOT NULL default '1',
        `reason` text default '',
        INDEX (`warehouse_id_from`),
        INDEX (warehouse_id_to),        
        PRIMARY KEY  (`warehouse_sendstock_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_warehouse_sendstock_product')};
    CREATE TABLE {$this->getTable('erp_inventory_warehouse_sendstock_product')} (
        `warehouse_sendstock_product_id` int(11) unsigned NOT NULL auto_increment,
        `warehouse_sendstock_id` int(11) unsigned default NULL,
        `product_id` int(11) unsigned default NULL,
        `product_sku` varchar(255) default '',
        `product_name` varchar(255) default '',
        `qty` decimal(10,0) default '0',        
        PRIMARY KEY  (`warehouse_sendstock_product_id`),
        INDEX(`warehouse_sendstock_id`),
        FOREIGN KEY (`warehouse_sendstock_id`) REFERENCES {$this->getTable('erp_inventory_warehouse_sendstock')}(`warehouse_sendstock_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_warehouse_requeststock')};
    CREATE TABLE {$this->getTable('erp_inventory_warehouse_requeststock')} (
        `warehouse_requeststock_id` int(11) unsigned NOT NULL auto_increment,        
        `warehouse_id_from` int(11) unsigned default NULL,
        `warehouse_name_from` varchar(255) default '',
        `warehouse_id_to` int(11) unsigned default NULL,
        `warehouse_name_to` varchar(255) default '',
        `total_products` decimal(10,0) default '0',
        `created_at` date default NULL,
        `created_by` varchar(255) default '',
        `status` tinyint(2) NOT NULL default '1',
        `reason` text default '',
        INDEX (`warehouse_id_from`),
        INDEX (`warehouse_id_to`),        
        PRIMARY KEY  (`warehouse_requeststock_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_warehouse_requeststock_product')};
    CREATE TABLE {$this->getTable('erp_inventory_warehouse_requeststock_product')} (
        `warehouse_requeststock_product_id` int(11) unsigned NOT NULL auto_increment,
        `warehouse_requeststock_id` int(11) unsigned default NULL,
        `product_id` int(11) unsigned default NULL,
        `product_sku` varchar(255) default '',
        `product_name` varchar(255) default '',
        `qty` decimal(10,0) default '0',        
        PRIMARY KEY  (`warehouse_requeststock_product_id`),
        INDEX(`warehouse_requeststock_id`),
        FOREIGN KEY (`warehouse_requeststock_id`) REFERENCES {$this->getTable('erp_inventory_warehouse_requeststock')}(`warehouse_requeststock_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");        
$installer->endSetup();

