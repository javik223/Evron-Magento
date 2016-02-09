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

class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbystockonhand_Renderer_Action 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if ($row->getAction())
            return;        
        $productId = $row->getParentId() ? $row->getParentId() : $row->getEntityId();
        $stockHistoryUrl = $this->getUrl('adminhtml/inr_product/chart', array('id'=> $productId));
        
        $html = "<a href='' onclick='window.open( "."\"" . $stockHistoryUrl . "\"". "," ."\"" 
                . $this->__('Stock History') . "\""."," ."\"" . 'scrollbars=yes, resizable=yes, width=520, height=540, top=50, left=300' . "\""."); "
                . "return false;' target='_blank'>"
                . $this->__('Stock History'). '</a>';
        
        return $html;
    }
}
