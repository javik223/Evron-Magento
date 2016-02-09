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
 * @package     Magestore_Inventoryphysicalstocktaking
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	ALTER TABLE {$this->getTable('erp_inventory_warehouse_permission')} 
		ADD `can_physical` tinyint(1) NOT NULL default 0;
	
	DROP TABLE IF EXISTS {$this->getTable('erp_inventory_physicalstocktaking')};
	CREATE TABLE {$this->getTable('erp_inventory_physicalstocktaking')} (
		`physicalstocktaking_id` int(11) unsigned NOT NULL auto_increment,
		`warehouse_id` int(11) unsigned NOT NULL,
		`warehouse_name` varchar(255) NOT NULL,
		`file_path` varchar(255) NOT NULL,
		`created_at` date,
		`create_by` varchar(255) default '',
		`reason` text,
		`confirm_by` varchar(255) default '',
		`confirm_at` date,
		`status` tinyint(1) NOT NULL,
		PRIMARY KEY  (`physicalstocktaking_id`)                
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS {$this->getTable('erp_inventory_physicalstocktaking_product')};
	CREATE TABLE {$this->getTable('erp_inventory_physicalstocktaking_product')} (
		`physicalstocktakingproduct_id` int(11) unsigned NOT NULL auto_increment,
		`physicalstocktaking_id` int(11) unsigned  NOT NULL,
		`product_id` int(11) unsigned  NOT NULL,
		`old_qty` decimal(10,0) default '0',
		`adjust_qty` decimal(10,0) default '0',
		`updated_qty` decimal(10,0) default '0',
		PRIMARY KEY  (`physicalstocktakingproduct_id`),
		INDEX(`physicalstocktaking_id`),
		FOREIGN KEY (`physicalstocktaking_id`) REFERENCES {$this->getTable('erp_inventory_physicalstocktaking')}(`physicalstocktaking_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();

