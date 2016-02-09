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
 * Inventory Supplier Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */

class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Edit_Tab_Renderer_AvailableQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $columnName = $this->getColumn()->getName();
        $columnName = explode('_', $columnName);
        $warehouseId=$columnName[1];
        $product_id = $row->getId();
        $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                ->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $product_id)
		->getFirstItem();

       	$availableQty = 0 + $warehouse_product->getAvailableQty();
        
	return '<input name="warehouse_' . $warehouseId . '" class="input-text" type="text" value=""/><br/>'.Mage::helper('inventorypurchasing')->__('Avail. Qty: ') . $availableQty;
        

    }
}
