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
 * @category 	Magestore
 * @package 	Magestore_Inventorysupplier
 * @copyright 	Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license 	http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

try{
    $installer->run("
        DROP TABLE IF EXISTS {$this->getTable('erp_inventory_outofstock_tracking')};
        CREATE TABLE {$this->getTable('erp_inventory_outofstock_tracking')} (
                `oos_tracking_id` int(11) unsigned NOT NULL auto_increment,
                `product_id` int(11) unsigned NOT NULL default 0,     
                `outofstock_date` datetime, 
                `instock_date` datetime,
                PRIMARY KEY  (`oos_tracking_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

        ALTER TABLE {$this->getTable('erp_inventory_send_email_log')}
        ADD `priority` INT(11) DEFAULT 0;
    ");
}catch(Exception $e){
    
}

$installer->endSetup();

