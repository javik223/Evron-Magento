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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        //get row order ids
        $orderIds = $row->getAllOrderId();
        $filter = 'orderids=' . $orderIds;
        $filter = base64_encode($filter);
        $orderUrl = Mage::helper("adminhtml")->getUrl('adminhtml/inr_sales/orders', array('top_filter' => $filter, "_secure" => Mage::app()->getStore()->isCurrentlySecure()));
        $html .= "<a href='$orderUrl'>" . $this->__('View Orders List') . "</a>";
        return $html;
    }

}
