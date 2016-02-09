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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    UPDATE {$this->getTable('erp_inventory_report_type')} SET `title` = 'Days of Week' WHERE {$this->getTable('erp_inventory_report_type')}.`code` = 'days_of_week';
    insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('sales','days_of_month','Days of Month');
    insert into {$this->getTable('erp_inventory_report_type')}(`type`,`code`,`title`) values ('customer','customer','Customer');
");
$installer->endSetup();


