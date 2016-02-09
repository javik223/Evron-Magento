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

/**
 * Inventorywarehouse Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Salesrate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_salesrate';
        $this->_blockGroup = 'inventorywarehouse';
        $this->_headerText = Mage::helper('inventorywarehouse')->__('Sales Rate ');
        $this->_addButtonLabel = Mage::helper('inventorywarehouse')->__('Add Item');
        parent::__construct();
        $this->setTemplate('inventorywarehouse/salesrate/content-header.phtml');
        $this->_removeButton('add');
    }
}