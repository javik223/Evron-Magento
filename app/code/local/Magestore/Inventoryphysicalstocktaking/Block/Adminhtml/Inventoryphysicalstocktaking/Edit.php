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
 * Inventoryphysicalstocktaking Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Inventoryphysicalstocktaking_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryphysicalstocktaking';
        $this->_controller = 'adminhtml_inventoryphysicalstocktaking';
        
        $this->_updateButton('save', 'label', Mage::helper('inventoryphysicalstocktaking')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('inventoryphysicalstocktaking')->__('Delete Item'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventoryphysicalstocktaking_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventoryphysicalstocktaking_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventoryphysicalstocktaking_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('inventoryphysicalstocktaking_data')
            && Mage::registry('inventoryphysicalstocktaking_data')->getId()
        ) {
            return Mage::helper('inventoryphysicalstocktaking')->__("Edit Item '%s'",
                                                $this->htmlEscape(Mage::registry('inventoryphysicalstocktaking_data')->getTitle())
            );
        }
        return Mage::helper('inventoryphysicalstocktaking')->__('Add Item');
    }
}