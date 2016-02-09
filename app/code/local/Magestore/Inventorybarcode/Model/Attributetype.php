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
 * Inventorybarcode Display Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Model_Attributetype extends Varien_Object
{
    const STATUS_YES    = 1;
    const STATUS_NO    = 0;
   

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $model = Mage::getModel('inventorybarcode/barcodeattribute_type')->getCollection();
        
        $labels = array(
                    'product' => Mage::helper('inventoryplus')->__('Product'),
                    'warehouse' => Mage::helper('inventoryplus')->__('Warehouse'),                    
                    'custom' => Mage::helper('inventoryplus')->__('Custom')
                    );
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {   
            $labels['supplier'] = Mage::helper('inventoryplus')->__('Supplier');
            $labels['purchaseorder'] =   Mage::helper('inventoryplus')->__('Purchase Order');
        }
        
        $array = array();
        
        foreach($model as $type){
            $array[$type->getAttributeType()] = $labels[$type->getAttributeType()];
        }
        return $array;
    }
    
    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value'    => $value,
                'label'    => $label
            );
        }
        return $options;
    }
}