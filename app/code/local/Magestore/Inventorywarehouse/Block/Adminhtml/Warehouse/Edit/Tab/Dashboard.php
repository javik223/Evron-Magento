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
 * Warehouse Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Edit_Tab_Dashboard extends Mage_Adminhtml_Block_Template {

    protected $_qtyOrderedLast30daysByString;
    protected $_qtyItemSoldLast30daysByString;
    protected $_revenueSalesOrderedLast30daysByString;
    protected $_qtyPurchasedLast30daysByString;
    protected $_qtyItemBoughtLast30daysByString;
    protected $_totalCostPurchaseLast30daysByString;

    /**
     * Get warehouse
     * 
     * @return Magestore_Inventoryplus_Model_Warehouse
     */
    public function getWarehouseId() {
        $warehouse_id = $this->getRequest()->getParam('id');
        return $warehouse_id;
    }

    /**
     * Set template to block
     * 
     * @return Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Edit_Tab_Dashboard
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventorywarehouse/dashboard.phtml');
        return $this;
    }

    public function getSalesReportLast30days() {
        $warehouseId = $this->getWarehouseId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $saleOrderItemCol = Mage::getModel('sales/order_item')->getCollection();
        $saleOrderItemCol->addFieldToSelect(array('created_at', 'order_id', 'qty_ordered', 'product_id', 'product_type', 'base_price_incl_tax'));
        $saleOrderItemCol->addFieldToFilter('created_at', array('gteq' => $firstDate));
        $saleOrderItemCol->addFieldToFilter('product_type', array('nin' => array('configurable', 'bundle', 'grouped', 'virtual', 'downloadable')));
        $saleOrderItemCol->getSelect()
                ->joinLeft(
                        array('warehouse_order' => $saleOrderItemCol->getTable('inventoryplus/warehouse_order')), "main_table.order_id = warehouse_order.order_id", array('warehouse_id'));
        $saleOrderItemCol->getSelect()->where("`warehouse_order`.`warehouse_id`= $warehouseId");
        $saleOrderItemCol->getSelect()->columns(array('date_without_hour' => 'date(`created_at`)'));
        return $saleOrderItemCol;
    }

    public function getQtyOrderedLast30daysToString() {
        $saleOrderItemCol = $this->getSalesReportLast30days();
        $saleOrderItemCol->getSelect()->columns(array('order_by_day' => 'SUM(`qty_ordered`)'));
        $saleOrderItemCol->getSelect()->group(array('date(`created_at`)'));
        $saleOrderItemData = $saleOrderItemCol->getData();
        $saleOrderItemByDay = array();
        foreach ($saleOrderItemData as $saleOrderItem) {
            $saleOrderItemByDay[$saleOrderItem['date_without_hour']] = $saleOrderItem;
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30)
                $return .= ', ';
            if (isset($saleOrderItemByDay[$d])) {
                $return .= round($saleOrderItemByDay[$d]['order_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        $this->_qtyOrderedLast30daysByString = $return;
        return $return;
    }

    public function getTotalQtyOrderedLast30days() {
        $qtyStr = $this->_qtyOrderedLast30daysByString;
        $return = array_sum(explode(",", str_replace(' ', '', $qtyStr)));
        return $return;
    }

    public function getItemSoldLast30daysToString() {
        $saleOrderItemCol = $this->getSalesReportLast30days();
        $saleOrderItemData = $saleOrderItemCol->getData();
        $itemSoldByDay = array();
        $processed = array();
        foreach ($saleOrderItemData as $saleOrderItem) {
            if (!isset($processed[$saleOrderItem['date_without_hour']][$saleOrderItem['product_id']])) {
                if (!isset($itemSoldByDay[$saleOrderItem['date_without_hour']]))
                    $itemSoldByDay[$saleOrderItem['date_without_hour']] = 0;
                if (!isset($itemSoldByDay[$saleOrderItem['product_id']][$saleOrderItem['date_without_hour']])) {
                    $itemSoldByDay[$saleOrderItem['date_without_hour']] += 1;
                }
                $processed[$saleOrderItem['date_without_hour']][$saleOrderItem['product_id']] = 1;
            }
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30)
                $return .= ', ';
            if (isset($itemSoldByDay[$d])) {
                $return .= $itemSoldByDay[$d];
            } else {
                $return .= '0';
            }
        }
        $this->_qtyItemSoldLast30daysByString = $return;
        return $return;
    }

    public function getUnitSoldLast30days() {
        $saleOrderItemCol = $this->getSalesReportLast30days();
        $saleOrderItemCol->getSelect()->columns(array('order_by_product' => 'SUM(`qty_ordered`)'));
        $saleOrderItemCol->getSelect()->group(array('main_table.product_id'));
        return $saleOrderItemCol->getSize();
    }

    public function getRevenueLast30daysToString() {
        $warehouseId = $this->getWarehouseId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $saleOrderItemCol = Mage::getModel('sales/order_item')->getCollection();
        $saleOrderItemCol->addFieldToSelect(array('created_at', 'base_price_incl_tax','qty_ordered'));
        $saleOrderItemCol->addFieldToFilter('created_at', array('gteq' => $firstDate));
        $saleOrderItemCol->getSelect()->columns(array('date_without_hour' => 'date(`created_at`)'));
        $saleOrderItemCol->getSelect()->columns(array('revenue_by_day' => 'SUM(`base_price_incl_tax` * `qty_ordered`)'));
        $saleOrderItemCol->getSelect()
                ->joinLeft(
                        array('warehouse_order' => $saleOrderItemCol->getTable('inventoryplus/warehouse_order')), "main_table.order_id = warehouse_order.order_id", array('warehouse_id'));
        $saleOrderItemCol->getSelect()->where("`warehouse_order`.`warehouse_id`= $warehouseId");
        $saleOrderItemCol->getSelect()->group(array('date(`created_at`)'));
        $saleOrderItemData = $saleOrderItemCol->getData();
        $revenueSaleOrderByDay = array();
        foreach ($saleOrderItemData as $saleOrderItem) {
            $revenueSaleOrderByDay[$saleOrderItem['date_without_hour']] = $saleOrderItem;
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30)
                $return .= ', ';
            if (isset($revenueSaleOrderByDay[$d])) {
                $return .= round($revenueSaleOrderByDay[$d]['revenue_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        $this->_revenueSalesOrderedLast30daysByString = $return;
        return $return;
    }

    public function getTotalRevenueLast30days() {
        $totalRevenueStr = $this->_revenueSalesOrderedLast30daysByString;
        $return = array_sum(explode(",", str_replace(' ', '', $totalRevenueStr)));
        return $return;
    }

    public function getPurchasesReportLast30days() {
        $warehouseId = $this->getWarehouseId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $purchaseOrderItemCol = Mage::getModel('inventorypurchasing/purchaseorder_productwarehouse')->getCollection();
        $purchaseOrderItemCol->addFieldToSelect(array('purchase_order_id', 'warehouse_id', 'product_id', 'qty_order', 'product_name', 'product_sku'));
        $purchaseOrderItemCol->getSelect()
                ->joinLeft(
                        array('purchaseorder' => $purchaseOrderItemCol->getTable('inventorypurchasing/purchaseorder')), "main_table.purchase_order_id = purchaseorder.purchase_order_id", array('purchase_on'));
        $purchaseOrderItemCol->addFieldToFilter('purchase_on', array('gteq' => $firstDate));
        $purchaseOrderItemCol->getSelect()->where("`main_table`.`warehouse_id`= $warehouseId");
        $purchaseOrderItemCol->getSelect()->where("`purchaseorder`.`status` = 6");
        $purchaseOrderItemCol->getSelect()->columns(array('date_without_hour' => 'date(`purchase_on`)'));
        return $purchaseOrderItemCol;
    }

    public function getQtyPurchasedLast30daysToString() {
        $purchaseOrderItemCol = $this->getPurchasesReportLast30days();
        $purchaseOrderItemCol->getSelect()->columns(array('purchase_by_day' => 'SUM(`qty_order`)'));
        $purchaseOrderItemCol->getSelect()->group(array('date(`purchase_on`)'));
        $purchaseOrderItemData = $purchaseOrderItemCol->getData();
        $purchaseOrderByDay = array();
        foreach ($purchaseOrderItemData as $purchaseOrderItem) {
            $purchaseOrderByDay[$purchaseOrderItem['date_without_hour']] = $purchaseOrderItem;
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30)
                $return .= ', ';
            if (isset($purchaseOrderByDay[$d])) {
                $return .= round($purchaseOrderByDay[$d]['purchase_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        $this->_qtyPurchasedLast30daysByString = $return;
        return $return;
    }

    public function getTotalPurchaseQtyLast30days() {
        $totalQtyPurchaseStr = $this->_qtyPurchasedLast30daysByString;
        $return = array_sum(explode(",", str_replace(' ', '', $totalQtyPurchaseStr)));
        return $return;
    }

    public function getItemBoughtLast30daysToString() {
        $purchaseOrderItemCol = $this->getPurchasesReportLast30days();
        $purchaseOrderItemData = $purchaseOrderItemCol->getData();
        $itemBoughtByDay = array();
        $processed = array();
        foreach ($purchaseOrderItemData as $purchaseOrderItem) {
            if (!isset($processed[$purchaseOrderItem['date_without_hour']][$purchaseOrderItem['product_id']])) {
                if (!isset($itemBoughtByDay[$purchaseOrderItem['date_without_hour']])){
                    $purchaseOrderItem[$purchaseOrderItem['date_without_hour']] = 0;
                    $itemBoughtByDay[$purchaseOrderItem['date_without_hour']] = 0;
                }
                if (!isset($itemBoughtByDay[$purchaseOrderItem['product_id']][$purchaseOrderItem['date_without_hour']])) {
                    $itemBoughtByDay[$purchaseOrderItem['date_without_hour']] += 1;
                }
                $processed[$purchaseOrderItem['date_without_hour']][$purchaseOrderItem['product_id']] = 1;
            }
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30)
                $return .= ', ';
            if (isset($itemBoughtByDay[$d])) {
                $return .= $itemBoughtByDay[$d];
            } else {
                $return .= '0';
            }
        }
        $this->_qtyItemBoughtLast30daysByString = $return;
        return $return;
    }

    public function getTotalItemBoughtLast30days() {
        $purchaseOrderProductCol = $this->getPurchasesReportLast30days();
        $purchaseOrderProductCol->getSelect()->group(array('main_table.product_id'));
        return count($purchaseOrderProductCol);
    }

    public function getCostPurchasedLast30daysToString() {
        $warehouseId = $this->getWarehouseId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $purchaseOrderItemCol = Mage::getModel('inventorypurchasing/purchaseorder_productwarehouse')->getCollection();
        $purchaseOrderItemCol->addFieldToSelect(array('purchase_order_id', 'warehouse_id', 'product_id', 'qty_order'));
        $purchaseOrderItemCol->getSelect()
                ->joinLeft(
                        array('purchaseorder' => $purchaseOrderItemCol->getTable('inventorypurchasing/purchaseorder')), "main_table.purchase_order_id = purchaseorder.purchase_order_id", array('purchase_on'));
        $purchaseOrderItemCol->getSelect()
                ->joinLeft(
                        array('po_product' => $purchaseOrderItemCol->getTable('inventorypurchasing/purchaseorder_product')), "main_table.purchase_order_id = po_product.purchase_order_id AND main_table.product_id = po_product.product_id", array('cost'));
        $purchaseOrderItemCol->addFieldToFilter('purchase_on', array('gteq' => $firstDate));
        $purchaseOrderItemCol->getSelect()->where("`main_table`.`warehouse_id`= $warehouseId");
        $purchaseOrderItemCol->getSelect()->where("`purchaseorder`.`status` = 6");
        $purchaseOrderItemCol->getSelect()->columns(array('date_without_hour' => 'date(`purchase_on`)'));
		/*
        $purchaseOrderItemCol->getSelect()->columns(array('cost_purchase_by_day' => 'SUM(`main_table`.`qty_order` * `po_product`.`cost`)'));
		*/
        $purchaseOrderItemCol->getSelect()->columns(array('cost_purchase_by_day' => 'SUM(`main_table`.`qty_received` * `po_product`.`cost`)'));
        $purchaseOrderItemCol->getSelect()->group(array('date(`purchase_on`)'));
        $purchaseOrderItemData = $purchaseOrderItemCol->getData();
        $purchaseOrderByDay = array();
        foreach ($purchaseOrderItemData as $purchaseOrderItem) {
            $purchaseOrderByDay[$purchaseOrderItem['date_without_hour']] = $purchaseOrderItem;
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30)
                $return .= ', ';
            if (isset($purchaseOrderByDay[$d])) {
                $return .= round($purchaseOrderByDay[$d]['cost_purchase_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        $this->_totalCostPurchaseLast30daysByString = $return;
        return $return;
    }

    public function getTotalCostLast30days() {
        $totalCostStr = $this->_totalCostPurchaseLast30daysByString;
        $return = array_sum(explode(",", str_replace(' ', '', $totalCostStr)));
        return $return;
    }

    public function getStockOnHand() {
        $warehouseId = $this->getWarehouseId();
        $warehouseProductCol = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $warehouseProductCol->addFieldToFilter('warehouse_id', $warehouseId);
        $coreResource = Mage::getSingleton('core/resource');
        $catalog_product_entity = $coreResource->getTableName('catalog_product_entity');
        $warehouseProductCol->getSelect()
                ->joinLeft(
                        array($catalog_product_entity), "main_table.product_id = {$catalog_product_entity}.entity_id", array('sku'));
        $productAttributes = array('product_name' => 'name', 'product_cost' => 'cost');
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
            $warehouseProductCol->getSelect()->joinLeft(
                    array($tableAlias => $attribute->getBackendTable()), "main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}", array($alias => 'value')
            );
        }
		$warehouseProductCol->getSelect()->group('product_id');
        $warehouseProductCol->setOrder('total_qty', 'DESC');
        $warehouseProductCol->getSelect()->limit(10);
        $return = array();
        $return['product_name'] = $return['product_sku'] = $return['product_qty'] = "[";
        $i = 0;
        foreach ($warehouseProductCol as $warehouseProduct) {
            if ($i != 0) {
                $return['product_name'] .= ', ';
                $return['product_sku'] .= ', ';
                $return['product_qty'] .= ', ';
            }
            $pName = str_replace(array(",", "'", '"'), '', $warehouseProduct->getProductName());
            $return['product_name'] .= "'" . $pName . "'";
            $return['product_sku'] .= "'" . $warehouseProduct->getSku() . "'";
            $return['product_qty'] .= $warehouseProduct->getTotalQty();
            $i++;
        }
        $return['product_name'] .= "]";
        $return['product_sku'] .= "]";
        $return['product_qty'] .= "]";
        return $return;
    }

    public function getStockOnHandDetail() {
        $warehouseId = $this->getWarehouseId();
        $warehouseProductCol = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $warehouseProductCol->addFieldToFilter('warehouse_id', $warehouseId);
        $productAttributes = array('product_name' => 'name', 'product_cost' => 'cost');
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
            $warehouseProductCol->getSelect()->joinLeft(
                    array($tableAlias => $attribute->getBackendTable()), "main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}", array($alias => 'value')
            );
        }
        $warehouseProductCol->setOrder('total_qty', 'DESC');
        $unit = $warehouseProductCol->getSize();
        $warehouseProductCol->getSelect()->columns(array('unit_on_hand' => 'COUNT(`product_id`)'));
        $warehouseProductCol->getSelect()->columns(array('qty_on_hand' => 'SUM(`total_qty`)'));
        $warehouseProductCol->getSelect()->columns(array('value_on_hand' => 'SUM(`cost_table`.`value` * `total_qty`)'));
        $f = $warehouseProductCol->getFirstItem();
        $return = array();
        $return['on_hand_unit'] = $f->getUnitOnHand();
        $return['on_hand_qty'] = $f->getQtyOnHand();
        $return['value_on_hand'] = $f->getValueOnHand();
        return $return;
    }

}
