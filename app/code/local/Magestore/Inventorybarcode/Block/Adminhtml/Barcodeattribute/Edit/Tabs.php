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
 * Inventorybarcode Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Block_Adminhtml_Barcodeattribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('barcodeattribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventorybarcode')->__('Barcode Attribute'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Inventorybarcode_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('inventorybarcode')->__('Barcode Attribute'),
            'title'     => Mage::helper('inventorybarcode')->__('Barcode Attribute'),
            'content'   => $this->getLayout()
                                ->createBlock('inventorybarcode/adminhtml_barcodeattribute_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}