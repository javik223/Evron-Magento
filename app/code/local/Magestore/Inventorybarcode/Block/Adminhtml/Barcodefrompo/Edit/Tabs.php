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
class Magestore_Inventorybarcode_Block_Adminhtml_Barcodefrompo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('barcode_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventorybarcode')->__('Barcode'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $po = $this->getRequest()->getParam('po_id');
        
        if($po){
            $this->addTab('products_section', array(
                        'label' => Mage::helper('inventorybarcode')->__('Barcode'),
                        'title' => Mage::helper('inventorybarcode')->__('Barcode'),
                        'url' => $this->getUrl('*/*/productsfrompo', array('_current' => true,'po_id' => $po)),
                        'class' => 'ajax',
                    ));
        }else{
            $this->addTab('form_section', array(
                'label' => Mage::helper('inventorybarcode')->__('Barcode Information'),
                'title' => Mage::helper('inventorybarcode')->__('Barcode Information'),
                'content' => $this->getLayout()
                        ->createBlock('inventorybarcode/adminhtml_barcodefrompo_edit_tab_form')
                        ->toHtml(),
            ));
        }
        return parent::_beforeToHtml();
    }
}