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
 * @package     Magestore_Inventoryplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
$installer = $this;

$connection = $installer->getConnection();

$installer->startSetup();

$installer->run("
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_install')} (
	`install_id` int(11) unsigned NOT NULL auto_increment,
	`status` tinyint(1) NOT NULL,
        `last_product_id` INT(11) UNSIGNED DEFAULT '0',
        `install_flag` TINYINT(1) DEFAULT 0,
        `last_shipping_progress_order_id` INT(11) UNSIGNED DEFAULT 0,
	PRIMARY KEY  (`install_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_warehouse_refund')} (
            `warehouse_refund_id` int(11) unsigned NOT NULL auto_increment,
            `warehouse_id` int(11) unsigned  NOT NULL,
            `warehouse_name` varchar(255) NOT NULL,
            `creditmemo_id` int(11) unsigned  NOT NULL,
            `order_id` int(11) unsigned  NOT NULL,
            `item_id` int(11) unsigned  NOT NULL,
            `product_id` int(11) unsigned  NOT NULL,
            `qty_refunded` int(11) NOT NULL default '0',
            PRIMARY KEY  (warehouse_refund_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_warehouse_transaction')} (
        `warehouse_transaction_id` int(11) unsigned NOT NULL auto_increment,
        `warehouse_sendstock_id` int(11) unsigned default NULL,
        `warehouse_requeststock_id` int(11) unsigned default NULL,
        `type` tinyint(1) NOT NULL default '1',
        `warehouse_id_from` int(11) unsigned default NULL,
        `warehouse_name_from` varchar(255) default '',
        `warehouse_id_to` int(11) unsigned default NULL,
        `warehouse_name_to` varchar(255) default '',
        `total_products` decimal(10,0) default '0',
        `created_at` date default NULL,
        `created_by` varchar(255) default '',
        `reason` text default '',
        PRIMARY KEY  (`warehouse_transaction_id`)       
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_warehouse_transaction_product')} (
        `warehouse_transaction_product_id` int(11) unsigned NOT NULL auto_increment,
        `warehouse_transaction_id` int(11) unsigned default NULL,
        `product_id` int(11) unsigned default NULL,
        `product_sku` varchar(255) default '',
        `product_name` varchar(255) default '',
        `qty` decimal(10,0) default '0',        
        PRIMARY KEY  (`warehouse_transaction_product_id`),
        INDEX(`warehouse_transaction_id`),
        FOREIGN KEY (`warehouse_transaction_id`) REFERENCES {$this->getTable('erp_inventory_warehouse_transaction')}(`warehouse_transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
        

    $connection->addColumn($this->getTable('erp_inventory_adjuststock'), 'stock_data', 
            array('type' => Varien_Db_Ddl_Table::TYPE_TEXT, 'comment'=>'Stock Data'));
    $connection->addColumn($this->getTable('erp_inventory_adjuststock'), 'last_product_id', 
            array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 'length' => 11, 'default' => 0, 'comment'=>'Last Product Id'));
   

    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $writeConnection = $resource->getConnection('core_write');
    //Check exists column shipping_progress
    $result = $readConnection->fetchAll("SHOW COLUMNS FROM " . $resource->getTableName('sales/order') . " LIKE 'shipping_progress'");
    if (count($result) == 0) {
        $writeConnection->query("ALTER TABLE " . $resource->getTableName('sales/order') . " ADD COLUMN `shipping_progress` TINYINT(2) NULL DEFAULT 0;");
    }
    
    try{
        $connection->insert($this->getTable('erp_inventory_install'), array('install_id' => 1, 'status' => 0, 'last_product_id' => 0, 'install_flag' => 0, 'last_shipping_progress_order_id' => 0));
    }catch(Exception $e){
        
    }    

$installer->endSetup();
