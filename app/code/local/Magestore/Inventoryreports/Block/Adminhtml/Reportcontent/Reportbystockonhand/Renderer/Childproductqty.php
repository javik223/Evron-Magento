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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbystockonhand_Renderer_Childproductqty
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $html = null;
        if(!$childProductQtys = $row->getData('child_product_qty')){
            return $html;
        }
        $qtyData = array();
        foreach(explode(',', $childProductQtys) as $childProductQty){
            $childProductQty = explode(':', $childProductQty);
            $childProductQty[1] = $childProductQty[1] ? $childProductQty[1] : 0;
            if(isset($qtyData[$childProductQty[0]])){
                $qtyData[$childProductQty[0]] += $childProductQty[1];
            } else {
                 $qtyData[$childProductQty[0]] = $childProductQty[1];
            }
        }
        $productSkus = Mage::getResourceModel('catalog/product_collection')
                                ->addFieldToFilter('entity_id', array('in' => array_keys($qtyData)));
        
        foreach($productSkus as $productSku){
            $html .= $productSku->getSku() .': '. (int) $qtyData[$productSku->getId()] . '<br/>';
        }
        return $html;
    }

}
