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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */
$installer->run("
        
DROP TABLE IF EXISTS {$this->getTable('erp_inventory_supplier_shipment')};
CREATE TABLE {$this->getTable('erp_inventory_supplier_shipment')} (
	`supplier_shipment_id` int(11) unsigned NOT NULL auto_increment,
	`supplier_id` int(11) unsigned  NOT NULL,
	`supplier_name` varchar(255) NOT NULL,
	`shipment_id` int(11) unsigned  NOT NULL,
	`order_id` int(11) unsigned  NOT NULL,
	`item_id` int(11) unsigned  NOT NULL,
	`product_id` int(11) unsigned  NOT NULL,
	`qty_requested` int(11) NOT NULL default '0',
	`qty_shipped` int(11) NOT NULL default '0',
	`qty_refunded` int(11) NOT NULL default '0',
	PRIMARY KEY  (supplier_shipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

