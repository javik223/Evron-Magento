<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_warehouse_webpos_user')};
    CREATE TABLE {$this->getTable('erp_inventory_warehouse_webpos_user')} (
      `warehouse_webpos_user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_id` INT(11) UNSIGNED NOT NULL,
      `user_id` INT(11) UNSIGNED NOT NULL,
      `can_create_shipment` TINYINT(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`warehouse_webpos_user_id`),
      CONSTRAINT fk_WebposWarehouse FOREIGN KEY (`warehouse_id`) REFERENCES {$this->getTable('erp_inventory_warehouse')}(`warehouse_id`),
      CONSTRAINT fk_WebposUser FOREIGN KEY (`user_id`) REFERENCES {$this->getTable('webpos_user')}(`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
