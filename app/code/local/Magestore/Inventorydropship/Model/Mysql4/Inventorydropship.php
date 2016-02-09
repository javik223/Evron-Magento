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
 * Inventorydropship Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Model_Mysql4_Inventorydropship extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('inventorydropship/inventorydropship', 'dropship_id');
    }
    
    public function checkSupplerId($supplierId)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array('supplier_id' => (int)$supplierId);
        $select  = $adapter->select()
            ->from($this->getTable('inventorypurchasing/supplier'), 'supplier_id')
            ->where('supplier_id = :supplier_id')
            ->limit(1);

        $result = $adapter->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }
}