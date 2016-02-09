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
 * Inventorysupplyneeds Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Draftpo_Renderer_Warehouseqty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Get suppliers dropdown
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $warehouseId = $this->getColumn()->getWarehouseId();
        $html = '<input value="' . $this->_getWarehouseQty($row) . '" '
                . 'name="warehouse_purchase[' . $row->getProductId() . '][' . $warehouseId . ']" '
                . 'id="warehouse_purchase[' . $row->getProductId() . '][' . $warehouseId . ']" '
                . 'type="number" min="0" '
                . 'product_id="' . $row->getProductId() . '" '
                . ($this->_canEdit() ? '' : ' disabled ')
                . 'class="input-text po-update warehouse-qty">';
        $html .= '<br/>' . $this->_getWarehouseStatisticHtml($row);
        return $html;
    }

    /**
     * Get purchase qty of warehouse
     * 
     * @param Varien_Object $row
     * @return int|float
     */
    protected function _getWarehouseQty($row) {
        $warehouseQty = json_decode($row->getData('warehouse_purchase'));
        if (count($warehouseQty)) {
            foreach ($warehouseQty as $warehouseId => $qty) {
                if ($this->getColumn()->getWarehouseId() == $warehouseId)
                    return $qty;
            }
        }
        return 0;
    }

    /**
     * Get warehouse statistics
     * 
     * @param Varien_Object $row
     * @return string
     */
    protected function _getWarehouseStatisticHtml($row) {
        $statistics = array();
        $html = '';
        $attachFields = array('total_qty_ordered' => $this->__('Total Sold'), 
                                'avg_qty_ordered' => $this->__('Total Sold per Day'), 
                                'available_qty' => $this->__('Available Qty'), 
                                'in_purchasing' => $this->__('Qty On Order'), 
                                'supplyneeds' => $this->__('Supply Needs'));
        $warehouseId = $this->getColumn()->getWarehouseId();
        foreach ($attachFields as $attachField=>$label) {
            $value = ((float) $row->getData($attachField . '_' . $warehouseId)) ? : 0;
            if ($attachField == 'supplyneeds') {
                $value = ceil($value);
            }
            $statistics['text'][] = $label.': '.$value;
            $statistics['value'][] = $value;
        }
        $html = '<a href="javascript:void(0);" style="text-decoration:none;" title="'.implode("\n", $statistics['text']).'">'
                . implode(' | ', $statistics['value']) . '</a>';
        return $html;
    }

    /**
     * Check edit permission
     * 
     * @return boolean
     */
    protected function _canEdit() {
        $warehouseId = $this->getColumn()->getWarehouseId();
        return Mage::helper('inventoryplus')->getPermission($warehouseId, 'can_purchase_product');
    }

}
