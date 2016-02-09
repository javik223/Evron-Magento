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

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create inventorydashboard table
 */
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('erp_inventory_dashboard_tab')};

CREATE TABLE {$this->getTable('erp_inventory_dashboard_tab')} (
  `tab_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `position` int(6) NOT NULL,
  PRIMARY KEY (`tab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('erp_inventory_dashboard_item')};
CREATE TABLE {$this->getTable('erp_inventory_dashboard_item')} (
  `item_id` int(11) unsigned NOT NULL auto_increment,
  `tab_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `item_column` int(11) NOT NULL,
  `item_row` int(11) NOT NULL,  
  `report_code` varchar(255) NOT NULL default '',
  `chart_code` varchar(255) NOT NULL default '',
  PRIMARY KEY (`item_id`),
  INDEX (`tab_id`),
  FOREIGN KEY (`tab_id`) REFERENCES {$this->getTable('erp_inventory_dashboard_tab')}(`tab_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

