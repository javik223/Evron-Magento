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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$webposHelper = Mage::helper("webpos");
$installer->run("

	DROP TABLE IF EXISTS {$this->getTable('webpos_user')};

	CREATE TABLE {$this->getTable('webpos_user')} (
	  `user_id` int(11) unsigned NOT NULL auto_increment,
	  `username` varchar(255) NOT NULL default '',
	  `password` varchar(255) NOT NULL default '',
	  `display_name` text default '',
          `email` text default '',
	  `location_id` int(11) NULL,
	  `role_id` int(11) NULL,
	  `status` smallint(6) NOT NULL default '1',
	  `auto_logout` tinyint(2) DEFAULT 0,
	  PRIMARY KEY (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS {$this->getTable('webpos_role')};

	CREATE TABLE {$this->getTable('webpos_role')} (
	  `role_id` int(11) unsigned NOT NULL auto_increment,
	  `display_name` text default '',
      `permission_ids` text default '',
      `description` text default '',
      `active` tinyint(1) default 1,
      PRIMARY KEY (`role_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS {$this->getTable('webpos_user_location')};

	CREATE TABLE {$this->getTable('webpos_user_location')} (
	  `location_id` int(11) unsigned NOT NULL auto_increment,
      `display_name` text default '',
      `address` text default '',
      `description` text default '',
      PRIMARY KEY (`location_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    DROP TABLE IF EXISTS {$this->getTable('webpos_order')};

	CREATE TABLE {$this->getTable('webpos_order')} (
	  `webpos_order_id` int(11) unsigned NOT NULL auto_increment,
      `order_id` text default '',
      `user_id` int(11) NULL,
      `order_comment` text default '',
      `order_totals` decimal(12,4) UNSIGNED,
      `product_ids` text default '',
      `unit_prices` text default '',
      `order_status` text default '',
      `user_location_id` int(11) NULL,
	  `user_role_id` int(11) NULL,
	  `created_date` datetime,
	  PRIMARY KEY (`webpos_order_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	DROP TABLE IF EXISTS {$this->getTable('webpos_products')};

	CREATE TABLE {$this->getTable('webpos_products')} (
	  `webpos_product_id` int(11) unsigned NOT NULL auto_increment,
      `product_id` int NOT NULL,
      `type` text ,
      `popularity` int ,
      `last_added` datetime,
      `user_id` int(11) DEFAULT 0,
      PRIMARY KEY (`webpos_product_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

/* Mr.Jack create custom sale product */
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$product = Mage::getModel('catalog/product');

if ($productId = $product->getIdBySku('webpos-customsale')) {
    return $product->load($productId);
}

$entityType = $product->getResource()->getEntityType();
$attributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
        ->setEntityTypeFilter($entityType->getId())
        ->getFirstItem();
$websiteIds = Mage::getModel('core/website')->getCollection()
        ->addFieldToFilter('website_id', array('neq' => 0))
        ->getAllIds();
$product->setAttributeSetId($attributeSet->getId())
        ->setTypeId('customsale')
        ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
        ->setSku('webpos-customsale')
        ->setWebsiteIds($websiteIds)
        ->setStockData(array(
            'manage_stock' => 0,
            'use_config_manage_stock' => 0,
        ));
$product->addData(array(
    'name' => 'Custom Sale',
    'weight' => 1,
    'status' => 1,
    'visibility' => 1,
    'price' => 0,
    'description' => 'Custom Sale for POS system',
    'short_description' => 'Custom Sale for POS system',
));

if (!is_array($errors = $product->validate())) {
    try {
        $product->save();
        if (!$product->getId()) {
            $lastProduct = Mage::getModel('catalog/product')->getCollection()->setOrder('entity_id', 'DESC')->getFirstItem();
            $lastProductId = $lastProduct->getId();
            $product->setName('Custom Sale')->setId($lastProductId + 1)->save();
            Mage::getModel('catalog/product')->load(0)->delete();
        }
    } catch (Exception $e) {
        return false;
    }
}
/**/

if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_cash')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_cash` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/order'), 'webpos_base_cash')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `webpos_base_cash` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_cash')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_cash` decimal(12,4) NOT NULL default '0'");
}
if (!$webposHelper->columnExist($this->getTable('sales/invoice'), 'webpos_base_cash')) {
    $installer->run("ALTER TABLE {$this->getTable('sales/invoice')} ADD COLUMN `webpos_base_cash` decimal(12,4) NOT NULL default '0'");
}
$installer->endSetup();

