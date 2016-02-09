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
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Draftpo extends Mage_Adminhtml_Block_Widget {

    public function __construct() {
        parent::__construct();
    }

    protected function _prepareLayout() {
        $this->setChild('back_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(
                                array(
                                    'label' => Mage::helper('inventorysupplyneeds')->__('Back'),
                                    'onclick' => "window.location.href = '" . $this->getUrl('*/*/index', array($this->getRequest()->getParams())) . "'",
                                    'class' => 'back'
                                )
                        )
        );
        $this->setChild('delete_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(
                                array(
                                    'label' => Mage::helper('inventorysupplyneeds')->__('Delete All Drafts'),
                                    'onclick' => 'if(confirm(\''.$this->__('Are you sure you want to delete all draft purchase orders?').'\')) location.href=\'' . $this->getUrl('*/*/deletepo', array('id'=>$this->getDraftPOId())) . '\'',
                                    'class' => 'delete'
                                )
                        )
        );
        $this->setChild('create_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(
                                array(
                                    'label' => Mage::helper('inventorysupplyneeds')->__('Create Purchase Order'),
                                    'onclick' => 'if(confirm(\''.$this->__('Are you sure you want to create pending purchase orders from these drafts?').'\')) location.href=\'' . $this->getUrl('*/*/submitpo',array('id'=>$this->getDraftPOId())) . '\'',
                                    'class' => 'save'
                                )
                        )
        );
        $this->setChild('add_product_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                                    'label' => Mage::helper('inventorysupplyneeds')->__('Add Product'),
                                    'onclick' => "drafPOObject.addProductToDraftPO();return false;",
                                    'class' => 'add'
                                )
                        )
        );        
        if ($this->getRequest()->getParam('id') && !$this->getRequest()->getParam('top_filter'))
            $this->setChild('adminhtml.inventorysupplyneeds.edit.suggestdraft', $this->getLayout()->createBlock('inventorysupplyneeds/adminhtml_inventorysupplyneeds_edit_suggestdraft')
            );
        else
            $this->setChild('adminhtml.inventorysupplyneeds.edit.supplyneeds', $this->getLayout()->createBlock('inventorysupplyneeds/adminhtml_inventorysupplyneeds_edit_supplyneeds')
            );
        return parent::_prepareLayout();
    }
    
    public function getDraftPOId(){
        return $this->getRequest()->getParam('id');
    }
    
    /**
     * @param string|int $type
     * @return string
     */
    public function getMassChangeSupplierUrl($type) {
         return $this->getUrl('adminhtml/insu_inventorysupplyneeds/masschangesupplier',
                                    array('id'=>$this->getDraftPOId(),
                                            'type' => $type,
                            ));
    }    

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        return Mage::helper('inventorysupplyneeds')
                ->__('Draft Purchase Orders (Forecast to %s)', $this->formatDate($this->getDraftPO()->getForecastTo(),'long'));
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/saveDraftPO', array('_current' => true));
    }

    public function getDeleteButtonHtml() {
        return $this->getChildHtml('delete_button');
    }

    public function getCreateButtonHtml() {
        return $this->getChildHtml('create_button');
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }
    
    public function getAddProductButtonHtml() {
        return $this->getChildHtml('add_product_button');
    }
    
    /**
     * Get add product to draff purchase order url
     * 
     * @return string
     */    
    public function getAddProductUrl(){
        return $this->getUrl('*/*/addproducttopo', array('id'=>$this->getRequest()->getParam('id'), '_secure' => true));
    }    

    /**
     * 
     * @return \Magestore_Inventorysupplyneeds_Model_Draftpo
     */
    public function getDraftPO(){
        return Mage::registry('draftpo');
    }    
}
