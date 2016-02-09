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

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_draft_purchase_order')}(
        `draft_po_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `currency` varchar(10) NOT NULL,
        `change_rate` float NOT NULL DEFAULT 1,
        `sales_from` datetime NOT NULL,
        `sales_to` datetime NOT NULL,
        `daterange_type` varchar(20) NOT NULL,
        `forecast_to` datetime NOT NULL,
        `warehouses` text NOT NULL,
        `suppliers` text NOT NULL,
        `purchase_rate` float NOT NULL DEFAULT '1',
        `created_at` datetime NOT NULL,
        `created_by` varchar(255) NOT NULL,
        PRIMARY KEY(`draft_po_id`)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;    
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('erp_inventory_draft_purchase_order_product')}(
        `draft_po_product_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `draft_po_id` int(11) unsigned NOT NULL,
        `product_id` int(11) unsigned NOT NULL,
        `supplier_id` int(11) unsigned NOT NULL,
        `purchase_more` decimal(12,4) unsigned NOT NULL default '0',
        `warehouse_purchase` text,
        PRIMARY KEY(`draft_po_product_id`),
        INDEX(`draft_po_id`),
        FOREIGN KEY (`draft_po_id`) REFERENCES {$this->getTable('erp_inventory_draft_purchase_order')}(`draft_po_id`) ON DELETE CASCADE ON UPDATE CASCADE
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;  
    
");

$installer->endSetup();

