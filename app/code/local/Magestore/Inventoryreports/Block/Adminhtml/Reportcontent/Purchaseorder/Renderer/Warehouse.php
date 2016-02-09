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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Purchaseorder_Renderer_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        if ($qty = $row->getData($this->getColumn()->getId())) {
            return $qty;
        }
        //$productIds = explode(',', $row->getData('all_child_product_id'));
        $productIds = array($row->getData('product_id'));
        $POIDs = explode(',', $row->getAllPurchaseOrderId());
        $warehouseId = $this->getColumn()->getData('warehouse_id');
        return $this->_getWarehousePOQty($POIDs, $productIds, $warehouseId);
    }

    /**
     * Get purchased qty in each warehouse
     * 
     * @param array $POIDs
     * @param array $productIds
     * @param string $warehouseId
     * @return int|float
     */
    protected function _getWarehousePOQty($POIDs, $productIds, $warehouseId) {
        if ($this->getColumn()->getGrid()->isPOSKUReport()) {
            return $this->helper('inventoryreports/purchaseorder')
                            ->getWarehousePOQtyByProduct($POIDs, $productIds, $warehouseId);
        } else {
            return $this->helper('inventoryreports/purchaseorder')
                            ->getWarehousePOQty($POIDs, $warehouseId);
        }
    }

}
