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
 * @package     Magestore_Inventorydropship
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
 * create inventorydropship table
 */
$installer->run("
    
call AddColumnUnlessExists(Database(), '{$this->getTable('erp_inventory_supplier')}', 'password_hash', 'varchar(255) default \'\'');
    

    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_dropship')} (
      `dropship_id` int(11) unsigned NOT NULL auto_increment,
      `order_id` int(11) unsigned  NOT NULL,
      `supplier_id` int(11) NOT NULL default '0',
      `supplier_name` varchar(255) NOT NULL default '',
      `supplier_email` varchar(255) NOT NULL default '',
      `shipping_name` varchar(255) NOT NULL default '',
      `created_on` datetime NULL,      
      `status` smallint(6) NOT NULL default '0',
      `admin_name` varchar(255) NOT NULL default '',
      `admin_email` varchar(255) NOT NULL default '',
      `session` varchar(255) NOT NULL default '',
      `increment_id` varchar(255) default '',
      INDEX(`order_id`),
      FOREIGN KEY (`order_id`) REFERENCES {$this->getTable('sales/order')}(`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      PRIMARY KEY (`dropship_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_dropship_product')} (
      `dropshipproduct_id` int(11) unsigned NOT NULL auto_increment,
      `dropship_id` int(11) unsigned  NOT NULL,
      `item_id` int(11) unsigned NOT NULL,
      `supplier_id` int(11) NOT NULL default '0',
      `supplier_name` varchar(255) NOT NULL default '',      
      `product_id` int(11) NOT NULL default '0',
      `product_sku` varchar(255) NOT NULL default '',      
      `product_name` varchar(255) NOT NULL default '',
      `qty_request` decimal(10,0) default '0',
      `qty_offer` decimal(10,0) default '0',
      `qty_approve` decimal(10,0) default '0',
      `qty_shipped` decimal(10,0) default '0', 
      INDEX(`dropship_id`),
      FOREIGN KEY (`dropship_id`) REFERENCES {$this->getTable('erp_inventory_dropship')}(`dropship_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      PRIMARY KEY (`dropshipproduct_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");

$installer->endSetup();

 