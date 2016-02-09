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
abstract class Magestore_Inventoryplus_Block_Adminhtml_Gridabstract extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv() {
        $content = parent::getCsv();
        $content = chr(239) . chr(187) . chr(191) . $content;
        return $content;
    }

    /**
     * Add export csv button to Grid
     * 
     */
    public function addCSVExport() {
        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryplus')->__('CSV'));
    }

    /**
     * Sort collection
     * 
     * @return collection
     */
    protected function _prepareCollection() {
        if ($this->getCollection()) {
            $this->_sortCollection($this->getCollection());
        }
        return parent::_prepareCollection();
    }
    
    /**
     * Filter text field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterTextCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();    
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        $collection->getSelect()->where($field . ' like \'%' . $filter . '%\'');
        return $collection;
    }

    /**
     * Filter number field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterNumberCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        if (isset($filter['from'])) {
            $collection->getSelect()->having($field . ' >= \'' . $filter['from'] . '\'');
        }
        if (isset($filter['to'])) {
            $collection->getSelect()->having($field . ' <= \'' . $filter['to'] . '\'');
        }
        return $collection;
    }
    
    /**
     * Filter datetime field
     * 
     * @param type $collection
     * @param type $column
     * @return collection
     */
    protected function _filterDateCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex());
        if (isset($filter['orig_from'])) {
            $from = date('Y-m-d H:i:s', strtotime($filter['orig_from']));
            $collection->getSelect()->having($field . ' >= \'' . $from . '\'');
        }
        if (isset($filter['orig_to'])) {
            $to = date('Y-m-d H:i:s', strtotime($filter['orig_to']));
            $collection->getSelect()->having($field . ' <= \'' . $to . '\'');
        }
        return $collection;
    }
    
    /**
     * Sort collection
     * 
     * @param collection $collection
     * @return collection
     */
    protected function _sortCollection($collection) {
        $sort = $this->getRequest()->getParam('sort', $this->_defaultSort);
        $dir = $this->getRequest()->getParam('dir', $this->_defaultDir);
        $field = $this->_getRealFieldFromAlias($sort);
        if ($field) {
            $collection->getSelect()->order("$field $dir");
        }
        return $collection;
    }

    /**
     * Get real filed from alias in sql
     * 
     * @param string $alias
     * @return string
     */
    abstract protected function _getRealFieldFromAlias($alias);
}
