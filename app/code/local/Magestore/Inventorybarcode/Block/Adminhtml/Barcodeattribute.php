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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Block_Adminhtml_Barcodeattribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_barcodeattribute';
        $this->_blockGroup = 'inventorybarcode';
        $this->_headerText = Mage::helper('inventorybarcode')->__('Manage Barcode Attributes');
        $this->_addButtonLabel = Mage::helper('inventorybarcode')->__('Add Barcode Attribute');
        parent::__construct();
    }
}