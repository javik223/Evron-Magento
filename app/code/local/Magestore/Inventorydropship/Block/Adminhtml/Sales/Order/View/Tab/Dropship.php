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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */

class Magestore_Inventorydropship_Block_Adminhtml_Sales_Order_View_Tab_Dropship
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    
    public function getTabLabel()
    {        
        return Mage::helper('inventorydropship')->__('Drop Shipments');
    }

    public function getTabTitle()
    {
        return Mage::helper('inventorydropship')->__('Drop Shipments');
    }

    public function canShowTab()
    {
        if(Mage::getStoreConfig('inventoryplus/dropship/enable'))
            return true;
        else
            return false;
    }

    public function isHidden()
    {
        return false;
    }
    
    public function getTabClass()
    {
        return 'ajax notloaded';
    }

    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/indr_inventorydropship/dropship', array('_current'=>true));
    }
}