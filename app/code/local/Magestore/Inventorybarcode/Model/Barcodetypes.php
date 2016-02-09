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
 * Inventorybarcode Status Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Model_Barcodetypes extends Varien_Object
{
    
    /**
     * get model option as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'code128', 'label'=>Mage::helper('inventorybarcode')->__('Code-128')),            
            array('value' => 'code25', 'label'=>Mage::helper('inventorybarcode')->__('Code-25')),
            array('value' => 'code25interleaved', 'label'=>Mage::helper('inventorybarcode')->__('Interleaved 2 of 5')),
            array('value' => 'code39', 'label'=>Mage::helper('inventorybarcode')->__('Code-39')),
            array('value' => 'ean13', 'label'=>Mage::helper('inventorybarcode')->__('Ean-13')),
            array('value' => 'ean2', 'label'=>Mage::helper('inventorybarcode')->__('Ean-2')),
            array('value' => 'ean5', 'label'=>Mage::helper('inventorybarcode')->__('Ean-5')),
            array('value' => 'ean8', 'label'=>Mage::helper('inventorybarcode')->__('Ean-8')),
            array('value' => 'identcode', 'label'=>Mage::helper('inventorybarcode')->__('Identcode')),
            array('value' => 'itf14', 'label'=>Mage::helper('inventorybarcode')->__('Itf14')),
            array('value' => 'leitcode', 'label'=>Mage::helper('inventorybarcode')->__('Leitcode')),
            array('value' => 'planet', 'label'=>Mage::helper('inventorybarcode')->__('Planet')),
            array('value' => 'postnet', 'label'=>Mage::helper('inventorybarcode')->__('Postnet')),
            array('value' => 'royalmail', 'label'=>Mage::helper('inventorybarcode')->__('Royalmail')),
            array('value' => 'upca', 'label'=>Mage::helper('inventorybarcode')->__('UPC-A')),
            array('value' => 'upce', 'label'=>Mage::helper('inventorybarcode')->__('UPC-E')),
           
        );
    }
    
  
}