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
 * Inventorysupplyneeds Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Draftpo_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    public function render(Varien_Object $row) {
        $url = 'if(confirm(\'.'.$this->__('Are you sure you want to delete this draft purchase order?').'\')) location.href=\''.
                        $this->getUrl('*/*/removeproduct', array('poproductid'=>$row->getId(),
                                                            'id'=>$row->getData('draft_po_id'))).'\'';
        $html = '<a href="javascript:void(0);" onclick="'.$url.'">'.$this->__('Remove').'</a>';
        return $html;
    }
}

?>
