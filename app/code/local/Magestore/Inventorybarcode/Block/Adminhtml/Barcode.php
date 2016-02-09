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
class Magestore_Inventorybarcode_Block_Adminhtml_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_barcode';
        $this->_blockGroup = 'inventorybarcode';
        $this->_headerText = Mage::helper('inventorybarcode')->__('Manage Barcodes');
        $this->_addButtonLabel = Mage::helper('inventorybarcode')->__('Create Barcode');
        parent::__construct();
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {
            $this->_addButton('add_PO', array(
                            'label'     => Mage::helper('inventorybarcode')->__('Create Barcode from Purchase Order'),
                            'onclick'   => 'setLocation(\''. $this->getUrl('adminhtml/inb_barcode/newfrompo') .'\')',
                            'class'     => 'add',
                    ), -50);
        }
        /* Add by Kai - print barcode */
        
        $this->_addButton('print', array(
                            'label'     => Mage::helper('inventorybarcode')->__('MASS PRINT'),
                            'onclick'   => 'setLocation(\''. $this->getUrl('adminhtml/inb_barcode/edit',array('print'=>'true')) .'\')',
                            'class'     => '',
                    ), -1);
        
        /* End add by Kai - print barcode */
        
        
        
    }
}