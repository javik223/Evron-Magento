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
class Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_inventorydropship';
        $this->_blockGroup = 'inventorydropship';
        $this->_headerText = Mage::helper('inventorydropship')->__('Item Manager');
        $this->_addButtonLabel = Mage::helper('inventorydropship')->__('Add Item');
        parent::__construct();
    }
    
    public function getItemsInDropship($dropshipId)
    {
        $dropshipProducts = Mage::getModel('inventorydropship/inventorydropship_product')        
                                ->getCollection()
                                ->addFieldToFilter('dropship_id',$dropshipId);
        return $dropshipProducts;
    }  
    
    public function getSupplierInformation($dropshipId)
    {
        $dropship = Mage::getModel('inventorydropship/inventorydropship')->load($dropshipId);
        $supplierId = $dropship->getSupplierId();
        $supplierName = $dropship->getSupplierName();
        $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        $supplierField = '';
        $supplierField .= '<b><a href="'.$this->getUrl('adminhtml/inpu_supplier/edit',array('id'=>$supplierId)).'">'.$supplierName.'</a></b>';
        if($supplierModel->getId()){
            $data = $supplierModel->getData();
            $supplierField .= "<br/>".$data['street'];
            if(isset($data['state'])){
                    $supplierField .=  " - ".$data['state'];
            }
            $supplierField .= " - ".$data['city'];
            $supplierField .= "<br />".$this->__('Telephone: ').$data['telephone'];
            $supplierField .= "<br/>".$this->__('Email: ').$data['supplier_email'];
        }
        return  $supplierField;        
    }
}