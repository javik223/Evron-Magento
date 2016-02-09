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
 * Inventorysupplyneeds Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_inventorysupplyneeds';
        $this->_blockGroup = 'inventorysupplyneeds';
        $this->_headerText = Mage::helper('inventorysupplyneeds')->__('Supply Need Forecast');
        parent::__construct();
        $this->setTemplate('inventorysupplyneeds/content-header.phtml');
        $this->_removeButton('add');
        $filter = $this->getRequest()->getParam('top_filter');
        $helperClass = Mage::helper('inventorysupplyneeds');
        if ($filter)
            $helperClass->setTopFilter($filter);
    }
    
    /**
     * 
     * @return \Magestore_Inventorysupplyneeds_Model_Draftpo
     */
    public function getDraftPO(){
        if(!$this->hasData('draft_purchaseorder')){
            $draftPO = $this->helper('inventorysupplyneeds')->getDraftPO();
            $this->setData('draft_purchaseorder', $draftPO);
        }
        return $this->getData('draft_purchaseorder');
    }
    
    /**
     * 
     * @return boolean
     */
    public function hasDraftPO(){
        if($this->getDraftPO()->getId())
            return true;
        return false;
    }
    
    /**
     * 
     * @return string
     */
    public function getDraftPOUrl() {
        if(!$this->hasDraftPO())
            return null;
        return $this->getUrl('adminhtml/insu_inventorysupplyneeds/viewpo', array('id'=>$this->getDraftPO()->getId()));
    }    

}
