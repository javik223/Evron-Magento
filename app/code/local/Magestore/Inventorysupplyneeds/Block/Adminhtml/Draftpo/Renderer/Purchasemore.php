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
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Draftpo_Renderer_Purchasemore extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Get suppliers dropdown
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $html = '<input value="'.$row->getData($this->getColumn()->getId()).'" '
                . 'name="purchase_more['.$row->getProductId().']" '
                . 'id="purchase_more['.$row->getProductId().']"'
                . 'type="number" min="0" '
                . ($this->_canEdit() ? '' : ' disabled ')
                . 'class="input-text po-update">';
        return $html;
    }
    
    /**
     * Check edit permission
     * 
     * @return boolean
     */
    protected function _canEdit(){
        return false;
    }    

}
