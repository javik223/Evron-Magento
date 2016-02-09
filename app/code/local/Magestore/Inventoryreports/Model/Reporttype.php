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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Model_Reporttype extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventoryreports/reporttype');
    }
    
    public function getReportTypeData($type_id){
        $collection = $this->getCollection()
                ->addFieldToFilter('type', $type_id);
        return $collection;
    }
    
     public function getOrderAttributeOptions(){
        $options = array();
        //index will use in join table in helper/Data.php
        //index must be table_alias / field
        $options['shipping_method'] = Mage::helper('inventoryreports')->__('Shipping Method');
        $options['payment_method'] = Mage::helper('inventoryreports')->__('Payment Method');
        $options['status'] = Mage::helper('inventoryreports')->__('Status');
        $options['tax_code'] = Mage::helper('inventoryreports')->__('Tax');
        return $options;
    }
}