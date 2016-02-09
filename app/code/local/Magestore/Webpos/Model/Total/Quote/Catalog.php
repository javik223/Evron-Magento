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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Collect point spent by catalog rules
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Total_Quote_Catalog extends Magestore_RewardPointsRule_Model_Total_Quote_Catalog
{
    public function __construct()
    {
		$this->setCode('rewardpoints_catalog');
	}
	
	public function collect(Mage_Sales_Model_Quote_Address $address)
    {
		$quote = $address->getQuote();
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        
		$session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            $catalogRules = array();
        }
        $newCatalogRules = array();
        
        $customerPoints = Mage::helper('rewardpoints/customer')->getAccountByCustomerId($session->getData('webpos_customerid'))->getPointBalance(); /* Hai.Tran 23.10.2013 */
        if ($customerPoints < (int)Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Customer::XML_PATH_REDEEMABLE_POINTS,
            $quote->getStoreId()
        )) {
            $session->setCatalogRules($newCatalogRules);
            return $this;
        }
        
        $helper = Mage::helper('rewardpointsrule/calculation_spending');
        /* @var $helper Magestore_RewardPointsRule_Helper_Calculation_Spending */
        $usePoint           = 0;
        $baseDiscount       = 0;
        $maxPointPerOrder   = $helper->getMaxPointsPerOrder($quote->getStoreId());
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if (!isset($catalogRules[$item->getId()])) {
                continue;
            }
            $rulePoints = $catalogRules[$item->getId()];
            if ($customerPoints < $usePoint + $item->getQty() * $rulePoints['point_used']) {
                $session->addError($helper->__('Not enough points to use the full catalog rule.'));
                break;
            }
            if ($maxPointPerOrder && $maxPointPerOrder < $usePoint + $item->getQty() * $rulePoints['point_used']) {
                $session->addError($helper->__('Maximum points allowed to spend for an order is %s', $maxPointPerOrder));
                break;
            }
            $product = $item->getProduct();
            if (!$product) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }
            $rule = $helper->getCatalogRule($rulePoints['rule_id']);
            if (!$rule->getId() || !$rule->validate($product)) {
                continue;
            }
            $points = $rulePoints['point_used'];
            
            $baseRuleDiscount = $helper->getCatalogDiscount($rule, $product, $points);
            
            $itemBaseDiscount   = $item->getQty() * $baseRuleDiscount;
            $itemDiscount       = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
            
            $item->setRewardpointsBaseDiscount($itemBaseDiscount)
                ->setRewardpointsDiscount($itemDiscount)
                ->setRewardpointsSpent($item->getQty() * $points);
            
            $baseDiscount += $itemBaseDiscount;
            $usePoint     += $item->getQty() * $points;
            
            $newCatalogRules[$item->getId()] = array(
                'item_id'   => $item->getId(),
                'item_qty'  => $item->getQty(),
                'rule_id'   => $rule->getId(),
                'point_used'    => $points,
                'base_point_discount'   => $baseRuleDiscount,
                'point_discount'    => Mage::app()->getStore()->convertPrice($baseRuleDiscount),
                'type'      => 'catalog_spend',
            );
        }
        $session->setCatalogRules($newCatalogRules);
        if ($baseDiscount) {
            $discount = Mage::app()->getStore()->convertPrice($baseDiscount);
            
            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
            $address->setGrandTotal($address->getGrandTotal() - $discount);
            
            $address->setRewardpointsSpent($usePoint);
            $address->setRewardpointsBaseDiscount($baseDiscount);
            $address->setRewardpointsDiscount($discount);
        }
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
	}
}
