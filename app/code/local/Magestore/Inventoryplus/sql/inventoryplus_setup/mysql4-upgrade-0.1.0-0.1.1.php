<?php 
$installer = $this;
$installer->startSetup();
$resource = Mage::getModel('core/resource');

$installer->run("
     ALTER TABLE {$resource->getTableName('inventoryplus/warehouse')} 
         ADD `is_root` tinyint(1) NOT NULL DEFAULT '0';
     ALTER TABLE {$resource->getTableName('inventoryplus/warehouse')} 
         ADD `manager` varchar(255) NOT NULL;
     ALTER TABLE ".$resource->getTableName('erp_inventory_checkupdate')."
        ADD `is_insert_data` smallint(6) NOT NULL DEFAULT '0';    
");

/* Check if module was created before */
    $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
             ->getCollection()
             ->getFirstItem();
    if(!is_null($warehouse_product->getId())){
        $installer->run("UPDATE {$resource->getTableName('erp_inventory_checkupdate')}
                 SET `is_insert_data` = '1';");
    }
$installer->endSetup();