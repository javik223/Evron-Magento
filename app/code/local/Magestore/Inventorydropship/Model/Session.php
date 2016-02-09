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
 * Inventorydropship Status Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Model_Session extends Mage_Core_Model_Session
{
    protected $_isSupplierIdChecked = null;
    
    public function __construct()
    {
        $namespace = 'inventoryplus';
        $this->init($namespace);        
    }     
    
    public function isLoggedIn()
    {
        if($this->getSupplier())
            return (bool)$this->getSupplier()->getId() && (bool)$this->checkSupplierId($this->getSupplier()->getId());
        return false;
    }    
    
    public function checkSupplierId($supplierId)
    {
        if ($this->_isSupplierIdChecked === null) {
            $this->_isSupplierIdChecked = Mage::getResourceSingleton('inventorydropship/inventorydropship')->checkSupplerId($supplierId);
        }
        return $this->_isSupplierIdChecked;
    }
    
    public function login($username, $password)
    {        
        if ($supplier = $this->authenticate($username, $password)) {
            $this->setSupplierAsLoggedIn($supplier);            
            return true;
        }
        return false;
    }
    
    public function setSupplierAsLoggedIn($supplier)
    {
        $this->setSupplier($supplier);        
        return $this;
    }
    
    public function authenticate($username, $password)
    {
        $supplier = Mage::getModel('inventorypurchasing/supplier')
                        ->getCollection()
                        ->addFieldToFilter('supplier_email',$username)
                        ->getFirstItem();
        if($supplier->getId() && md5($password)==$supplier->getPasswordHash()){
            return $supplier;
        }else{
            return null;
        }
    }
}