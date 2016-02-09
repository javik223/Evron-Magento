<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
$installer = $this;

$installer->startSetup();

$webposHelper = Mage::helper("webpos");

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('webpos_transaction')};
  CREATE TABLE {$this->getTable('webpos_transaction')} (
  `transaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cash_in` float DEFAULT '0',
  `cash_out` float DEFAULT '0',
  `type` varchar(20) DEFAULT NULL,  
  `user_id` int(4) unsigned DEFAULT NULL,
  `location_id` int(4) unsigned NULL DEFAULT 0,
  `till_id` int(4) unsigned NULL DEFAULT 0,
  `created_time` datetime DEFAULT NULL,
  `order_id` varchar(20) DEFAULT NULL,
  `store_id` int(4) unsigned DEFAULT '0',
  `previous_balance` float DEFAULT '0',
  `current_balance` float DEFAULT '0',
  `payment_method` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `transac_flag` int(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

DROP TABLE IF EXISTS {$this->getTable('webpos_xreport')};
  CREATE TABLE {$this->getTable('webpos_xreport')} (
  `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_time` datetime DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `store_id` int(11) unsigned NOT NULL DEFAULT '0',  
  `order_total` int(11) unsigned NOT NULL DEFAULT '0',
  `amount_total` float DEFAULT '0',
  `transfer_amount` float DEFAULT '0',
  `tax_amount` float DEFAULT '0',
  `refund_amount` float DEFAULT '0',
  `discount_amount` float DEFAULT '0',
  `cash_system` float DEFAULT '0',
  `cash_count` float DEFAULT '0',
  `check_system` float DEFAULT '0',
  `check_count` float DEFAULT '0',
  `cc_system` float DEFAULT '0',
  `cc_count` float DEFAULT '0',
  `other_system` float DEFAULT '0',
  `other_count` float DEFAULT '0',
  `till_id` int(11) unsigned NOT NULL DEFAULT 0,
  `location_id` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

	DROP TABLE IF EXISTS {$this->getTable('webpos_till')};

	CREATE TABLE {$this->getTable('webpos_till')} (
	  `till_id` int(11) unsigned NOT NULL auto_increment,
	  `till_name` varchar(255) NOT NULL default '',
	  `location_id` int(11) NULL,
	  `status` smallint(6) NOT NULL default '1',
	  PRIMARY KEY (`till_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
if (!$webposHelper->columnExist($this->getTable('webpos_user'), 'store_ids')) {
    $installer->run("ALTER TABLE {$this->getTable('webpos_user')} ADD `store_ids` VARCHAR(255) NOT NULL AFTER `user_id`;");
}
if (!$webposHelper->columnExist($this->getTable('webpos_transaction'), 'user_id')) {
    $installer->run("ALTER TABLE {$this->getTable('webpos_transaction')} ADD `user_id` int(4) unsigned DEFAULT NULL;");
}
if (!$webposHelper->columnExist($this->getTable('webpos_transaction'), 'till_id')) {
    $installer->run("ALTER TABLE {$this->getTable('webpos_transaction')} ADD `till_id` unsigned NULL DEFAULT 0;");
}
if (!$webposHelper->columnExist($this->getTable('webpos_transaction'), 'location_id')) {
    $installer->run("ALTER TABLE {$this->getTable('webpos_transaction')} ADD `location_id` unsigned NULL DEFAULT 0;");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'till_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/order')} ADD `till_id` int(11) unsigned NOT NULL DEFAULT 0; ");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'location_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/order')} ADD `location_id` int(11) unsigned NOT NULL DEFAULT 0; ");
}
if (!$webposHelper->columnExist($this->getTable('webpos_order'), 'till_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('webpos_order')} ADD `till_id` int(11) unsigned NOT NULL DEFAULT 0; ");
}
if (!$webposHelper->columnExist($this->getTable('webpos_order'), 'location_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('webpos_order')} ADD `location_id` int(11) unsigned NOT NULL DEFAULT 0; ");
}
if (!$webposHelper->columnExist($this->getTable('sales/quote_payment'), 'ccforpos_ref_no')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/quote_payment')} ADD `ccforpos_ref_no` VARCHAR( 255 ) NOT NULL; ");
}
if (!$webposHelper->columnExist($this->getTable('sales/order_payment'), 'ccforpos_ref_no')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/order_payment')} ADD `ccforpos_ref_no` VARCHAR( 255 ) NOT NULL; ");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_ccforpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_ccforpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_base_ccforpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_base_ccforpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_ccforpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_ccforpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_base_ccforpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_base_ccforpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_cp1forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_cp1forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_base_cp1forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_base_cp1forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_cp1forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_cp1forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_base_cp1forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_base_cp1forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_cp2forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_cp2forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_base_cp2forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_base_cp2forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_cp2forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_cp2forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_base_cp2forpos')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_base_cp2forpos` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_change')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_change` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_base_change')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_base_change` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_change')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_change` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_base_change')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_base_change` decimal(12,4) NOT NULL default '0'");
}
$webposHelper->addDefaultData();

$installer->endSetup();

$installer = new Mage_Eav_Model_Entity_Setup ();
$installer->startSetup();
$fieldList = array(
			'tax_class_id'
);
foreach ($fieldList as $field) {
	$applyTo = explode(',', $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to'));
	if (!in_array('customsale', $applyTo)) {
		$applyTo[] = 'customsale';
		$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
	}
}
$installer->endSetup();

$product = Mage::getModel('catalog/product');
$product->getIdBySku('webpos-customsale');
if ($product->getId()) {
	try {
		$product->setData('tax_class_id',0);
		$product->save();
	} catch (Exception $e) {
		Zend_debug::dump($e->getMessage());
	}
}

        
