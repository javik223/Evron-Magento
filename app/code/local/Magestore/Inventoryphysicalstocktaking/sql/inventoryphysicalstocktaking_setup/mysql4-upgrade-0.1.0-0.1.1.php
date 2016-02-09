<?php
$installer = $this;
$installer->startSetup();
$installer->run("-- DROP TABLE IF EXISTS {$this->getTable('erp_inventory_physicalstocktaking_product_id_qty')};
    
CREATE TABLE {$this->getTable('erp_inventory_physicalstocktaking_product_id_qty')} (
      `physicalstocktakingproductidqty_id` int(11) unsigned NOT NULL auto_increment,
      `product_id` int(10) unsigned NOT NULL,
      `qty` int(11) NOT NULL default 0,
      PRIMARY KEY (`physicalstocktakingproductidqty_id`),
      CONSTRAINT fk_ProdEnt FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity`(`entity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();