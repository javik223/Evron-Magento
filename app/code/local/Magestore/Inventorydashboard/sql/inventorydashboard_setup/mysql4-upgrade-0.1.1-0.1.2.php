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
//add new dashboard tab
$dashboardTab = Mage::getModel('inventorydashboard/tabs')
        ->setData('name','General Information')
        ->setData('position','0')
        ->save();
$tabId = $dashboardTab->getId();
/**
 * create inventorydashboard table
 */
try{
    $installer->run("
        INSERT INTO {$this->getTable('erp_inventory_dashboard_item')} (tab_id, name, item_column, item_row, report_code, chart_code) VALUES ($tabId, 'Sales Order', '1', '0', 'sales_days', 'chart_line');
        INSERT INTO {$this->getTable('erp_inventory_dashboard_item')} (tab_id, name, item_column, item_row, report_code, chart_code) VALUES ($tabId, 'Stock On-Hand', '2', '0', 'stockonhand', 'chart_column');
    ");
} catch(Exception $e) {
    
}

$installer->endSetup();

