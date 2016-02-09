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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Calculation Earning Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_Webpos_Helper_Rewrite_Earning extends Magestore_RewardPointsRule_Helper_Calculation_Earning
{
    public function getCatalogEarningPoints($product, $customerGroupId = null, $websiteId = null, $date = null)
    {
        if (!is_object($product) and is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        
        //webpos
        $customerId = Mage::getSingleton('checkout/session')->getData('webpos_customerid');
        if($customerId){
            $customerGroupId = Mage::getModel('customer/customer')->load($customerId)->getGroupId();
        }
        else $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
//        if (is_null($customerGroupId)) {
//            if ($product->hasCustomerGroupId()) {
//                $customerGroupId = $product->getCustomerGroupId();
//            } else {
//                $customerGroupId = $this->getCustomerGroupId();
//            }
//        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $cacheKey = "catalog_earning:{$product->getId()}:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getCache($collectionKey);
        }
        foreach ($rules as $rule) {
            if ($rule->validate($product)) {
                $points += $this->calcCatalogPoint(
                                $rule->getSimpleAction(),
                                $rule->getPointsEarned(),
                                $product->getPrice(),
                                $product->getPrice() - $product->getCost(),
                                $rule->getMoneyStep(),
                                $rule->getMaxPointsEarned()
                            );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        $this->saveCache($cacheKey, $points);
        return $this->getCache($cacheKey);
    }
    /**
     * calculate earning for quote/order item
     * 
     * @param Varien_Object $item
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getCatalogItemEarningPoints($item, $customerGroupId = null, $websiteId = null, $date = null)
    {
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        
        //webpos
        $customerId = Mage::getSingleton('checkout/session')->getData('webpos_customerid');
        if($customerId){
            $customerGroupId = Mage::getModel('customer/customer')->load($customerId)->getGroupId();
        }
        else $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($item->getStoreId())->getWebsiteId();
        }
        if (is_null($date)) {
            $date = date('Y-m-d', strtotime($item->getCreatedAt()));
        }
        $cacheKey = "catalog_item_earning:{$item->getId()}:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getCache($collectionKey);
        }
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            $price  = 0;
            $profit = 0;
            foreach ($item->getChildren() as $child) {
                $price  += $child->getQty() * $child->getBasePrice();
                $profit += $child->getQty() * ($child->getBasePrice() - $child->getBaseCost());
            }
        } else {
            $price = $item->getBasePrice();
            if (!$price && $item->getPrice()) {
                $price = $item->getPrice() / Mage::app()->getStore($item->getStoreId())->convertPrice(1);
            }
            $profit = $price - $item->getBaseCost();
        }
        foreach ($rules as $rule) {
            if ($rule->validate($product)) {
                $points += $this->calcCatalogPoint(
                    $rule->getSimpleAction(),
                    $rule->getPointsEarned(),
                    $price,
                    $profit,
                    $rule->getMoneyStep(),
                    $rule->getMaxPointsEarned()
                );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        $this->saveCache($cacheKey, $points * $item->getQty());
        return $this->getCache($cacheKey);
    }   
    
    /**
     * calculate earning point for order quote
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getShoppingCartPoints($quote, $customerGroupId = null, $websiteId = null, $date = null)
    {
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        //webpos
        $customerId = Mage::getSingleton('checkout/session')->getData('webpos_customerid');
        if($customerId){
            $customerGroupId = Mage::getModel('customer/customer')->load($customerId)->getGroupId();
        }
        else $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($quote->getStoreId())->getWebsiteId();
        }
        if (is_null($date)) {
            $date = date('Y-m-d', strtotime($quote->getCreatedAt()));
        }
        $points = 0;
        
        $rules = Mage::getResourceModel('rewardpointsrule/earning_sales_collection')
            ->setAvailableFilter($customerGroupId, $websiteId, $date);
        $items = $quote->getAllItems();
        $this->setStoreId($quote->getStoreId());
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if (!$rule->validate($address)) {
                continue;
            }
            $rowTotal = 0;
            $qtyTotal = 0;
            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($rule->getActions()->validate($item)) {
                    $rowTotal += max(0, $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - $item->getRewardpointsBaseDiscount());
                    $qtyTotal += $item->getQty();
                }
            }
            if (!$qtyTotal) {
                continue;
            }
            if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_SHIPPING, $quote->getStoreId())) {
                $rowTotal += $address->getBaseShippingAmount();
            }
            $points += $this->calcSalesPoints(
                $rule->getSimpleAction(),
                $rule->getPointsEarned(),
                $rule->getMoneyStep(),
                $rowTotal,
                $rule->getQtyStep(),
                $qtyTotal,
                $rule->getMaxPointsEarned()
            );
            if ($points && $rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $points;
    }
}
