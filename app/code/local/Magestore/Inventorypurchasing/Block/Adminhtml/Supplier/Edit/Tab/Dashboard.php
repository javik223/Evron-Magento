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
 * Supplier Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Block_Adminhtml_Supplier_Edit_Tab_Dashboard extends Mage_Adminhtml_Block_Template {

    protected function getSupplierId() {
        $supplierId = $this->getRequest()->getParam('id');
        return $supplierId;
    }

    /**
     * Get supplier
     * 
     * @return Magestore_Inventorypurchasing_Model_Supplier
     */
    protected function getSupplier() {
        $supplierId = $this->getSupplierId();
        $supplier = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
        return $supplier;
    }

    /**
     * Set template to block
     * 
     * @return Magestore_Inventorypurchasing_Block_Adminhtml_Supplier_Edit_Tab_Dashboard
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventorypurchasing/dashboard.phtml');
        return $this;
    }

    protected function _getPurchaseOrderCollection() {
        $supplierId = $this->getSupplierId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $purchaseOrderCollection = Mage::getModel('inventorypurchasing/purchaseorder')
            ->getCollection()
            ->addFieldToFilter('purchase_on', array('gteq' => $firstDate))
            ->addFieldToFilter('supplier_id', array('eq' => $supplierId));
        $purchaseOrderCollection->getSelect()->columns(array('date_without_hour' => 'date(`purchase_on`)'));
        $purchaseOrderCollection->getSelect()->columns(array('cost_purchase_by_day' => 'sum(`main_table`.`total_amount`)'));
        $purchaseOrderCollection->getSelect()->group(array('date(`purchase_on`)'));

        return $purchaseOrderCollection;
    }

    protected function _getPurchaseProductJoinPurchaseCol() {
        $supplierId = $this->getSupplierId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $purchaseOrderItemCol = Mage::getModel('inventorypurchasing/purchaseorder_productwarehouse')->getCollection();
        $purchaseOrderItemCol->addFieldToSelect(array('purchase_order_id', 'warehouse_id', 'product_id', 'qty_order', 'product_name', 'product_sku'));
        $purchaseOrderItemCol->getSelect()
                ->joinLeft(
                        array('purchaseorder' => $purchaseOrderItemCol->getTable('inventorypurchasing/purchaseorder')), "main_table.purchase_order_id = purchaseorder.purchase_order_id", array('purchase_on'));
        $purchaseOrderItemCol->addFieldToFilter('purchase_on', array('gteq' => $firstDate));
        $purchaseOrderItemCol->getSelect()->where("`purchaseorder`.`supplier_id`= $supplierId");
        $purchaseOrderItemCol->getSelect()->columns(array('date_without_hour' => 'date(`purchase_on`)'));

        return $purchaseOrderItemCol;
    }

    protected function _getPurchaseOrderProductCollection() {
        $supplierId = $this->getSupplierId();
        $firstDate = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00', strtotime('-30 days'));
        $purchaseOrderProductCollection = Mage::getModel('inventorypurchasing/purchaseorder_product')
            ->getCollection();
        $purchaseOrderProductCollection->getSelect()
            ->joinLeft(
                array('purchaseorder' => $purchaseOrderProductCollection->getTable('inventorypurchasing/purchaseorder')), "main_table.purchase_order_id = purchaseorder.purchase_order_id", array('purchase_on'));
        $purchaseOrderProductCollection->addFieldToFilter('purchase_on', array('gteq' => $firstDate));
        $purchaseOrderProductCollection->getSelect()->where("`purchaseorder`.`supplier_id`= $supplierId");
        $purchaseOrderProductCollection->getSelect()->where("`purchaseorder`.`status` = 6");
        $purchaseOrderProductCollection->getSelect()->columns(array('date_without_hour' => 'date(`purchase_on`)'));
        $purchaseOrderProductCollection->getSelect()->columns(array('purchase_qty_by_day' => 'sum(`main_table`.`qty_recieved`)'));
        $purchaseOrderProductCollection->getSelect()->columns(array('cost_purchase_by_day' => 'sum(`purchaseorder`.`total_amount`)'));

        return $purchaseOrderProductCollection;
    }

    protected function getPurchasesData() {
        $purchaseOrderCollection = $this->_getPurchaseOrderProductCollection();
        $purchaseOrderCollection->getSelect()->group(array('date(`purchase_on`)'));
        $purchaseOrderData = $purchaseOrderCollection->getData();

        $purchaseOrderByDay = array();

        foreach ($purchaseOrderData as $purchaseOrder) {
            $purchaseOrderByDay[$purchaseOrder['date_without_hour']] = $purchaseOrder;
        }

        return $purchaseOrderByDay;
    }

    protected function getUnitsBoughtReportLast30DaysToString() {
        $purchaseOrderItemCol = $this->_getPurchaseProductJoinPurchaseCol();
        $purchaseOrderItemData = $purchaseOrderItemCol->getData();
        $itemBoughtByDay = array();
        $processed = array();
        foreach ($purchaseOrderItemData as $purchaseOrderItem) {
            if (!isset($processed[$purchaseOrderItem['date_without_hour']][$purchaseOrderItem['product_id']])) {
                if (!isset($itemBoughtByDay[$purchaseOrderItem['date_without_hour']]))
                    $itemBoughtByDay[$purchaseOrderItem['date_without_hour']] = 0;
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
        return $return;
    }

    protected function getPurchasesUnitsBought() {
        $purchaseUnits = $this->getUnitsBoughtReportLast30DaysToString();
        return array_sum(explode(",", str_replace(' ', '', $purchaseUnits)));
    }

    protected function getPurchaseQtyReportLast30DaysToString() {
        $purchaseOrderByDay = $this->getPurchasesData();
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30) {
                $return .= ', ';
            }
            if (isset($purchaseOrderByDay[$d])) {
                $return .= $purchaseOrderByDay[$d]['purchase_qty_by_day'];
            } else {
                $return .= '0';
            }
        }
        return $return;
    }
    
    protected function getPurchasesPurchaseQty() {
        $purchaseOrders = $this->getPurchaseQtyReportLast30DaysToString();
        return array_sum(explode(",", str_replace(' ', '', $purchaseOrders)));
    }

    protected function getCostReportLast30DaysToString() {
        $purchaseOrderCollection = $this->_getPurchaseOrderCollection();
        $purchaseOrderData = $purchaseOrderCollection->getData();

        $purchaseOrderByDay = array();

        foreach ($purchaseOrderData as $purchaseOrder) {
            $purchaseOrderByDay[$purchaseOrder['date_without_hour']] = $purchaseOrder;
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30) {
                $return .= ', ';
            }
            if (isset($purchaseOrderByDay[$d])) {
                $return .= round($purchaseOrderByDay[$d]['cost_purchase_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        return $return;
    }
    
    protected function getPurchasesTotalCost() {
        $purchaseCost = $this->getCostReportLast30DaysToString();
        return array_sum(explode(",", str_replace(' ', '', $purchaseCost)));
    }

    private function getSalesData() {
        $supplierId = $this->getSupplierId();
        $resource = Mage::getSingleton('core/resource');
        $saleOrderItemCol = Mage::getModel('sales/order_item')->getCollection();
        $saleOrderItemCol->addFieldToSelect(array('created_at', 'order_id', 'qty_ordered', 'product_id', 'product_type', 'base_price_incl_tax', 'row_total_incl_tax'));
        $saleOrderItemCol->addFieldToFilter('main_table.created_at', array(
                    'from' => Mage::helper('inventorypurchasing/supplier')->getLast30DaysTime(),
                    'date' => true,
                ));
        $saleOrderItemCol->addFieldToFilter('parent_item_id', array('null' => true));
        $saleOrderItemCol->getSelect()
                ->joinLeft(
                        array('order' => $resource->getTableName('sales/order')), 'main_table.order_id = order.entity_id', array('status'));
        $saleOrderItemCol->getSelect()->where("`order`.`status`= 'complete'");

        $saleOrderItemCol->getSelect()
                ->joinLeft(
                        array('supplier_product' => $saleOrderItemCol->getTable('inventorypurchasing/supplier_product')), "main_table.product_id = supplier_product.product_id", array('supplier_id'));
        $saleOrderItemCol->getSelect()->where("`supplier_product`.`supplier_id`= $supplierId");
        $saleOrderItemCol->getSelect()->columns(array('date_without_hour' => 'date(`order`.`created_at`)'));
        return $saleOrderItemCol;
    }

    protected function getUnitsSoldReportLast30DaysToString() {
        $saleOrderItemCol = $this->getSalesData();
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
        return $return;
    }

    protected function getSalesUnitsSold() {
        $unitsSoldStr = $this->getUnitsSoldReportLast30DaysToString();
        $return = array_sum(explode(",", str_replace(' ', '', $unitsSoldStr)));
        return $return;
    }

    protected function getSalesOrderQtyLast30DaysToString() {
        $saleOrderItemCol = $this->getSalesData();
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
            if ($i != 30) {
                $return .= ', ';
            }
            if (isset($saleOrderItemByDay[$d])) {
                $return .= round($saleOrderItemByDay[$d]['order_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        return $return;
    }

    protected function getSalesOrderQty() {
        $qtyStr = $this->getSalesOrderQtyLast30DaysToString();
        $return = array_sum(explode(",", str_replace(' ', '', $qtyStr)));
        return $return;
    }

    protected function getRevenueLast30DaysToString() {
        $saleOrderItemCol = $this->getSalesData();
        $saleOrderItemCol->getSelect()->columns(array('revenue_by_day' => 'SUM(`row_total_incl_tax`)'));
        $saleOrderItemCol->getSelect()->group(array('date(`created_at`)'));
        $saleOrderItemData = $saleOrderItemCol->getData();
        $revenueSaleOrderByDay = array();
        foreach ($saleOrderItemData as $saleOrderItem) {
            $revenueSaleOrderByDay[$saleOrderItem['date_without_hour']] = $saleOrderItem;
        }
        $return = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
            if ($i != 30) {
                $return .= ', ';
            }
            if (isset($revenueSaleOrderByDay[$d])) {
                $return .= round($revenueSaleOrderByDay[$d]['revenue_by_day'], 2);
            } else {
                $return .= '0';
            }
        }
        return $return;
    }

    protected function getSalesTotalRevenue() {
        $qtyStr = $this->getRevenueLast30DaysToString();
        $return = array_sum(explode(",", str_replace(' ', '', $qtyStr)));
        return $return;
    }

    protected function getStockOnHandProduct() {
        $supplierId = $this->getSupplierId();
        $supplierStockOnHandCol = Mage::getModel('inventorypurchasing/supplier_product')->getCollection();
        $supplierStockOnHandCol->addFieldToFilter('supplier_id', $supplierId);
        $supplierStockOnHandCol->getSelect()
                ->joinLeft(
                        array('warehouse_product' => $supplierStockOnHandCol->getTable('inventoryplus/warehouse_product')), "main_table.product_id = warehouse_product.product_id", array('total_qty'));
        $supplierStockOnHandCol->getSelect()->group(array('main_table.product_id'));
        $supplierStockOnHandCol->getSelect()->columns(array('total_qty_by_product' => 'sum(`warehouse_product`.`total_qty`)'));
        $coreResource = Mage::getSingleton('core/resource');
        $catalog_product_entity = $coreResource->getTableName('catalog_product_entity');
        $supplierStockOnHandCol->getSelect()
                ->joinLeft(
                        array($catalog_product_entity), "main_table.product_id = {$catalog_product_entity}.entity_id", array('sku'));
        $productAttributes = array('product_name' => 'name', 'product_cost' => 'cost');
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
            $supplierStockOnHandCol->getSelect()->joinLeft(
                    array($tableAlias => $attribute->getBackendTable()), "main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}", array($alias => 'value')
            );
        }
        $supplierStockOnHandCol->setOrder('total_qty_by_product', 'DESC');
        return $supplierStockOnHandCol;
    }

    protected function getStockOnHandData() {
        $supplierStockOnHandCol = $this->getStockOnHandProduct();
        $supplierStockOnHandCol->getSelect()->limit(10);
        $supplierStockOnHandData = $supplierStockOnHandCol->getData();
        $stockOnHandByProduct = array();
        foreach ($supplierStockOnHandData as $supplierStockOnHandItem) {
            $stockOnHandByProduct[$supplierStockOnHandItem['product_id']] = $supplierStockOnHandItem;
        }
        return $stockOnHandByProduct;
    }

    protected function getStockOnHandProductSku() {
        $stockOnHandByProduct = $this->getStockOnHandData();
        $i = 0;
        $return = '[';
        foreach ($stockOnHandByProduct as $row) {
            if ($i != 0) {
                $return .= ',';
            }
            $return .= '"' . preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $row['sku']) . '"';
            $i++;
        }
        $return .= ']';
        return $return;
    }

    protected function getStockOnHandProductName() {
        $stockOnHandByProduct = $this->getStockOnHandData();
        $i = 0;
        $return = '[';
        foreach ($stockOnHandByProduct as $row) {
            if ($i != 0) {
                $return .= ',';
            }
            $return .= '"' . preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $row['product_name']) . '"';
            $i++;
        }
        $return .= ']';
        return $return;
    }

    protected function getStockOnHandProductTotalQty() {
        $stockOnHandByProduct = $this->getStockOnHandData();
        $return = '';
        foreach ($stockOnHandByProduct as $row) {
            if ($row != reset($stockOnHandByProduct)) {
                $return .= ', ';
            }
            if (isset($row)) {
                $return .= round($row['total_qty_by_product'], 2);
            } else {
                $return .= '0';
            }
        }
        return $return;
    }

    protected function getUnitsOnHandTotal() {
        return $this->getStockOnHandProduct()->getSize();
    }

    protected function getTotalQty() {
        $totalQty = 0;
        $stockOnHandByProduct = $this->getStockOnHandData();
        foreach ($stockOnHandByProduct as $row) {
            $totalQty += $row['total_qty_by_product'];
        }
        return $totalQty;
    }

    protected function getInventoryEvaluation() {
        $total = 0;
        $stockOnHandByProduct = $this->getStockOnHandData();
        foreach ($stockOnHandByProduct as $row) {
            $total += $row['product_cost'] * $row['total_qty'];
        }
        return $total;
    }

}
