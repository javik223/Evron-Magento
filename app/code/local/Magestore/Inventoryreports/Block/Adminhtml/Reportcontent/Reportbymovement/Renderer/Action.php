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
 * @package     Magestore_Inventoryplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbymovement_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {        
        //get row order ids
        if($row->getAction())
            return;
        $movementIds = $row->getAllMovementId();
        $filter = 'movementids='.$movementIds;
        $filter = base64_encode($filter);
        $productUrl = Mage::helper("adminhtml")->getUrl('adminhtml/inr_product/details',array('top_filter'=>$filter,"_secure" => Mage::app()->getStore()->isCurrentlySecure()));
        $html = "<a href='' onclick='window.open( "."\"" . $productUrl . "\"". "," ."\"" . $this->__('Order List') . "\""."," ."\"" . 'scrollbars=yes, resizable=yes, width=1000, height=600, top=50, left=300' . "\""."); return false;' target='_blank'>".$this->__('Show Product List')."</a>";
        return $html;

    }
}
