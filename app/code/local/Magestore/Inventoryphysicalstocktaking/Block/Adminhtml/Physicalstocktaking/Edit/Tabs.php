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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adjust Stock Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Physicalstocktaking_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('physicalstocktaking_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_Inventory_Block_Adminhtml_Adjuststock_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section',array(
                'label'     => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking'),
                'title'     => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking'),
                'url'       => $this->getUrl('*/*/product',array(
                  '_current'	=> true,
                  'id'			=> $this->getRequest()->getParam('id'),
                  'store'		=> $this->getRequest()->getParam('store')
                )),
                'class'     => 'ajax',
        ));
        return parent::_beforeToHtml();
    }
}