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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbystockonhand_Renderer_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        if ($qty = $row->getData($this->getColumn()->getId())) {
            return $qty;
        }
        $productId = $row->getData('entity_id');
        $superProduct = $this->helper('inventoryreports/stockonhand')
                            ->getSuperProduct($productId);
        if($superProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
            $productId = $superProduct->getTypeInstance()->getUsedProductIds();
        }
        $warehouseId = $this->getColumn()->getData('warehouse_id');
        return $this->helper('inventoryplus/warehouse')->getTotalQty($productId, $warehouseId);
    }

}
