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
 * @package     Magestore_WebPOS
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * WebPOS Rewrite Quote Discount Model
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount
{
    protected function _getWebposSession(){
         return Mage::getSingleton('webpos/session');
    }
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        /*session data*/
        $customDiscount = $this->_getWebposSession()->getCustomDiscount();
        $type = $this->_getWebposSession()->getType();
        $discountValue = $this->_getWebposSession()->getDiscountValue();
        $discountName = $this->_getWebposSession()->getDiscountName();
        if($customDiscount == 'true'){  // not apply coupon
            if($type == 'true') // percent
            {
                $quote->setWebposDiscountAmount(0)
                ->setWebposDiscountPercent($discountValue)
                ->setWebposDiscountDesc($discountName);
            }
            else{
                $quote->setWebposDiscountAmount($discountValue)
                ->setWebposDiscountPercent(0)
                ->setWebposDiscountDesc($discountName);
            }
        }
        /**/
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        if ($quote->getWebposDiscountAmount() < 0.0001
            && $quote->getWebposDiscountPercent() < 0.0001
        ) {
            return $this;
        }
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
		$showItemPriceInclTax = Mage::getStoreConfig('tax/cart_display/price');
        if ($quote->getWebposDiscountAmount() < 0.0001) {
            // Percent discount
            foreach ($items as $item) {
                if ($item->getParentItemId()) continue;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
						$itemPrice = ($showItemPriceInclTax != 1) ? $child->getPriceInclTax() : $child->getPrice();
                        $discount = $child->getQty() * $itemPrice * $quote->getWebposDiscountPercent() / 100;
                        $discount = min($discount, $child->getQty() * $itemPrice - $child->getDiscountAmount());
                        $discount = $quote->getStore()->roundPrice($discount);
                        $baseDiscount = $discount / $quote->getStore()->convertPrice(1);

                        $child->setDiscountAmount($child->getDiscountAmount() + $discount)
                            ->setBaseDiscountAmount($child->getBaseDiscountAmount() + $baseDiscount);

                        $this->_addAmount(-$discount);
                        $this->_addBaseAmount(-$baseDiscount);
                    }
                } else {
					$itemPrice = ($showItemPriceInclTax != 1) ? $item->getPriceInclTax() : $item->getPrice();
                        
                    $discount = $item->getQty() * $itemPrice * $quote->getWebposDiscountPercent() / 100;
                    $discount = min($discount, $item->getQty() * $itemPrice - $item->getDiscountAmount());
                    
                    $discount = $quote->getStore()->roundPrice($discount);
                    $baseDiscount = $discount / $quote->getStore()->convertPrice(1);
                    
                    $item->setDiscountAmount($item->getDiscountAmount() + $discount)
                        ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseDiscount);
                    
                    $this->_addAmount(-$discount);
                    $this->_addBaseAmount(-$baseDiscount);
                }
            }
            if ($address->getShippingAmount()) {
                $discount = $address->getShippingAmount() * $quote->getWebposDiscountPercent() / 100;
                $discount = min($discount, $address->getShippingAmount() - $address->getShippingDiscountAmount());
                
                $discount = $quote->getStore()->roundPrice($discount);
                $baseDiscount = $discount / $quote->getStore()->convertPrice(1);
                
                $address->setShippingDiscountAmount($address->getShippingDiscountAmount() + $discount)
                    ->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount() + $baseDiscount);
                
                $this->_addAmount(-$discount);
                $this->_addBaseAmount(-$baseDiscount);
            }
            $this->_addCustomDiscountDescription($address);
            return $this;
        }
        
        // Calculate items total
        $itemsPrice = 0;
        foreach ($items as $item) {
			$item_price = ($showItemPriceInclTax != 1) ? $item->getPriceInclTax() : $item->getPrice();
            if ($item->getParentItemId()) continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
					$child_price = ($showItemPriceInclTax != 1) ? $child->getPriceInclTax() : $child->getPrice();
             
                    $itemsPrice += $item->getQty() * ($child->getQty() * $child_price - $child->getDiscountAmount());
                }
            } else {
                $itemsPrice += $item->getQty() * $item_price - $item->getDiscountAmount();
            }
        }
        $itemsPrice += $address->getShippingAmount() - $address->getShippingDiscountAmount();
        if ($itemsPrice < 0.0001) {
            return $this;
        }
        
        // Calculate custom discount for each item
        $rate = $quote->getWebposDiscountAmount() / $itemsPrice;
        if ($rate > 1) $rate = 1;
        foreach ($items as $item) {
			$item_price = ($showItemPriceInclTax != 1) ? $item->getPriceInclTax() : $item->getPrice();
            
            if ($item->getParentItemId()) continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
					$child_price = ($showItemPriceInclTax != 1) ? $child->getPriceInclTax() : $child->getPrice();
            
                    $discount = $rate * ($child->getQty() * $child_price - $child->getDiscountAmount());
                    
                    $discount = $quote->getStore()->roundPrice($discount);
                    $baseDiscount = $discount / $quote->getStore()->convertPrice(1);
                    
                    $child->setDiscountAmount($child->getDiscountAmount() + $discount)
                        ->setBaseDiscountAmount($child->getBaseDiscountAmount() + $baseDiscount);

                    $this->_addAmount(-$discount);
                    $this->_addBaseAmount(-$baseDiscount);
                }
            } else {
                $discount = $rate * ($item->getQty() * $item_price - $item->getDiscountAmount());
                
                $discount = $quote->getStore()->roundPrice($discount);
                $baseDiscount = $discount / $quote->getStore()->convertPrice(1);
                
                $item->setDiscountAmount($item->getDiscountAmount() + $discount)
                    ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseDiscount);
                
                $this->_addAmount(-$discount);
                $this->_addBaseAmount(-$baseDiscount);
            }
        }
        if ($address->getShippingAmount()) {
            $discount = $rate * ($address->getShippingAmount() - $address->getShippingDiscountAmount());
            
            $discount = $quote->getStore()->roundPrice($discount);
            $baseDiscount = $discount / $quote->getStore()->convertPrice(1);
            
            $address->setShippingDiscountAmount($address->getShippingDiscountAmount() + $discount)
                ->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount() + $baseDiscount);
            
            $this->_addAmount(-$discount);
            $this->_addBaseAmount(-$baseDiscount);
        }
        $this->_addCustomDiscountDescription($address);
        return $this;
    }
    
    protected function _addCustomDiscountDescription($address)
    {
        $description = $address->getDiscountDescriptionArray();
        $label = $address->getQuote()->getWebposDiscountDesc();
        if (!$label) {
            $label = Mage::helper('webpos')->__('Custom Discount');
        }
        $description[0] = $label;
        
        $address->setDiscountDescriptionArray($description);
        $this->_calculator->prepareDescription($address);
    }
}
