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
    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $writeConnection = $resource->getConnection('core_write');
    $result = $readConnection->fetchAll("SHOW COLUMNS FROM " . $resource->getTableName('core_store_group') . " LIKE 'warehouse_id'");
    if (count($result) == 0) {
        $installer = $this;
        $installer->startSetup();

        $installer->run("
            ALTER TABLE {$this->getTable('core_store_group')}
            ADD warehouse_id INT(11) UNSIGNED DEFAULT 1;

            ALTER TABLE {$this->getTable('core_store_group')}
            ADD CONSTRAINT `fk_core_store_group_erp_inventory_warehouse`
            FOREIGN KEY (`warehouse_id`)
            REFERENCES {$this->getTable('erp_inventory_warehouse')} (`warehouse_id`)
            ON DELETE SET NULL ON UPDATE CASCADE;
        ");
        $installer->endSetup();
    }  

