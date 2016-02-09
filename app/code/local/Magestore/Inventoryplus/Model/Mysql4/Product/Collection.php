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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Resource Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection {

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
     * @return \Magestore_Inventoryplus_Model_Mysql4_Product_Collection
     */
    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }

    /**
     * Reset having or not
     * 
     * @param bool $value
     * @return \Magestore_Inventoryplus_Model_Mysql4_Product_Collection
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
            if ($this->_isGroupSql) {
                $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
            } else {
                $this->_totalRecords = ($this->getConnection()->fetchOne($sql, $this->_bindParams));
            }
        }
        return intval($this->_totalRecords);
    }

    /**
     * Get count sql
     * 
     * @return Zend_DB_Select
     */
    public function getSelectCountSql() {
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
                //$countSelect->reset(Zend_Db_Select::GROUP);
                if ($this->_resetHaving) {
                    $countSelect->reset(Zend_Db_Select::HAVING);
                }
                //$countSelect->distinct(true);
                $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
                $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
            } else {
                $countSelect->columns('COUNT(*)');
            }
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }

    /**
     * Set items to collection
     * 
     * @param array $arr
     * @return \Magestore_Inventoryplus_Model_Mysql4_Product_Collection
     */
    public function setItems($arr) {
        //remove all item
        foreach ($this->getItems() as $key => $item) {
            $this->removeItemByKey($key);
        }
        foreach ($arr as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * Set size to collection
     * 
     * @param int $size
     */
    public function setSize($size) {
        $this->_totalRecords = intval($size);
    }

}
