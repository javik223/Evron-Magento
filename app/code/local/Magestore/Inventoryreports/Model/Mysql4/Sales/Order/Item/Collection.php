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
 * Inventoryreports Resource Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Model_Mysql4_Sales_Order_Item_Collection 
    extends Mage_Sales_Model_Mysql4_Order_Item_Collection{
    
    /**
     *
     * @var bool 
     */
    protected $_isGroupSql = false;
    
    /*
     * @var bool
     */
    protected $_resetHaving = false;

    /**
     * Set is grouping sql
     * 
     * @param bool $value
     * @return \Magestore_Inventoryreports_Model_Mysql4_Sales_Order_Collection
     */
    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }

    /**
     * Reset having or not
     * 
     * @param bool $value
     * @return \Magestore_Inventoryreports_Model_Mysql4_Sales_Order_Collection
     */
    public function setResetHaving($value) {
        $this->_resetHaving = $value;
        return $this;
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize() {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            if ((count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) || (count($this->getSelect()->getPart(Zend_Db_Select::HAVING)) > 0)) {
                $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
            } else {
                $this->_totalRecords = ($this->getConnection()->fetchOne($sql, $this->_bindParams));
            }
        }
        return intval($this->_totalRecords);
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql() {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        //$countSelect->reset(Zend_Db_Select::HAVING);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        if ((count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) || (count($this->getSelect()->getPart(Zend_Db_Select::HAVING)) > 0)) {
            //$countSelect->reset(Zend_Db_Select::GROUP);
            //$countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }
    
}
