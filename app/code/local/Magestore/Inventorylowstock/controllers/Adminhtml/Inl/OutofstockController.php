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
 * @package     Magestore_Inventorylowstock
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorylowstock Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorylowstock
 * @author      Magestore Developer
 */
class Magestore_Inventorylowstock_Adminhtml_Inl_OutofstockController extends Magestore_Inventoryplus_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorylowstock_Adminhtml_NotificationlogController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/stock_onhand/outofstock')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Out Of Stock Tracking'), Mage::helper('adminhtml')->__('Out Of Stock Tracking')
        );
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Out Of Stock Tracking'));
        return $this;
    }

    public function insertAction() {
        $coreResource = Mage::getSingleton('core/resource');
        $writeConnection = $coreResource->getConnection('core_write');
        $listOosProducts = $this->getOutOfStockProducts();
        $listOosProducts->getSelect()
                ->joinLeft(
                        array('order_item' => $coreResource->getTableName('sales/order_item')), "e.entity_id=order_item.product_id", array('outofstock_at' => 'MAX(IFNULL(order_item.created_at,e.created_at))'));
        $listOosProducts->getSelect()->group('entity_id');
        $insertSql = '';
        $countSql = 0;
        foreach ($listOosProducts as $product) {
            $insertSql .= ' INSERT INTO ' . $coreResource->getTableName('erp_inventory_outofstock_tracking')
                    . ' (`product_id`, `outofstock_date`)'
                    . " VALUES ('" . $product->getId() . "', '" . $product->getOutofstockAt() . "');";

            $countSql++;
            if ($countSql == 900) {
                $writeConnection->query($insertSql);
                $insertSql = '';
                $countSql = 0;
            }
        }
        if (!empty($insertSql)) {
            $writeConnection->query($insertSql);
        }
    }

    protected function getOutOfStockProducts() {
        $products = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('entity_id')
                ->joinField(
                        'is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'
                )
                ->addAttributeToFilter('is_in_stock', array('eq' => 0));
        return $products;
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventorylowstock');
    }

}
