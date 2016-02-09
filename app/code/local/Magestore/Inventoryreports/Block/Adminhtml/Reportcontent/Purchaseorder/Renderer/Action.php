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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Purchaseorder_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if ($row->getAction())
            return;
        //get row order ids
        $purchaseorderIds = $row->getAllPurchaseOrderId();
        $filter = 'purchaseorderids=' . $purchaseorderIds;
        $filter = base64_encode($filter);
        $orderUrl = Mage::helper("adminhtml")
                ->getUrl('adminhtml/inr_report/purchaseorderlist', array('top_filter' => $filter,
            '_secure' => Mage::app()->getStore()->isCurrentlySecure())
        );
        $html = "<a href='' onclick='window.open( " . "\"" . $orderUrl . "\"" . "," . "\""
                . $this->__('Order List') . "\"" . "," . "\"" . 'scrollbars=yes, resizable=yes, width=1000, height=600, top=50, left=300' . "\"" . "); "
                . "return false;' target='_blank'>" . $this->__('View Orders List') . "</a>";
        //show only in report by sku
        if ($this->getColumn()->getGrid()->isPOSKUReport()) {
            $productId = $row->getParentId() ? $row->getParentId() : $row->getProductId();
            $stockHistoryUrl = $this->getUrl('adminhtml/inr_product/chart', array('id' => $productId));

            $html = "<a href='' onclick='window.open( " . "\"" . $stockHistoryUrl . "\"" . "," . "\""
                    . $this->__('Stock History') . "\"" . "," . "\"" . 'scrollbars=yes, resizable=yes, width=520, height=540, top=50, left=300' . "\"" . "); "
                    . "return false;' target='_blank'>"
                    . $this->__('Stock History') . '</a> <br/>' . $html;
        }
        return $html;
    }

}
