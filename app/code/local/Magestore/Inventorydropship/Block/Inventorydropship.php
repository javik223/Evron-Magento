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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Block_Inventorydropship extends Mage_Core_Block_Template
{
    /**
     * prepare block's layout
     *
     * @return Magestore_Inventorydropship_Block_Inventorydropship
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getCountryHtmlSelect()
    {
        if($supplier = Mage::getSingleton('inventorydropship/session')->getSupplier()){
            $countryId = $supplier->getCountryId();
            if (is_null($countryId)) {
                $countryId = Mage::getStoreConfig('general/country/default');
            }
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName('country_id')
                ->setId('country_id')
                ->setTitle(Mage::helper('checkout')->__('Country'))
                ->setClass('validate-select')
                ->setValue($countryId)
                ->setOptions($this->getCountryOptions());

            return $select->getHtml();
        }
        return '';
    }
    
    public function getCountryOptions(){
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }
    
    public function getCountryCollection(){
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }
    
    public function getActiveTab()
    {
        $action = Mage::app()->getRequest()->getActionName();
        if($action == 'index'){
            return 'supplier_info';
        }elseif($action == 'dropship'){
            return 'dropship';
        }
    }
    
    public function listDropships()
    {
        if(!$this->hasData('listDropships')) {
            $supplierId = Mage::getSingleton('inventorydropship/session')->getSupplier()->getId();
            $collection = Mage::getModel('inventorydropship/inventorydropship')
                                        ->getCollection()
                                        ->addFieldToFilter('supplier_id',$supplierId)
                                        ->setOrder('dropship_id','DESC');
            $this->setData('listDropships', $collection);
	}
	return $this->getData('listDropships'); 
    }
    
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}