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

/**
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Supplier_Inventorybysupplier extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {                    
        $this->_controller = 'adminhtml_supplier_inventorybysupplier';
        $this->_blockGroup = 'inventoryreports';
        $this->_headerText = Mage::helper('inventoryreports')->__('Inventory Reports by Supplier');        
        parent::__construct();
        $this->_removeButton('add');
    }

}