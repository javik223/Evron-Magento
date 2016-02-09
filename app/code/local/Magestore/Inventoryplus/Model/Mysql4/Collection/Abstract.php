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
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Mysql4_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract {

    /**
     * 	Checking in getSelectCountSql fucntion.    
     */
    protected $_isGroupSql = false;

    /*
     *  Checking for reset(Zend_Db_Select::HAVING)
     */
    protected $_resetHaving = false;

    /*
     * 	Checking for reset(Zend_Db_Select::COLUMNS)
     */
    protected $_resetColumns = false;

    /*
     *  Set collection is distinct or not
     */
    protected $_isDistinct = false;

    /**
     *
     * @var array 
     */
    protected $_sumFields = array();

    /**
     * Set is grouping sql
     * 
     * @param bool $value
     * @return \Magestore_Inventoryplus_Model_Mysql4_Collection_Abstract
     */
    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }

    /**
     * Reset having or not
     * 
     * @param bool $value
     * @return \Magestore_Inventoryplus_Model_Mysql4_Collection_Abstract
     */
    public function setResetHaving($value) {
        $this->_resetHaving = $value;
        return $this;
    }

    /**
     * Reset column or not
     * 
     * @param bool $value
     * @return \Magestore_Inventoryplus_Model_Mysql4_Collection_Abstract
     */
    public function setResetColumns($value) {
        $this->_resetColumns = $value;
        return $this;
    }

    /**
     * Distinct or not
     * 
     * @param bool $value
     * @return \Magestore_Inventoryplus_Model_Mysql4_Collection_Abstract
     */
    public function setIsDistinct($value) {
        $this->_isDistinct = $value;
        return $this;
    }

    /**
     * Get sum of a field in query
     * 
     * @return int|float
     */
    public function getSum($field) {
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
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            if ($this->_resetHaving) {
                $countSelect->reset(Zend_Db_Select::HAVING);
            }
            $countSelect->distinct($this->_isDistinct);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    /**
     * Get SQL for get record sum
     *
     * @return Varien_Db_Select
     */
    public function getSelectSumSql() {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        if ($this->_resetHaving) {
            $countSelect->reset(Zend_Db_Select::HAVING);
        }
        if ($this->_resetColumns) {
            $countSelect->reset(Zend_Db_Select::COLUMNS);
        }
        return $countSelect;
    }

}
