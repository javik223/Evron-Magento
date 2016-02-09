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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Product_Chart extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Chart {

    /**
     * Get product Id
     * 
     * @return string
     */
    public function getProductId() {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Get product
     * 
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        if (!$this->hasData('product')) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            $this->setData('product', $product);
        }
        return $this->getData('product');
    }

    /*
     * @return array
     */

    public function getProductIds() {
        $productIds = array($this->getProductId());
        $product = $this->getProduct();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $productIds = $product->getTypeInstance(true)->getUsedProductIds($product);
        }
        return $productIds;
    }

    /**
     * Get date range
     * 
     * @return array
     */
    public function getDateRange() {
        $time = 365;
        $start_day = date('Y-m-d 00:00:00', time());
        $end_day = date('Y-m-d 23:59:59');
        $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day))
                        . " -$time day"));
        $to = $end_day;
        return array('from' => $from, 'to' => $to);
    }

    /**
     * Format chart date
     * 
     * @param string $date
     * @return string
     */
    protected function _formatChartDate($date) {
        $date = Mage::helper('core')->formatDate($date, 'medium');
        $date = date('Y-m-d', strtotime($date));
        $dateArray = explode('-', $date);
        $dateArray[1] = (int) $dateArray[1] - 1;
        return implode(',', $dateArray);
    }

    /**
     * Get sales data
     * 
     * @return colelction
     */
    public function getSalesData() {
        if (!$this->hasData('sales_data')) {
            $dateRange = $this->getDateRange();
            $collection = $this->helper('inventoryreports/order')->getOrderItemCollection();
            $collection->addFieldToFilter('created_at', array(
                'from' => $dateRange['from'],
                'to' => $dateRange['to'],
                'date' => true,
            ));
            $collection->addFieldToFilter('product_id', array('in' => $this->getProductIds()));
            $collection->getSelect()->group('DATE(created_at)');
            $collection->getSelect()->columns(array(
                'date_range' => "DATE(main_table.created_at)",
                'sold_qty' => 'IFNULL(SUM(main_table.qty_invoiced - main_table.qty_refunded),0)'
            ));
            $this->setData('sales_data', $collection);
        }
        return $this->getData('sales_data');
    }

    /**
     * Get purchase data
     * 
     * @return collection
     */
    public function getPurchaseData() {
        if (!$this->hasData('purchase_data')) {
            $dateRange = $this->getDateRange();
            $collection = Mage::getResourceModel('inventorypurchasing/purchaseorder_product_collection');
            $collection->addFieldToFilter('purchase_on', array(
                'from' => $dateRange['from'],
                'to' => $dateRange['to'],
                'date' => true,
            ));
            $collection->addFieldToFilter('product_id', array('in' => $this->getProductIds()));
            $collection->getSelect()->joinLeft(
                    array('PO' => $collection->getTable('inventorypurchasing/purchaseorder')), 'main_table.purchase_order_id= PO.purchase_order_id', array('status', 'purchase_on')
            );
            $collection->getSelect()->group('DATE(purchase_on)');
            $collection->getSelect()->columns(array(
                'date_range' => "DATE(PO.purchase_on)",
                'purchased_qty' => 'IFNULL(SUM(main_table.qty_recieved - main_table.qty_returned),0)'
            ));
            $this->setData('purchase_data', $collection);
        }
        return $this->getData('purchase_data');
    }

    /**
     * Get sales report
     * 
     * @return array
     */
    public function getSalesReport() {
        if ($this->hasData('sales_report')) {
            return $this->getData('sales_report');
        }
        $reportData = array('data' => array(), 'total' => 0);
        $collection = $this->getSalesData();
        if (count($collection)) {
            foreach ($collection as $item) {
                $dateData = $this->_formatChartDate($item->getData('date_range'));
                $reportData['data'][] = '[Date.UTC(' . $dateData . '),' . $item->getData('sold_qty') . ']';
                $reportData['total'] += $item->getData('sold_qty');
            }
        }
        $reportData['data'] = implode(',', $reportData['data']);
        $this->setData('sales_report', $reportData);
        return $this->getData('sales_report');
    }

    /**
     * Get purchasing report
     * 
     * @return array
     */
    public function getPurchaseReport() {
        if ($this->hasData('purchase_report')) {
            return $this->getData('purchase_report');
        }
        $reportData = array('data' => array(), 'total' => 0);
        $collection = $this->getPurchaseData();
        if (count($collection)) {
            foreach ($collection as $item) {
                $dateData = $this->_formatChartDate($item->getData('date_range'));
                $reportData['data'][] = '[Date.UTC(' . $dateData . '),' . $item->getData('purchased_qty') . ']';
                $reportData['total'] += $item->getData('purchased_qty');
            }
        }
        $reportData['data'] = implode(',', $reportData['data']);
        $this->setData('purchase_report', $reportData);
        return $this->getData('purchase_report');
    }

    /**
     * Get sales report as string
     * 
     * @return string
     */
    public function getSalesReportData() {
        $report = $this->getSalesReport();
        return $report['data'];
    }

    /**
     * Get purchasing report as string
     * 
     * @return string
     */
    public function getPurchaseReportData() {
        $report = $this->getPurchaseReport();
        return $report['data'];
    }

    /**
     * Get total sold qty
     * 
     * @return int|float
     */
    public function getSoldQty() {
        $report = $this->getSalesReport();
        return $report['total'];
    }

    /**
     * Get total purchased qty
     * 
     * @return int|float
     */
    public function getPurchasedQty() {
        $report = $this->getPurchaseReport();
        return $report['total'];
    }

    /**
     * Get total on-hand qty
     * 
     * @return int|float
     */
    public function getOnHandQty() {
        $total_qty = 0;
        $warehouseProducts = Mage::getModel('inventoryplus/warehouse_product')
                ->getCollection()
                ->addFieldToFilter('product_id', array('in' => $this->getProductIds()))
                ->addFieldToFilter('total_qty', array('gt' => 0));
        if (count($warehouseProducts)) {
            foreach ($warehouseProducts as $warehouseProduct) {
                $total_qty += $warehouseProduct->getTotalQty();
            }
        }
        return $total_qty;
    }

}
