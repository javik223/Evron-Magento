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
 * @package     Magestore_Inventoryplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
$installer = $this;

$installer->startSetup();

$installer->run("
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_product')}
    MODIFY COLUMN `total_qty` DECIMAL(12,4);
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_product')}
    MODIFY COLUMN `available_qty` DECIMAL(12,4);

    ALTER TABLE {$this->getTable('erp_inventory_warehouse_order')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_order_erp_inventory_warehouse1`
    FOREIGN KEY (`warehouse_id`)
    REFERENCES {$this->getTable('erp_inventory_warehouse')} (`warehouse_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_order')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_order_sales_flat_order`
    FOREIGN KEY (`order_id`) 
    REFERENCES {$this->getTable('sales_flat_order')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_order')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_order_catalog_product`
    FOREIGN KEY (`product_id`)
    REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_order')}
    MODIFY COLUMN `qty` DECIMAL(12,4);
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_shipment')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_shipment_warehouse`
    FOREIGN KEY (`warehouse_id`)
    REFERENCES {$this->getTable('erp_inventory_warehouse')} (`warehouse_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_shipment')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_order_sales_flat_shipment`
    FOREIGN KEY (`shipment_id`) 
    REFERENCES {$this->getTable('sales_flat_shipment')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_warehouse_shipment')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_shipment_catalog_product`
    FOREIGN KEY (`product_id`)
    REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_adjuststock')}
    ADD CONSTRAINT `fk_erp_inventory_adjuststock_erp_inventory_warehouse1`
    FOREIGN KEY (`warehouse_id`)
    REFERENCES {$this->getTable('erp_inventory_warehouse')} (`warehouse_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_adjuststock_product')}
    ADD CONSTRAINT `fk_erp_inventory_adjuststock_product_catalog_product`
    FOREIGN KEY (`product_id`)
    REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
    ALTER TABLE {$this->getTable('erp_inventory_adjuststock_product')}
    MODIFY COLUMN `old_qty` DECIMAL(12,4);
    
    ALTER TABLE {$this->getTable('erp_inventory_adjuststock_product')}
    MODIFY COLUMN `suggest_qty` DECIMAL(12,4);
    
    ALTER TABLE {$this->getTable('erp_inventory_adjuststock_product')}
    MODIFY COLUMN `adjust_qty` DECIMAL(12,4);

    ALTER TABLE {$this->getTable('erp_inventory_warehouse_history_content')}
    ADD CONSTRAINT `fk_erp_inventory_warehouse_history_content_erp_inventory_ware1`
    FOREIGN KEY (`warehouse_history_id`)
    REFERENCES {$this->getTable('erp_inventory_warehouse_history')} (`warehouse_history_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
");


$installer->endSetup();
