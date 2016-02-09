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
class Magestore_Inventoryreports_Helper_Stockonhand extends Mage_Core_Helper_Abstract {

    /**
     * Get stock remaining collection, filterable by warehouse and supplier
     * 
     * @param array $requestData
     * @return collection
     */
    public function getStockRemainingCollection($requestData) {
        $collection = Mage::getResourceModel('inventoryreports/product_collection')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id');
        $collection->getSelect()->joinLeft(
                array('warehouseproduct' => $collection->getTable('inventoryplus/warehouse_product')), 'e.entity_id = warehouseproduct.product_id', array('warehouse_id', 'total_qty')
        );

//        $collection->getSelect()->join(
//                array('productSuper' => $collection->getTable('catalog/product_super_link')), 'e.entity_id = productSuper.product_id', array('parent_id')
//        );

        $collection->getSelect()->columns(array('total_remain' => new Zend_Db_Expr("SUM(warehouseproduct.total_qty)")));
        //filter by warehouse
        if (isset($requestData['warehouse_select']) && $requestData['warehouse_select']) {
            $collection->getSelect()->where('`warehouseproduct`.`warehouse_id` = \'' . $requestData['warehouse_select'] . '\'');
        }

        //filter by supplier
        if (isset($requestData['supplier_select']) && $requestData['supplier_select']) {
            $collection->getSelect()->joinLeft(
                    array('supplierproduct' => $collection->getTable('inventorypurchasing/supplier_product')), 'e.entity_id = supplierproduct.product_id', array('supplier_id')
            );
            $collection->getSelect()->where('`supplierproduct`.`supplier_id` = \'' . $requestData['supplier_select'] . '\'');
        }
        $collection->getSelect()->group('e.entity_id');

        return $collection;
    }

    /**
     * Get super product
     * 
     * @param string|int $productId
     * @return string
     */
    public function getSuperProduct($productId) {
        if (!Mage::registry('super_product_' . $productId)) {
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                    ->getParentIdsByChild($productId);
            $productId = count($parentIds) ? $parentIds[0] : $productId;
            $superProduct = Mage::getResourceModel('catalog/product_collection')
                    ->addFieldToFilter('entity_id', $productId)
                    ->getFirstItem();

            Mage::register('super_product_' . $productId, $superProduct, true);
        }
        return Mage::registry('super_product_' . $productId);
    }

}
