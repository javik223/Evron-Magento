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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Block
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Block_Webpos extends Mage_Checkout_Block_Onepage_Abstract {

    var $configData = array();

    public function __construct() {
        $this->configData = $this->_getConfigData();
		Mage::getSingleton('webpos/session')->setWebposCash(null);
        //set default shipping && payment method 
		// $this->_setDefaultShippingMethod();
        //$this->_setDefaultPaymentMethod();
    }

    protected function _setDefaultShippingMethod() {
        $shipping_address = $this->getOnepage()->getQuote()->getShippingAddress();
        $shipping_method = $shipping_address->getShippingMethod();
        if (!$shipping_method || $shipping_method == '') {
            //set default shipping method
            $default_shipping_method = $this->configData['default_shipping'];
            if ($default_shipping_method != '') {
                //Mage::helper('onestepcheckout')->saveShippingMethod($default_shipping_method);
                $this->getOnePage()->getQuote()->getShippingAddress()->setShippingMethod($default_shipping_method);
            } else {
                // if no default shipping method and only one shipping method is available, set it as default
                if ($method = $this->hasOnlyOneShippingMethod()) {
                    //Mage::helper('onestepcheckout')->saveShippingMethod($method);
                    $this->getOnePage()->getQuote()->getShippingAddress()->setShippingMethod($method);
                }
            }
        }
        $this->getOnePage()->getQuote()->collectTotals()->save();
    }

    /*
     * set default payment method
     */

    protected function _setDefaultPaymentMethod() {
        $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
        if (!$paymentMethod || $paymentMethod == '') {
            $default_payment_method = $this->configData['default_payment'];
            if ($default_payment_method != '') {
                $payment = array('method' => $default_payment_method);
                try {
                    Mage::helper('webpos')->savePaymentMethod($payment);
                } catch (Exception $e) {
                    // ignore error
                }
            } else {
                
            }
        }
    }

    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _getConfigData() {
        return Mage::helper('webpos')->getConfigData();
    }

    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function isVirtual() {
        return $this->getQuote()->isVirtual();
    }

    public function hasOnlyOneShippingMethod() {
        $rates = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRatesCollection();
        $rateCodes = array();
        foreach ($rates as $rate) {
            if (!in_array($rate->getCode(), $rateCodes)) {
                $rateCodes[] = $rate->getCode();
            }
        }
        if (count($rateCodes) == 1) {
            return $rateCodes[0];
        }
        return false;
    }

    public function isAjaxBillingField($field_name) {
        $fields = explode(',', $this->configData['ajax_fields']);
        if (in_array($field_name, $fields)) {
            return true;
        }
        return false;
    }

    public function isShowShippingAddress() {
        if ($this->getOnepage()->getQuote()->isVirtual()) {
            return false;
        }
        if ($this->configData['show_shipping_address']) {
            return true;
        }
        return false;
    }

    public function getBillingAddress() {
        return $this->getQuote()->getBillingAddress();
    }

    public function getCountryHtmlSelect($type) {
        if ($type == 'billing') {
            $address = $this->getQuote()->getBillingAddress();
        } else {
            $address = $this->getQuote()->getShippingAddress();
        }

        $countryId = $address->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('webpos/general/country_id', Mage::app()->getStore(true)->getId());
        }
        $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type . '[country_id]')
                ->setId($type . ':country_id')
                ->setTitle(Mage::helper('webpos')->__('Country'))
                ->setClass('validate-select')
                ->setValue($countryId)
                ->setOptions($this->getCountryOptions())
                ->setExtraParams('style="width:135px"');

        return $select->getHtml();
    }

    public function getShippingAddress() {
        if (!$this->isCustomerLoggedIn()) {
            return $this->getQuote()->getShippingAddress();
        } else {
            return Mage::getModel('sales/quote_address');
        }
    }

    public function clearCache() {
        if (Mage::getModel('checkout/session')->getData('rewardpoints_customerid')) {
            Mage::getModel('checkout/session')->unsetData('rewardpoints_customerid');
        }
        if (Mage::getModel('checkout/session')->getData('reward_sales_rules')) {
            Mage::getModel('checkout/session')->unsetData('reward_sales_rules');
        }
        if (Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
            Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
        }
    }

    //Show point earn/spend rule on checkout cart
    public function isShowRedeemRulesWebpos($product) {
        if (!$product) {
            return false;
        }
        if ($product->isGrouped()) {
            return false;
        }
        if ($product->getFinalPrice() < 0.0001) {
            return false;
        }
        if (!Mage::helper('customer')->isLoggedIn())//Mage::getSingleton('checkout/session')->getData('webpos_customerid'))
            return false;
        return true;
    }

    public function getSpendingRulesWebpos($product) {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return array();
        }
        if (!$product) {
            return array();
        }
        $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('checkout/session')->getData('webpos_customerid'));
        return Mage::helper('rewardpointsrule/calculation_spending')->getProductSpendingRules($product, $customer->getGroupId(), $customer->getWebsiteId());
    }

    public function getMinRulePoint($rule, $product) {
        if ($rule->getSimpleAction() == 'fixed') {
            return (int) $rule->getPointsSpended();
        }
        $price = $product->getFinalPrice();
        if ($rule->getMoneyStep() < 0.0001) {
            $minPoins = 0;
        }else {
            $minPoins = floor($price / $rule->getMoneyStep()) * $rule->getPointsSpended();
        }
        if ($rule->getMaxPointsSpended() && $minPoins > $rule->getMaxPointsSpended()) {
            $minPoins = $rule->getMaxPointsSpended();
        }
        return (int) $minPoins;
    }

    /**
     * get JSON string used for JS
     * 
     * @param array $rules
     * @return string
     */
    public function getProductRulesJson($rules = null, $customer = null, $product, $item_id)
    {
        if (is_null($rules)) {
            //$rules = $this->getSpendingRules();
            return array();
        }
        if ($customer == null)
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('checkout/session')->getData('webpos_customerid'));
        $result = array();
        foreach ($rules as $rule) {
            $ruleOptions = array();
            if ($customer) {
                $minPoins = $this->getMinRulePoint($rule, $product);
                //if($minPoins < 0) continue;
                $customerPoint = $this->getCustomerPoint($customer, $item_id);
                $totalPoints = Mage::helper('rewardpoints/customer')->getAccountByCustomer($customer)->getPointBalance();
                $minRedeem = (int) Mage::getStoreConfig(
                                Magestore_RewardPoints_Helper_Customer::XML_PATH_REDEEMABLE_POINTS
                );
                if ($customerPoint < $minPoins || ($minRedeem && $totalPoints < $minRedeem)) {
                    $ruleOptions['optionType'] = 'needPoint';
                    $ruleOptions['needPoint'] = max($minPoins - $customerPoint, $minRedeem - $totalPoints);
                } else {
                    $price = $product->getFinalPrice();
                    $sliderOption = array(
                        'minPoints' => $minPoins,
                        'pointStep' => $minPoins,
                    );
                    $ruleDiscount = $this->getRuleDiscount($rule, $product);
                   // if($minPoins == 0) $ruleDiscount = 0; /*Hai.Tran*/
                    if ($ruleDiscount < 0.0001) {
                        $ruleOptions['optionType'] = 'static';
                        $ruleOptions['stepDiscount'] = 0;
                    } else {
                        $ruleOptions['stepDiscount'] = Mage::app()->getStore()->convertPrice($ruleDiscount);
                        $ruleOptions['optionType'] = 'slider';
                        $maxPoints = $customerPoint;
                        $zMaxPoints = ceil($price / $ruleDiscount) * $sliderOption['pointStep'];
                        if ($maxPoints >= $zMaxPoints) {
                            $maxPoints = $zMaxPoints;
                        } else {
                            $maxPoints = floor($maxPoints / $sliderOption['pointStep']) * $sliderOption['pointStep'];
                        }
                        if ($timeUses = (int) $rule->getUsesPerProduct()) {
                            $zMaxPoints = $timeUses * $sliderOption['pointStep'];
                            if ($maxPoints > $zMaxPoints) {
                                $maxPoints = $zMaxPoints;
                            }
                        }
                        if ($maxPoints == $sliderOption['pointStep']) {
                            $ruleOptions['optionType'] = 'static';
                        } else {
                            $sliderOption['maxPoints'] = (int) $maxPoints;
                        }
                    }
                    $ruleOptions['sliderOption'] = $sliderOption;
                }
            } else {
                $ruleOptions['optionType'] = 'login';
            }
            $result[$rule->getId()] = $ruleOptions;
        }
        return Mage::helper('core')->jsonEncode($result);
    }
     public function getCustomerPoint($customer, $id){
        if ($this->hasData('customer_point'.$id)) {
            return $this->getData('customer_point'.$id);
        }
        $rewardAccount = Mage::helper('rewardpoints/customer')->getAccountByCustomer($customer);
        $points = $rewardAccount->getPointBalance();
        $points -= Mage::helper('rewardpointsrule/calculation_spending')->getCatalogSpendingPoints();
        if ($points < 0) {
            $points = 0;
        }
        $session = Mage::getSingleton('checkout/session');
        $shopingCartSpending = $session->getRewardSalesRules();
        if (is_array($shopingCartSpending)) $points -= $shopingCartSpending['use_point'];
        $points -= Mage::helper('rewardpointsrule/calculation_spending')->getCheckedRulePointWithout();
        
        $catalogRules = $session->getCatalogRules();
        $pointTemp = 0;
        if (isset($catalogRules[$id])) {
            $pointTemp = $catalogRules[$id]['point_used'] * $catalogRules[$id]['item_qty'];
        }

        $this->setData('customer_point'.$id, $points + $pointTemp);
        return $this->getData('customer_point'.$id);
    }

    public function getRuleDiscount($rule, $product) {
        $price = $product->getPrice();
        return Mage::helper('rewardpointsrule/calculation_spending')->getCatalogRuleDiscount($rule, $price);
    }
	
	/* Updated by Daniel - 24122014 */
	public function getAddress()
    {
        if ($this->isCustomerLoggedIn()){
            $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
            if ($customerAddressId){
                $billing = Mage::getModel('customer/address')->load($customerAddressId);
            }else{
                $billing = $this->getQuote()->getBillingAddress();
            }
            if(!$billing->getCustomerAddressId()){
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $default_address = $customer -> getDefaultBillingAddress();
                 if ($default_address) {
                    if ($default_address->getId()) {
                        if ($default_address->getPrefix()) {
                            $billing->setPrefix($default_address->getPrefix());
                        }
                        if ($default_address->getData('firstname')) {
                            $billing->setData('firstname', $default_address->getData('firstname'));
                        }
                        if ($default_address->getData('middlename')) {
                            $billing->setData('middlename', $default_address->getData('middlename'));
                        }if ($default_address->getData('lastname')) {
                            $billing->setData('lastname', $default_address->getData('lastname'));
                        }if ($default_address->getData('suffix')) {
                            $billing->setData('suffix', $default_address->getData('suffix'));
                        }if ($default_address->getData('company')) {
                            $billing->setData('company', $default_address->getData('company'));
                        }if ($default_address->getData('street')) {
                            $billing->setData('street', $default_address->getData('street'));
                        }if ($default_address->getData('city')) {
                            $billing->setData('city', $default_address->getData('city'));
                        }if ($default_address->getData('region')) {
                            $billing->setData('region', $default_address->getData('region'));
                        }if ($default_address->getData('region_id')) {
                            $billing->setData('region_id', $default_address->getData('region_id'));
                        }if ($default_address->getData('postcode')) {
                            $billing->setData('postcode', $default_address->getData('postcode'));
                        }if ($default_address->getData('country_id')) {
                            $billing->setData('country_id', $default_address->getData('country_id'));
                        }if ($default_address->getData('telephone')) {
                            $billing->setData('telephone', $default_address->getData('telephone'));
                        }if ($default_address->getData('fax')) {
                            $billing->setData('fax', $default_address->getData('fax'));
                        }
                        $billing->setCustomerAddressId($default_address->getId())
                                ->save();
                    }
                } else {
                    return $billing;
                }
            }
            return $billing;
        } else {
            return Mage::getModel('sales/quote_address');
        }
    }
	
	public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                        'value'=>$address->getId(),
                        'label'=>$address->format('oneline')
                );
            }
            $addressId = $this->getAddress()->getId();
            $shippingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
            if ($shippingAddressId != $addressId && $type == 'shipping'){
                $addressId = $shippingAddressId;
            }
            if (empty($addressId)) {
                if ($type=='billing') {
                        $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                        $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                        $addressId = $address->getId();
                }
            }
            $select = $this->getLayout()->createBlock('core/html_select')
                            ->setName($type.'_address_id')
                            ->setId($type.'-address-select')
                            ->setClass('address-select')
                            ->setExtraParams('style="width:100%"')
                            ->setValue($addressId)
                            ->setOptions($options);
            $select->addOption('', Mage::helper('checkout')->__('New Address'));
            return $select->getHtml();
        }
        return '';
    }
	/* end */
}
