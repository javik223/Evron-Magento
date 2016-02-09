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
 * @package     Magestore_Inventoryphysicalstocktaking
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryphysicalstocktaking Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Inventoryphysicalstocktaking extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_inventoryphysicalstocktaking';
        $this->_blockGroup = 'inventoryphysicalstocktaking';
        $this->_headerText = Mage::helper('inventoryphysicalstocktaking')->__('Item Manager');
        $this->_addButtonLabel = Mage::helper('inventoryphysicalstocktaking')->__('Add Item');
        parent::__construct();
    }
}