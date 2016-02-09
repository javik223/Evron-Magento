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
 * Webpos Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_IndexController extends Magestore_Webpos_Controller_Action {

    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function updateAttribute($attribute, $option) {
        $attributeObject = Mage::getSingleton('eav/config')->getAttribute('customer', $attribute);
        $valueConfig = array(
            '' => array('is_required' => 0, 'is_visible' => 0),
            'opt' => array('is_required' => 0, 'is_visible' => 1),
            '1' => array('is_required' => 0, 'is_visible' => 1),
            'req' => array('is_required' => 1, 'is_visible' => 1),
        );
        $data = $valueConfig[$option];
        $attributeObject->setData('is_required', $data['is_required']);
        $attributeObject->setData('is_visible', $data['is_visible']);
        $attributeObject->save();
    }

    public function enableCustomerFields() {
        $helper = Mage::helper('webpos');
        $prefix = 0;
        $suffix = 0;
        $middlename = 0;
        $birthday = 0;
        $gender = 0;
        $taxvat = 0;
        for ($i = 0; $i < 20; $i++) {
            if ($helper->getFieldEnable($i) == 'prefix')
                $prefix = 1;
            if ($helper->getFieldEnable($i) == 'suffix')
                $suffix = 1;
            if ($helper->getFieldEnable($i) == 'middlename')
                $middlename = 1;
            if ($helper->getFieldEnable($i) == 'birthday')
                $birthday = 1;
            if ($helper->getFieldEnable($i) == 'gender')
                $gender = 1;
            if ($helper->getFieldEnable($i) == 'taxvat')
                $taxvat = 1;
        }
        try {
            if ($prefix == 1) {
                if ($helper->getFieldRequire('prefix')) {
                    Mage::getConfig()->saveConfig('customer/address/prefix_show', 'req');
                    $this->updateAttribute('prefix', 'reg');
                } else {
                    Mage::getConfig()->saveConfig('customer/address/prefix_show', 'opt');
                    $this->updateAttribute('prefix', 'opt');
                }
            }
            if ($suffix == 1) {
                if ($helper->getFieldRequire('suffix')) {
                    Mage::getConfig()->saveConfig('customer/address/suffix_show', 'req');
                    $this->updateAttribute('suffix', 'req');
                } else {
                    Mage::getConfig()->saveConfig('customer/address/suffix_show', 'opt');
                    $this->updateAttribute('suffix', 'opt');
                }
            }
            if ($middlename == 1) {
                Mage::getConfig()->saveConfig('customer/address/middlename_show', '1');
                $this->updateAttribute('middlename', '1');
            }
            if ($birthday == 1) {
                if ($helper->getFieldRequire('birthday')) {
                    Mage::getConfig()->saveConfig('customer/address/dob_show', 'req');
                    $this->updateAttribute('dob', 'req');
                } else {
                    Mage::getConfig()->saveConfig('customer/address/dob_show', 'opt');
                    $this->updateAttribute('dob', 'opt');
                }
            }
            if ($gender == 1) {
                if ($helper->getFieldRequire('gender')) {
                    Mage::getConfig()->saveConfig('customer/address/gender_show', 'req');
                    $this->updateAttribute('gender', 'req');
                } else {
                    Mage::getConfig()->saveConfig('customer/address/gender_show', 'opt');
                    $this->updateAttribute('gender', 'opt');
                }
            }
            if ($taxvat == 1) {
                if ($helper->getFieldRequire('taxvat')) {
                    Mage::getConfig()->saveConfig('customer/address/taxvat_show', 'req');
                    $this->updateAttribute('taxvat', 'req');
                } else {
                    Mage::getConfig()->saveConfig('customer/address/taxvat_show', 'opt');
                    $this->updateAttribute('taxvat', 'opt');
                }
            }
        } catch (Exception $e) {
            
        }
    }

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $wid = $this->getRequest()->getParam('warehouse_id');
        if ($wid) {
            Mage::getSingleton('core/session')->setCurrentWarehouseId($wid);
        }
        $this->loadLayout();
        $this->_initLayoutMessages('core/session');
        $this->getLayout()->getBlock('head')->setTitle('Web POS');
        $this->renderLayout();
    }

    public function productSearchAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $storeId = Mage::app()->getStore()->getStoreId();
        $keyword = $this->getRequest()->getPost('keyword');
        $productBlock = Mage::getBlockSingleton('catalog/product_list');
        $result = array();
        $showOutofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock', $storeId);
        //search by SKU
        $productSkus = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->setStoreId($storeId)
                ->addStoreFilter($storeId)
                ->addFieldToFilter("status", 1)
                ->addFieldToFilter('sku', array('like' => '%' . $keyword . '%'))
                ->setCurPage(1)
        ;

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($productSkus);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productSkus);
        /* filter product is in stock */
        if (!$showOutofstock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productSkus);
        /* end */

        //search by name
        $productNames = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->setStoreId($storeId)
                ->addStoreFilter($storeId)
                ->addFieldToFilter("status", 1)
                ->addFieldToFilter('name', array('like' => '%' . $keyword . '%'))
                ->setCurPage(1)
        ;
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($productNames);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productNames);
        /* filter product is in stock */
        if (!$showOutofstock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productNames);
        /* end */

        $html = '';
        $html .= '<ul>';

        if (count($productSkus)) {
            foreach ($productSkus as $product) {
                if (!in_array($product->getId(), $result)) {
                    $addToCart = $productBlock->getAddToCartUrl($product) . 'tempadd/1';
                    $result[] = $product->getId();
                    $html .= '<li onclick="setLocation(\'' . $addToCart . '\')">';
                    $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
                    $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
                    if ($showOutofstock) {
                        $html .= '<br />';
                        if ($product->isAvailable()) {
                            $html .= '<p class="availability in-stock">' . $this->__('Availability:') . '<span>' . $this->__('In stock') . '</span></p><div style="clear:both"></div>';
                        } else {
                            $html .= '<p class="availability out-of-stock">' . $this->__('Availability:') . '<span>' . $this->__('Out of stock') . '</span></p><div style="clear:both"></div>';
                        }
                    }
                    $html .= '</li>';
                }
            }
        }
        if (count($productNames)) {
            foreach ($productNames as $product) {
                if (!in_array($product->getId(), $result)) {
                    $addToCart = $productBlock->getAddToCartUrl($product) . 'tempadd/1';
                    $result[] = $product->getId();
                    $html .= '<li onclick="setLocation(\'' . $addToCart . '\')">';
                    $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
                    $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
                    if ($showOutofstock) {
                        $html .= '<br />';
                        if ($product->isAvailable()) {
                            $html .= '<p class="availability in-stock">' . $this->__('Availability:') . '<span>' . $this->__('In stock') . '</span></p><div style="clear:both"></div>';
                        } else {
                            $html .= '<p class="availability out-of-stock">' . $this->__('Availability:') . '<span>' . $this->__('Out of stock') . '</span></p><div style="clear:both"></div>';
                        }
                    }
                    $html .= '</li>';
                }
            }
        }

        //ONLY RESULT
        $productSkuOnly = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->setStoreId($storeId)
                ->addStoreFilter($storeId)
                ->addFieldToFilter("status", 1)
                ->addFieldToFilter('sku', $keyword)
                ->setCurPage(1)
        ;
        /* filter product is in stock */
        if (!$showOutofstock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productSkuOnly);
        /* end */

        if (count($productSkuOnly) == 1 && count($productSkus) <= 1 && count($productNames) <= 1) {
            foreach ($productSkuOnly as $product) {
                $addToCart = $productBlock->getAddToCartUrl($product) . 'tempadd/1';
                $result[] = $product->getId();
                $html = '';
                $html .= '<ul>';
                $html .= '<li id="sku_only" url="' . $addToCart . '" onclick="setLocation(\'' . $addToCart . '\')">';
                $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
                $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
                if ($showOutofstock) {
                    $html .= '<br />';
                    if ($product->isAvailable()) {
                        $html .= '<p class="availability in-stock">' . $this->__('Availability:') . '<span>' . $this->__('In stock') . '</span></p><div style="clear:both"></div>';
                    } else {
                        $html .= '<p class="availability out-of-stock">' . $this->__('Availability:') . '<span>' . $this->__('Out of stock') . '</span></p><div style="clear:both"></div>';
                    }
                }
                $html .= '</li>';
                $html .= '</ul>';
            }
            echo $html;
            return;
        }

        $html .= '</ul>';
        if (!count($productSkus) && !count($productNames)) {
            $html = '<ul><li>No product</li></ul>';
        }
        echo $html;
    }

    public function save_customer_existAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $customerId = $this->getRequest()->getParam('customerId');
        if ($customerId) {
            /* save customer exist */
            $session = Mage::getModel('checkout/session');
            try {
                $oldcustomerId = $session->getData('webpos_customerid');
                if ($oldcustomerId != $customerId) {
                    $cookiesData = Mage::helper('webpos')->getWebPosCookies();
                    if (isset($cookiesData['websiteid'])) {
                        $website = Mage::app()->getWebsite($cookiesData['websiteid']);
                        $mageRunCode = $website->getData('code');
                        $mageRunType = 'website';
                    }
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    Mage::getModel('customer/session')->setCustomerAsLoggedIn($customer);
                }
            } catch (Exception $e) {
                
            }
            /* end */
            if ($this->_isLoggedIn()) {
                $session->setData('webpos_customerid', $customerId);
                $session->setData('rewardpoints_customerid', $customerId);
            } else {
                $session->unsetData('webpos_customerid');
                $session->unsetData('rewardpoints_customerid');
            }

            if ($session->getData('reward_sales_rules')) {
                $session->unsetData('reward_sales_rules');
            }
            if (Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
                Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
            }
        } else {
            /* reload customer address */
            if ($customerId == 0) {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                Mage::getModel('customer/session')->setCustomerAsLoggedIn($customer);
            }
            Mage::getModel('checkout/session')->unsetData('webpos_customerid');
            Mage::getModel('checkout/session')->unsetData('rewardpoints_customerid');
            /* end */
        }

        /* reload customer address */
        $this->loadLayout(false);
        $this->renderLayout();
        /* end */
    }

    public function customerSearchAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $storeId = Mage::app()->getStore(true)->getId();
        $keyword = $this->getRequest()->getParam('keyword');

        $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        $customers = null;
        if (is_numeric($keyword)) {
            // Search by ID
            $customers = Mage::getModel('customer/customer')->load($keyword);
        }
        if (!$customers || ($customers && !$customers->getId())) {

            // Search by name, fullname, email
            $fullkey = strtolower(str_replace(' ', '', $keyword));
            $result = array();

            /* Daniel - get store config - share customer account */
            $accountShare = Mage::getStoreConfig('customer/account_share/scope', $storeId);

            /*   old code     
              $customers = Mage::getResourceModel('customer/customer_collection')->addAttributeToFilter('website_id', $websiteId)->addAttributeToSelect('*')
             */
            /* Daniel - allow to search customer from any websites */
            $customers = Mage::getResourceModel('customer/customer_collection')->addAttributeToSelect('*')
                    ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                    ->addExpressionAttributeToSelect('full_name', 'LOWER(CONCAT({{firstname}},"",{{lastname}}))', array('firstname', 'lastname'))
                    ->addAttributeToFilter(array(
                        array('attribute' => 'firstname', 'like' => '%' . $keyword . '%'),
                        array('attribute' => 'lastname', 'like' => '%' . $keyword . '%'),
                        array('attribute' => 'telephone', 'like' => '%' . $keyword . '%'),
                        array('attribute' => 'email', 'like' => '%' . $keyword . '%'),
                        array('attribute' => 'full_name', 'like' => '%' . $fullkey . '%'),
                    ))
                    ->setOrder('full_name', 'ASC')
                    ->setPageSize(30)   //lam theo yeu cau cua Lap
                    ->load()
            ;

//            $customers->_toString();die();
        }

        $html = '';
        $html .= '<ul>';
        foreach ($customers as $customer) {

            $html .='<li onclick="addCustomerToCart(' . $customer->getId() . ');" style="width:100%; float:left; cursor: pointer" class="email-customer col-lg-6 col-md-6"><span style="float:left;">' . $customer->getFirstname() . ' ' . $customer->getLastname() . '</span><span style="float:right"><a href="#">' . $customer->getEmail() . '</a></span><br/><span style="float:right">' . $customer->getTelephone() . '</span></li>';

//            $html .= '</li>';
        }

        if (!count($customers)) {
            $html .= '<li>No customer</li>';
        }
        $html .= '</ul>';
        echo $html;
    }

    /**
     * Adam
     * @return type
     */
    public function addCustomerToCartAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $error = true;
        $result = array();
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();
        $customerId = $this->getRequest()->getParam('customer_id');

        if ($customerId) {
            $session = Mage::getModel('checkout/session');
            $posSession = Mage::getModel('webpos/session');
            $customerSession = Mage::getModel('customer/session');
            $customerModel = Mage::getModel('customer/customer');
            $customer = $customerModel->load($customerId);
            try {
                if ($customer && $customer->getId()) {
                    $customerSession->setCustomerAsLoggedIn($customer);
                    $posSession->setData('webpos_customerid', $customerId);

                    $unsetArr = array(
                        'entity_id',
                        'entity_type_id',
                        'attribute_set_id',
                        'increment_id',
                        'created_at',
                        'updated_at',
                        'prefix',
                        'is_active',
                        'increment_id',
                        'parent_id'
                    );
                    $sameAsBilling = true;
                    $shippingAddressId = $customer->getDefaultShipping();
                    if ($shippingAddressId) {
                        $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
                        $shipping_data = $shippingAddress->getData();
                        $shipping_street = array('0' => $shippingAddress->getStreet1(), '1' => $shippingAddress->getStreet2());
                        $shipping_data['street'] = $shipping_street;
                        foreach ($unsetArr as $key) {
                            unset($shipping_data[$key]);
                        }
                        if (!empty($shippingAddress)) {
                            $this->getOnepage()->saveShipping($shipping_data, $shippingAddressId);
                            $sameAsBilling = false;
                        }
                    }

                    $billingAddressId = $customer->getDefaultBilling();
                    if ($billingAddressId) {
                        $billingAddress = Mage::getModel('customer/address')->load($billingAddressId);
                        $billing_data = $billingAddress->getData();
                        $billing_street = array('0' => $billingAddress->getStreet1(), '1' => $billingAddress->getStreet2());
                        $billing_data['street'] = $billing_street;
                        foreach ($unsetArr as $key) {
                            unset($billing_data[$key]);
                        }
                        if (!empty($billingAddress)) {
                            $this->getOnepage()->saveBilling($billing_data, $billingAddressId);
                            if ($sameAsBilling)
                                $this->getOnepage()->saveShipping($billing_data, $billingAddressId);
                        }
                    }

                    $error = false;
                    $html = $this->__('Add customer completely');
                } else {
                    $error = true;
                    $html = $this->__('Customer is not existed');
                }
            } catch (Exception $e) {
                print_r($e->getMessage());
                die('x');
            }
        } else {
            $error = true;
            $html = $this->__('Customer is not existed');
        }
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();

        $result['error'] = $error;
        $result['html'] = $html;
        $result['customer_id'] = $customerId;
        $result['customer_name'] = $customer->getFirstname() . " " . $customer->getLastname();
        $result['customer_email'] = $customer->getEmail();
        /* S: Daniel - updated v2.5 */
        $productHelper = Mage::helper('webpos/product');
        $result['customerData'] = $productHelper->getCustomerInfoResponse($customer);
        /* E: Daniel - updated v2.5 */
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function removeCustomerFromCartAction() {
        $result = array();
        $customer = Mage::getModel('customer/customer')->setId(0);
        $quote = $this->getOnepage()->getQuote();
        $cart = Mage::getSingleton('checkout/cart');
        $session = Mage::getSingleton('checkout/session');
        $posSession = Mage::getSingleton('webpos/session');
        $error = true;
        try {
            $posSession->setData('webpos_customerid', null);
            $customerSession = Mage::getSingleton('customer/session');
            if ($customerSession->isLoggedIn()) {
                //Mage::helper('webpos')->deleteCustomerQuote($customerSession->getCustomerId());
                $customerSession->logout();
                if (version_compare(Mage::getVersion(), '1.7.0.0', '>'))
                    $customerSession->renewSession();
            }else {
                $allowGuestCheckout = Mage::helper('checkout')->isAllowedGuestCheckout($quote);
                if ($allowGuestCheckout)
                    $customerSession->setCustomerAsLoggedIn($customer);
            }
            $quote->setCustomer($customer)->save();
            Mage::helper('webpos')->getDefaultCustomer();
            $cart->save();
            $session->setCartWasUpdated(true);
            $result['error'] = false;
			/*
            if (Mage::helper('persistent/session')->isPersistent()) {
                Mage::helper('persistent/session')->getSession()->removePersistentCookie();
                Mage::getSingleton('persistent/observer')->setQuoteGuest();
            }
			*/
        } catch (Exception $e) {
            $result['error'] = true;
        }
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function save_addressAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $billing_data = $this->getRequest()->getPost('billing', false);
        $shipping_data = $this->getRequest()->getPost('shipping', false);
        $shipping_method = $this->getRequest()->getPost('shipping_method', false);
        $billing_address_id = $this->getRequest()->getPost('billing_address_id', false);

        if (isset($billing_data['use_for_shipping']) && $billing_data['use_for_shipping'] == '1') {
            $shipping_address_data = $billing_data;
        } else {
            $shipping_address_data = $shipping_data;
        }

        /* customize for load country ma khong dien day du thong tin */
        $quote = $this->getOnepage()->getQuote();
        $shipping = $quote->getShippingAddress();
        $billing = $quote->getBillingAddress();

        $billingCountryId = "";
        $billingRegionId = "";
        $billingZipcode = "";
        $billingRegion = "";
        $billingCity = "";

        if (isset($shipping_address_data['country_id']))
            $billingCountryId = $shipping_address_data['country_id'];
        if (isset($shipping_address_data['region_id']))
            $billingRegionId = $shipping_address_data['region_id'];
        if (isset($shipping_address_data['postcode']))
            $billingZipcode = $shipping_address_data['postcode'];
        if (isset($shipping_address_data['region']))
            $billingRegion = $shipping_address_data['region'];
        if (isset($shipping_address_data['city']))
            $billingCity = $shipping_address_data['city'];

        $this->getOnepage()->getQuote()->getShippingAddress()
                ->setCountryId($billingCountryId)
                ->setRegionId($billingRegionId)
                ->setPostcode($billingZipcode)
                ->setRegion($billingRegion)
                ->setCity($billingCity)
                ->setCollectShippingRates(true);

        $this->getOnepage()->getQuote()->save();
        $shipping->setSameAsBilling(true)->save();
        /* end customize */

        $billing_street = trim(implode("\n", $billing_data['street']));
        $shipping_street = trim(implode("\n", $shipping_address_data['street']));

        if (isset($billing_data['email'])) {
            $billing_data['email'] = trim($billing_data['email']);
        }

        // Ignore disable fields validation --- Only for 1..4.1.1

        /* Start: Modified by Daniel - 03/04/2015 - Improve Ajax speed */
        if (version_compare(Mage::getVersion(), '1.4.1.1', '<=')) {
            $this->setIgnoreValidation();
        }
        /* End: Modified by Daniel - 03/04/2015 - Improve Ajax speed */

        if (Mage::helper('webpos')->isShowShippingAddress()) {
            if (!isset($billing_data['use_for_shipping']) || $billing_data['use_for_shipping'] != '1') {
                $shipping_address_id = $this->getRequest()->getPost('shipping_address_id', false);
                $this->getOnepage()->saveShipping($shipping_data, $shipping_address_id);
            } else
                $this->getOnepage()->saveShipping($billing_data, $billing_address_id);
        } else
            $this->getOnepage()->saveShipping($billing_data, $billing_address_id);
        $this->getOnepage()->saveBilling($billing_data, $billing_address_id);

        /* new update - 24122014 */
        if (!$billing_address_id || $billing_address_id == '' || $billing_address_id == null) {
            if ($billing_data['country_id']) {
                Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->setData('country_id', $billing_data['country_id'])->save();
            }
        }
        /* end */

        // if different shipping address is enabled and customer ship to another address, save it
        if ($shipping_method && $shipping_method != '') {
            Mage::helper('webpos')->saveShippingMethod($shipping_method);
        }
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function save_shippingAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $shipping_method = $this->getRequest()->getPost('shipping_method', '');
        $payment_method = $this->getRequest()->getPost('payment_method', false);
        $old_shipping_method = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
        $billing_data = $this->getRequest()->getPost('billing', false);
        if ($billing_data['country_id']) {
            Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->setData('country_id', $billing_data['country_id'])->save();
        }
        // if ($shipping_method && $shipping_method != '' && $shipping_method != $old_shipping_method) {
        Mage::helper('webpos')->saveShippingMethod($shipping_method);
        $this->getOnepage()->saveShippingMethod($shipping_method);
        // }
        // if ($payment_method != '') {
        try {
            $payment = $this->getRequest()->getPost('payment', array());
            $payment['method'] = $payment_method;
            /* Start: Modified by Daniel - 03/04/2015 - improve ajax speed */
            /* $this->getOnepage()->savePayment($payment); */
            /* End: Modified by Daniel - 03/04/2015 - improve ajax speed */
            Mage::helper('webpos')->savePaymentMethod($payment);
        } catch (Exception $e) {
            //
        }
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function printInvoiceAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $cookie = Mage::getSingleton('core/cookie');
        $key = $cookie->get('webpos_admin_key');
        $code = $cookie->get('webpos_admin_code');
        $adminId = $cookie->get('webpos_admin_id');
        if (!$key || !$code || !$adminId)
            return $this;

        $this->loadLayout('print');
        $this->renderLayout();
    }

    public function show_term_conditionAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $helper = Mage::helper('webpos');
        if ($helper->enableTermsAndConditions()) {
            $html = $helper->getTermsConditionsHtml();
            echo $html;
            echo '<p class="a-right"><a href="#" onclick="javascript:TINY.box.hide();return false;">Close</a></p>';
        }
    }

    public function checkcartAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $cart = Mage::getSingleton('checkout/cart');
        if (!$cart->getQuote()->getItemsCount()) {
            echo 'noItem';
        } else {
            echo 'hasItem';
        }
    }

    public function orderlistSearchAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function viewOrderAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function savecashinAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $cashin = $this->getRequest()->getParam('cashin');
        Mage::getModel('checkout/session')->setData('webpos_cashin', $cashin);
    }

    public function reload_discountAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $discount = $this->getRequest()->getParam('custom-discount');
        if (!$discount) {
            $discount = 0;
        }
        $session = Mage::getSingleton('checkout/session');
        if ($discount > 0) {
            $session->setData('webpos_admin_discount', $discount);
        } else {
            $session->unsetData('webpos_admin_discount');
        }
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function add_couponAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $couponCode = (string) $this->getRequest()->getPost('coupon_code', '');
        $customerEmail = (string) $this->getRequest()->getPost('customerEmail', '');
        $customerExist = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customerEmail);
        $customerGuest = '';
        if ($customerExist && $customerExist->getId()) {
            $customerGuest = $customerExist->getId();
        }
        if ($customerEmail) {
            
        }
        $quote = $this->getOnepage()->getQuote();
        if ($this->getRequest()->getPost('remove') == 1) {
            $couponCode = '';
            if (Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
                Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
            }
        } else {
            if (Mage::getConfig()->getModuleConfig('Magestore_RewardPointsReferFriends')->is('active', 'true') && Mage::helper('rewardpointsreferfriends')->isEnable()) {
                if ($couponCode) {
                    $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByCoupon($couponCode);
                    $customerId = Mage::getSingleton('checkout/session')->getData('rewardpoints_customerid');
                    if (!$refer_cus || !$refer_cus->getId() || $refer_cus->getCustomerId() == $customerId || $customerGuest == $refer_cus->getCustomerId()) {
                        Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
                        if (!Mage::getSingleton('checkout/session')->getData('coupon_code'))
                            Mage::getSingleton('checkout/session')->setData('coupon_code', $couponCode);
                    } else {
//                        if ($refer_cus->getKey() == Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
//                            Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
//                        } else {
                        Mage::getSingleton('core/cookie')->set('rewardpoints_offer_key', $refer_cus->getKey());
//                        }
                    }
//                        Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
                }
            }
        }

//        $oldCouponCode = $quote->getCouponCode();
//        if (!strlen($couponCode) && !strlen($oldCouponCode) && !Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
//            return;
//        }
        try {
            $error = false;
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();

            if ($couponCode) {

                if ($couponCode == $quote->getCouponCode()) {
                    $message = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                } else {
                    $error = true;
                    if (count($quote->getAllItems()) == 0) {
                        $message = $this->__('Coupon code "%s" cannot be applied. Your shopping cart is empty!', Mage::helper('core')->htmlEscape($couponCode));
                    } else {
                        $message = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                    }
                    if (Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
                        $error = false;
                        $message = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                    }
                }
            } else {
                $message = $this->__('Coupon code was canceled.');
            }
        } catch (Mage_Core_Exception $e) {
            $error = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $error = true;
            $message = $this->__('Cannot apply the coupon code.');
        }
        //reload HTML for review order section
        $reviewHtml = $this->_getReviewTotalHtml();
        $result = array(
            'error' => $error,
            'message' => $message,
            'review_html' => $reviewHtml
        );
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function _getReviewTotalHtml() {
        //$this->_cleanLayoutCache();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('webpos_webpos_review');
        $layout->unsetBlock('shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    public function add_giftwrapAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $remove = $this->getRequest()->getPost('remove', false);
        $session = Mage::getSingleton('checkout/session');
        if (!$remove) {
            $session->setData('webpos_giftwrap', 1);
        } else {
            $session->unsetData('webpos_giftwrap');
            $session->unsetData('webpos_giftwrap_amount');
        }
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function reloadGiftwrapAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $helper = Mage::helper('webpos');
        $amount = $helper->getGiftwrapAmount();
        $giftwrapAmount = 0;
        // $freeBoxes = 0;
        if ($helper->getGiftwrapType() == 1) {
            $items = Mage::getSingleton('checkout/cart')->getItems();
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $giftwrapAmount += $amount * ($item->getQty());
            }
        } else
            $giftwrapAmount = $amount;

        $result = Mage::helper('checkout')->formatPrice($giftwrapAmount);
        if ($giftwrapAmount && $giftwrapAmount > 0)
            $result .= '<input id="hidden_price" type="hidden" value="' . $giftwrapAmount . '">';
        $this->getResponse()->setBody($result);
    }

    public function setIgnoreValidation() {
        $this->getOnepage()->getQuote()->getBillingAddress()->setShouldIgnoreValidation(true);
        $this->getOnepage()->getQuote()->getShippingAddress()->setShouldIgnoreValidation(true);
    }

    private function _emailIsRegistered($email_address) {
        /* Daniel - updated - allow to create account from other websites */
        if (Mage::getStoreConfig('customer/account_share/scope') == 1) {
            Mage::getModel('customer/session')->setData('checkvalidemail', true);
            $model = Mage::getModel('customer/customer');
            $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email_address);
            if ($model->getId()) {
                Mage::getModel('customer/session')->setData('checkvalidemail', false);
                return true;
            }
            Mage::getModel('customer/session')->setData('checkvalidemail', false);
            return false;
        } else {
            $model = Mage::getModel('customer/customer');
            $model->loadByEmail($email_address);
            if ($model->getId()) {
                if (Mage::getStoreConfig('customer/account_share/scope') == 0)
                    return true;
                return false;
            } else {
                return false;
            }
        }
        /* end */
    }

    public function isVirtual() {
        return $this->getOnepage()->getQuote()->isVirtual();
    }

    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    protected function _isLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function saveOrderAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $post = $this->getRequest()->getPost();
        if (!$post)
            return;
        $error = false;
        $helper = Mage::helper('webpos');

        $billing_data = $this->getRequest()->getPost('billing', array());
        $shipping_data = $this->getRequest()->getPost('shipping', array());
        $shippingDescription = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingDescription();

        $billing_data['save_in_address_book'] = '';
        /* Login for existing customer */

        if ($billing_data['account_type'] == 'exist') {
            if (isset($billing_data['email'])) {
                $email_address = $billing_data['email'];
                if ($this->_emailIsRegistered($email_address)) {
                    $customerExist = Mage::getModel('customer/customer')
                                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email_address);
                    Mage::getModel('customer/session')->setCustomerAsLoggedIn($customerExist);
                    /* save to address book */
                    $customBillingAddress = Mage::getModel('customer/address');
                    $customShippingAddress = Mage::getModel('customer/address');
                    if (isset($billing_data['save_in_address_book'])) {
                        $customBillingAddress->setData($billing_data)
                                ->setCustomerId($customerExist->getId())
                                ->setIsDefaultBilling('1')
                                ->setSaveInAddressBook('1');
                        try {
                            $customBillingAddress->save();
                        } catch (Exception $ex) {
                            
                        }
                    }
                    if (isset($shipping_data['save_in_address_book'])) {
                        $customShippingAddress->setData($shipping_data)
                                ->setCustomerId($customerExist->getId())
                                ->setIsDefaultShipping('1')
                                ->setSaveInAddressBook('1');
                        try {
                            $customShippingAddress->save();
                        } catch (Exception $ex) {
                            
                        }
                    }
                    /* end */
                }
            }
        }
        //set checkout method 
        $checkoutMethod = '';
        if (!Mage::getSingleton('customer/session')->isLoggedIn() && (!isset($billing_data['customer_id']) || $billing_data['customer_id'] == '' || $billing_data['customer_id'] == '0')) {
            $checkoutMethod = 'guest';
            $is_create_account = $this->getRequest()->getPost('create_account_checkbox');
            $email_address = $billing_data['email'];
            if ($is_create_account) {
                if ($this->_emailIsRegistered($email_address)) {
                    $error = true;
                    Mage::getSingleton('checkout/session')->addError(Mage::helper('webpos')->__('Email is already registered.'));
                    $this->_redirect('*/index/index');
                } else {
                    $billing_data['customer_password'] = Mage::helper('core')->uniqHash();
                    $billing_data['confirm_password'] = $billing_data['customer_password'];
                    $checkoutMethod = 'register';
                }
            }
        }

        if ($checkoutMethod != '') {
            $this->getOnepage()->saveCheckoutMethod($checkoutMethod);
        }

        //to ignore validation for disabled fields

        /* Start: Modified by Daniel - 03/04/2015 - Improve Ajax speed */
        if (version_compare(Mage::getVersion(), '1.4.1.1', '<=')) {
            $this->setIgnoreValidation();
        }
        /* End: Modified by Daniel - 03/04/2015 - Improve Ajax speed */

        //resave billing address to make sure there is no error if customer change something in billing section before finishing order
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $result = $this->getOnepage()->saveBilling($billing_data, $customerAddressId);
        if (isset($result['error'])) {
            $error = true;
            if (is_array($result['message']) && isset($result['message'][0]))
                Mage::getSingleton('checkout/session')->addError($result['message'][0]);
            else
                Mage::getSingleton('checkout/session')->addError($result['message']);
            $this->_redirect('*/index/index');
        }

        //re-save shipping address
        $shipping_address_id = $this->getRequest()->getPost('shipping_address_id', false);
        if ($helper->isShowShippingAddress()) {
            if (!isset($billing_data['use_for_shipping']) || $billing_data['use_for_shipping'] != '1') {
                $result = $this->getOnepage()->saveShipping($shipping_data, $shipping_address_id);
                if (isset($result['error'])) {
                    $error = true;
                    if (is_array($result['message']) && isset($result['message'][0]))
                        Mage::getSingleton('checkout/session')->addError($result['message'][0]);
                    else
                        Mage::getSingleton('checkout/session')->addError($result['message']);
                    $this->_redirect('*/index/index');
                }
            }
            else {
                $result['allow_sections'] = array('shipping');
                $result['duplicateBillingInfo'] = 'true';
            }
        }

        //re-save shipping method
        $shipping_method = $this->getRequest()->getPost('shipping_method', '');
        if (!$this->isVirtual()) {
            $result = $this->getOnepage()->saveShippingMethod($shipping_method);

            if (isset($result['error'])) {
                $error = true;
                if (is_array($result['message']) && isset($result['message'][0])) {
                    Mage::getSingleton('checkout/session')->addError($result['message'][0]);
                } else {
                    Mage::getSingleton('checkout/session')->addError($result['message']);
                }
                $this->_redirect('*/index/index');
            } else {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
            }
        }

        $paymentRedirect = false;
        //save payment method		
        try {
            $result = array();
            $payment = $this->getRequest()->getPost('payment', array());
            $result = $helper->savePaymentMethod($payment);
            if ($payment) {
                $this->getOnepage()->getQuote()->getPayment()->importData($payment);
            }
            $paymentRedirect = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

        if (isset($result['error'])) {
            $error = true;
            Mage::getSingleton('checkout/session')->addError($result['error']);
            $this->_redirect('*/index/index');
        }

        if ($paymentRedirect && $paymentRedirect != '') {
            Header('Location: ' . $paymentRedirect);
            exit();
        }

        //only continue to process order if there is no error
        if (!$error) {
            //newsletter subscribe
            if ($helper->isShowNewsletter()) {
                $news_billing = $this->getRequest()->getPost('billing');
                // $is_subscriber = $this->getRequest()->getPost('newsletter_subscriber_checkbox', false);	
                $is_subscriber = null;
                if (isset($news_billing['newsletter_subscriber_checkbox']))
                    $is_subscriber = $news_billing['newsletter_subscriber_checkbox'];
                if ($is_subscriber) {
                    $subscribe_email = '';
                    //pull subscriber email from billing data
                    if (isset($billing_data['email']) && $billing_data['email'] != '') {
                        $subscribe_email = $billing_data['email'];
                    } else if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $subscribe_email = Mage::helper('customer')->getCustomer()->getEmail();
                    }
                    //check if email is already subscribed
                    $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($subscribe_email);
                    if ($subscriberModel->getId() === NULL) {
                        Mage::getModel('newsletter/subscriber')->subscribe($subscribe_email);
                    } else if ($subscriberModel->getData('subscriber_status') != 1) {
                        $subscriberModel->setData('subscriber_status', 1);
                        try {
                            $subscriberModel->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }

            try {
                $result = $this->getOnepage()->saveOrder();
                $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $redirect = Mage::getUrl('webpos/index/index', array('_secure' => true));
                Header('Location: ' . $redirect);
                exit();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $redirect = Mage::getUrl('webpos/index/index', array('_secure' => true));
                Header('Location: ' . $redirect);
                exit();
            }
            $this->getOnepage()->getQuote()->save();
            if ($redirectUrl) {
                $redirect = $redirectUrl;
            } else {
                /* fix error - need to research customer after refesh Web POS page */
                Mage::getSingleton('checkout/session')->setData('checkout_success', true);
                /* end */
                $lastOrderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
                $order = Mage::getModel('sales/order')->load($lastOrderId);
                /* Daniel - inform to customer the order ID */
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('webpos')->__("A new order has been submitted successfully. Order ID: <a href='javascript:void(0)' style='cursor:pointer;' onclick='showOrder(" . $order->getId() . ", this); return false;'>#" . $order->getIncrementId() . "</a>"));
                /* end */
                $cookiesData = Mage::helper('webpos')->getWebPosCookies();
                if (isset($cookiesData['storeid'])) {
                    $order->setStoreId($cookiesData['storeid']);
                }
                if (isset($cookiesData['websiteid'])) {
                    $order->setWebsiteId($cookiesData['websiteid']);
                }
                $_order_items = $order->getAllItems();
                if (isset($billing_data['customer_id']) && $billing_data['customer_id'] != '') {
                    $order->setCustomerId($billing_data['customer_id']);
                }
                $savedQtys = array();
                foreach ($_order_items as $_order_item) {
                    $savedQtys[$_order_item->getId()] = $_order_item->getQtyOrdered();
                }
                $totalPaid = $this->getRequest()->getPost('cash-in');
                $totalRefunded = $this->getRequest()->getPost('balance');
                $transaction = Mage::getModel('core/resource_transaction')
                        ->addObject($order);
                /* send email after invoice */
                $sendemailInvoice = false;
                $sendemailShipment = false;
                /* -- */
                if ($totalPaid >= $order->getGrandTotal()) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
                    $invoice->register();
                    $transaction->addObject($invoice);
                    /* new code */
                    $sendemailInvoice = true;
                    /* -- */
                }
                if (isset($billing_data['onestepcheckout_shipped']))
                    $shipped = $billing_data['onestepcheckout_shipped'];
                if (isset($shipped) && $shipped == 1) {
                    $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
                    $shipment->register();
                    $transaction->addObject($shipment);
                    $sendemailShipment = true;
                }
                try {
                    if ((isset($shipped) && $shipped == 1) || ($totalPaid >= $order->getGrandTotal()))
                        $transaction->save();
                    /* new code */
                    $error = 0;
                    /* -- */
                } catch (Exception $e) {
                    /* new code */
                    $error = 1;
                    /* -- */
                }
                /* new code */
                if (isset($error) && $error == 0 && $sendemailInvoice == true) {
                    $invoice->sendEmail();
                }
                if (isset($error) && $error == 0 && $sendemailShipment == true) {
                    if (!$shipment->getEmailSent()) {
                        $shipment->sendEmail(true);
                        $shipment->setEmailSent(true);
                        $shipment->save();
                    }
                }
                /* end - send email after invoice , shipped */
                if ($totalRefunded <= 0) {
                    $totalRefunded = 0;
                }
                try {
                    if ($totalPaid < $order->getGrandTotal() && !(isset($shipped) && $shipped == 1)) {
                        $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                    }
                    if (($totalPaid < $order->getGrandTotal() && (isset($shipped) && $shipped == 1)) || ($totalPaid >= $order->getGrandTotal() && !$shipped)) {
                        $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
                    }

                    $cookie = Mage::getSingleton('core/cookie');
                    $adminId = $cookie->get('webpos_admin_id');
                    if ($adminId) {
                        $adminName = Mage::getModel('admin/user')->load($adminId)->getUsername();
                        $order->setWebposAdminId($adminId)
                                ->setWebposAdminName($adminName);
                    }
                    if (!$order->getData('shipping_description') && $shippingDescription)
                        $order->setData('shipping_description', $shippingDescription);
                    $order->setTotalPaid($totalPaid)
                            ->setBaseTotalPaid($totalPaid)
                            ->setTotalRefunded($totalRefunded)
                            ->setBaseTotalRefunded($totalRefunded)
                            ->save();
                } catch (Exception $e) {
                    
                }
                $cookie = Mage::getSingleton('core/cookie');
                $cookieTime = Mage::getStoreConfig('web/cookie/cookie_lifetime');
                $cookie->setLifeTime($cookieTime);
                $cookie->set('webpos_order_id', $lastOrderId);
                $redirect = Mage::getUrl('webpos/index/index', array('_secure' => true));
                Mage::getModel('core/session')->setData('webpos_order_id', $order->getId());
            }
            Header('Location: ' . $redirect);
            exit();
        } else {
            $this->_redirect('*/index/index');
        }
    }

    public function cartAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * 	Hai.Ta 28.5.2013
     * */
    public function loadCustomerToQuoteAction() {
        $result = array('success' => true);

        $email = $this->getRequest()->getParam('email_customer');
        $model = Mage::getModel('customer/customer');
        $customer = $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

        $quote = $this->_getCart()->getQuote();

        if ($customer->getId()) {
            $quote->setCustomer($customer);
        } else {
            $quote->setCustomer($customer);
            $quote->setCustomerGroupId(null);
            $quote->setCustomerTaxClassId(null);
        }

        $quote->save();
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * 	Hai.Ta 28.5.2013
     * */
    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     *  Hai.Tran 23.10.2013
     */
    public function applyRuleAction() {
        $data = $this->getRequest()->getPost();
        $ruleId = $data['ruleId'];
        $itemId = $data['itemId'];
        $point = $data['point'];

        $quoteItem = Mage::getModel('sales/quote_item')->load($itemId);
        if (!$quoteItem || !$itemId || !point) {
            echo 'error';
            return;
        }

        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }
        if ($this->getFlag('added_for_item_' . $quoteItem->getId())) {
            echo 'flag_false';
            return;
        }
        $this->setFlag('added_for_item_' . $quoteItem->getId());
        // Fix for Promotional Gift Extension
        if ($itemOptions = $quoteItem->getOptions()) {
            foreach ($itemOptions as $option) {
                $codeData = $option->getData('code');
                if ($codeData == 'option_promotionalgift_catalogrule') {
                    echo 'promotionalgif';
                    return;
                }
            }
        }

        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            $catalogRules = array();
        }
        if ($ruleId) {
            $catalogRules[$quoteItem->getId()] = array(
                'item_id' => $quoteItem->getId(),
                'item_qty' => $quoteItem->getQty(),
                'rule_id' => $ruleId,
                'point_used' => $point,
                'base_point_discount' => null,
                'point_discount' => null,
                'type' => 'catalog_spend'
            );
        } elseif (isset($catalogRules[$quoteItem->getId()])) {
            unset($catalogRules[$quoteItem->getId()]);
        }
        $session->setCatalogRules($catalogRules);
        echo 'success';
        return;
    }

    /**
     * Remove catalog spending for quote item
     */
    public function removecatalogAction() {
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            $catalogRules = array();
        }
        $id = $this->getRequest()->getParam('id');
        if (isset($catalogRules[$id])) {
            unset($catalogRules[$id]);
            $session->setCatalogRules($catalogRules);
            //$session->addSuccess($this->__('The rule has been successfully removed.'));
        } //else {
        //$session->addError($this->__('Rule not found'));
        // }
        echo 'success';
        return;
        //$this->_redirect('webpos/index/index');
    }

    public function checkAction() {
        if (Mage::getStoreConfig('rewardpoints/general/enable'))
            echo 'correct';
        else
            echo 'wrong';
        return;
    }

    /* Daniel - Updated - 12242014 */

    public function logoutAction() {
        $file = Mage::getBaseDir('media') . DS . 'magestore/webpos.xml';

        $data_file = array('webpos_admin_settingslink' => '',
            'webpos_admin_adminlogout' => '',
            'webpos_admin_adminlogin' => '',
            'firstname' => '',
            'lastname' => '',
            'username' => ''
        );



        Mage::getModel('webpos/file')->writeFile($data_file, $file);
        $this->_redirect('adminhtml/dashboard/index');
    }

    /* end */

    /* Daniel - updated - Load customer default shipping address */

    public function loadshippingAction() {
        $result = array();
        $customerId = Mage::app()->getRequest()->getParam('customerid');
        if ($customerId)
            $customerAddressId = Mage::getSingleton('customer/customer')->load($customerId)->getDefaultShipping();
        if ($customerAddressId) {
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            $result = $address->getData();
            $street = $address->getStreet();
            if ($street[0]) {
                $street0 = $street[0];
            } else {
                $street0 = '';
            }
            if ($street[1]) {
                $street1 = $street[1];
            } else {
                $street1 = '';
            }
            $result['street1'] = $street0;
            $result['street2'] = $street1;
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /* end */

    /*  Daniel - updated - empty cart */

    public function emptycartAction() {
        $session = Mage::getModel('checkout/session');
        try {
            $clearall = Mage::app()->getRequest()->getParam('clear');
            $items = $session->getQuote()->getAllItems();
            foreach ($items as $item) {
                $item->delete();
            }
            $session->setCartWasUpdated(true);
            $this->getResponse()->setBody("ok");
            if ($clearall == "all") {
                $session->unsetData('webpos_customerid');
                Mage::getSingleton('customer/session')->logout();
            }
        } catch (Mage_Core_Exception $exception) {
            $session->addError($exception->getMessage());
            $this->getResponse()->setBody("error");
        } catch (Exception $exception) {
            $session->addException($exception, $this->__('Cannot clear shopping cart.'));
            $this->getResponse()->setBody("error");
        }
    }

    /* end */

    /* Daniel - updated - check if an email is valid */

    public function is_valid_emailAction() {
        $validator = new Zend_Validate_EmailAddress();
        $email_address = $this->getRequest()->getPost('email_address');
        $message = 'Invalid';
        if ($email_address != '') {
            // Check if email is in valid format
            if (!$validator->isValid($email_address)) {
                $message = 'invalid';
            } else {
                //if email is valid, check if this email is registered
                /* Daniel - updated - allow to create account from other websites */
                if (Mage::getStoreConfig('customer/account_share/scope') == 1) {
                    $websiteId = Mage::app()->getStore()->getWebsiteId();
                    $cookiesData = Mage::helper('webpos')->getWebPosCookies();
                    if (isset($cookiesData['websiteid']))
                        $websiteId = $cookiesData['websiteid'];
                    $model = Mage::getModel('customer/customer');
                    $model->setWebsiteId($websiteId)->loadByEmail($email_address);
                    if ($model->getId()) {
                        $message = 'exists';
                    } else
                        $message = 'valid';
                }else {
                    $model = Mage::getModel('customer/customer');
                    $model->loadByEmail($email_address);
                    if ($model->getId()) {
                        if (Mage::getStoreConfig('customer/account_share/scope') == 0)
                            $message = 'exists';
                        else
                            $message = 'valid';
                    } else {
                        $message = 'valid';
                    }
                }
                /* end */
                /* if ($this->_emailIsRegistered($email_address)) {
                  $message = 'exists';
                  } else {
                  $message = 'valid';
                  } */
            }
        }
        $result = array('message' => $message);
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /* Mr.Jack */

    protected function _getWebposSession() {
        return Mage::getSingleton('webpos/session');
    }

    public function loginPostAction() {
        $session = $this->_getWebposSession();
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost();
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $this->_getWebposSession()->unsErrorMessage();
                    $resultLogin = $session->login($login['username'], $login['password']);
                    if ($resultLogin) {
                        if ($session->getUser()->getStatus() == 2) {
                            $this->_getWebposSession()->setErrorMessage(Mage::helper('webpos')->__('Your account was disabled.'));
                            $session->setId(null);
                            $result['errorMessage'] = Mage::helper('webpos')->__('Your account was disabled.');
                        } else {
                            $webpos_currency = $this->getLayout()->createBlock('directory/currency', 'webpos_currency')->setTemplate('webpos/webpos/currency.phtml');
                            $switchLanguageBlock = $this->getLayout()
                                    ->createBlock('page/switch', 'webpos_store_language')
                                    ->setTemplate('webpos/webpos/switch.phtml');
							$storeViewBlock = $this->getLayout()
                                    ->createBlock('webpos/liststore', 'webpos_websites')
                                    ->setTemplate('webpos/webpos/selectstore.phtml');
                            $result['menu'] = $this->getLayout()->createBlock('core/template')->setTemplate('webpos/webpos/menu.phtml')
                                    ->append($storeViewBlock)
                                    ->append($switchLanguageBlock)
                                    ->append($webpos_currency)
                                    ->toHtml();
                            $result['orders_area'] = $this->getLayout()->createBlock('core/template')->setTemplate('webpos/webpos/order.phtml')->toHtml();
                            $result['settings_area'] = $this->getLayout()->createBlock('core/template')->setTemplate('webpos/webpos/setting.phtml')->toHtml();
                            $result['webpos_popups'] = $this->getLayout()->createBlock('core/template')->setTemplate('webpos/webpos/popups.phtml')->toHtml();
                            $result['userid'] = $session->getUser()->getId();
                            $enableTills = Mage::getStoreConfig('webpos/general/enable_tills');
                            if ($enableTills == true) {
                                $result['enable_till'] = true;
                            }
                        }
                    } else {
                        $this->_getWebposSession()->setErrorMessage(Mage::helper('webpos')->__('Invalid User Name or Password.'));
                        $result['errorMessage'] = Mage::helper('webpos')->__('Invalid User Name or Password.');
                    }
                } catch (Exception $e) {
                    $result['errorMessage'] = $e->getMessage();
                }
            }
        }
        if (!$result)
            $this->_redirect('webpos/index/index');
        else
            $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function logoutPostAction() {
        try {
            Mage::getSingleton('core/session')->setCurrentWarehouseId(null);
            $this->_getWebposSession()->logout();
            Mage::getSingleton('checkout/cart')->truncate()->save();
            Mage::getSingleton('checkout/session')->clear();
            $this->_getWebposSession()->setCustomDiscount(null);
            $this->_getWebposSession()->setType(null);
            $this->_getWebposSession()->setDiscountValue(null);
            $this->_getWebposSession()->setDiscountName(null);
        } catch (Exception $e) {
            
        }
        $this->_redirect('webpos/index/index');
    }

    public function addCustomSaleAction() {
        $userId = Mage::helper('webpos/permission')->getCurrentUser();
        $isCreateOrder = Mage::helper('webpos/permission')->isCreateOrder($userId);
        if (!$isCreateOrder) {
            $result = array();
            $result['message'] = $this->__('You do not have permission!');
            $this->getResponse()->setBody(json_encode($result));
            return $this;
        }
        $cart = Mage::getSingleton('checkout/cart');
        $product = Mage::helper('webpos')->createCustomSaleProduct();
        $response = array();
        $name = $this->getRequest()->getParam('name');
        $this->_getWebposSession()->setItemName($name);
        $price = $this->getRequest()->getParam('price');
        $taxclass = $this->getRequest()->getParam('taxclass');
        if ($this->getRequest()->getParam('is_virtual') == 'true')
            $isVirtual = 1;
        else
            $isVirtual = 0;
        $response['imagePath'] = $product->getImageUrl();
        $response['productId'] = $product->getId();
        $response['name'] = $name;
        $response['price'] = $price;
        $requestInfo = array();
        $requestInfo['qty'] = 1;
        $requestInfo['name'] = $name;
        $requestInfo['price'] = $price;
        $requestInfo['tax_class_id'] = $taxclass;
        $requestInfo['is_virtual'] = $isVirtual;  /* able to ship or not */
        try {
            $cart->init();
            $cart->addProduct($product, $requestInfo);
        } catch (Exception $ex) {
            Mage::getSingleton('checkout/session')->addError($ex->getMessage());
        }
        $cart->save();
        //get last item
        $cart = Mage::getModel('checkout/cart')->getQuote();
        /*
          if ($cart->getCustomerId() == null) {
          $shippingAddress = $this->getOnepage()->getQuote()->getShippingAddress();
          if (isset($shippingAddress)) {
          $countryId = $shippingAddress->getCountryId();
          if (empty($countryId)) {
          Mage::helper('webpos')->getDefaultCustomer();
          }
          }
          }
          Mage::helper('webpos')->getDefaultCustomer();
         */
        foreach ($cart->getAllItems() as $item)
            ;
        $response['itemId'] = $item->getId();
        $response['subtotal_html'] = Mage::getBlockSingleton('webpos/cart_totals')->setTemplate('webpos/webpos/review/totals.phtml')->toHtml();
        $response['grand_total'] = Mage::app()->getStore()->formatPrice(Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal());
        $response['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $response['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
//vietdq
        $webposPrice = Mage::getSingleton('core/session')->getData('webpos_price');
        $webposArray = array();
        $webposArray = unserialize($webposPrice);
        $webposArray[$item->getId()] = $price;
        $webposPrice = serialize($webposArray);
        Mage::getSingleton('core/session')->setData('webpos_price', $webposPrice);
        /* new response */
        $grandTotal = Mage::getSingleton("checkout/cart")->getQuote()->getGrandTotal();
        $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
        $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
        $response['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $response['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
        $response['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
        /**/
        $this->getResponse()->setBody(json_encode($response));
//        $this->loadLayout(false);
//        $this->renderLayout();
    }

    public function createCustomerAction() {
        $result = array();
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $quote = $this->getOnepage()->getQuote();

        $billing_data = $this->getRequest()->getPost('billing', false);
        $customerDefaultInfo = Mage::helper('webpos/customer')->getAllDefaultCustomerInfo();
        //set default info if it is null
        if (isset($billing_data['country_id']) && $billing_data['country_id'] == null) {
            $billing_data['country_id'] = $customerDefaultInfo['country_id'];
        }
        if (isset($billing_data['region_id']) && $billing_data['region_id'] == null) {
            $billing_data['region_id'] = $customerDefaultInfo['region_id'];
        }
        if (isset($billing_data['street']) && $billing_data['street']['0'] == null) {
            $billing_data['street']['0'] = $customerDefaultInfo['street'];
        }
        if (isset($billing_data['city']) && $billing_data['city'] == null) {
            $billing_data['city'] = $customerDefaultInfo['city'];
        }
        if (isset($billing_data['postcode']) && $billing_data['postcode'] == null) {
            $billing_data['postcode'] = $customerDefaultInfo['postcode'];
        }
        if (isset($billing_data['telephone']) && $billing_data['telephone'] == null) {
            $billing_data['telephone'] = $customerDefaultInfo['telephone'];
        }
        if (isset($billing_data['region']) && $billing_data['region'] == null) {
            $billing_data['region'] = "";
        }
        if (isset($billing_data['region_id']) && $billing_data['region_id'] == null) {
            $billing_data['region_id'] = "";
        }
        if (isset($billing_data['fax']) && $billing_data['fax'] == null) {
            $billing_data['fax'] = "";
        }
        if (isset($billing_data['company']) && $billing_data['company'] == null) {
            $billing_data['company'] = "";
        }
		if ((isset($billing_data['vat_id']) && $billing_data['vat_id'] == null) || !isset($billing_data['vat_id'])) {
            $billing_data['vat_id'] = "";
        }
        //set default info if it is null
        $pass = Mage::helper('core')->uniqHash();
        $passConfirm = $pass;

        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($billing_data['firstname'])
                ->setLastname($billing_data['lastname'])
                ->setEmail($billing_data['email'])
                ->setGroupId($billing_data['group_id'])
                ->setPassword($pass)
                ->setConfirmation($passConfirm);

        try {
            $customer->save();
            //auto confirm new account if it is needed
            if ($customer->isConfirmationRequired()) {
                $customer->setConfirmation(null);
                $customer->save();
            }
            $customer->sendNewAccountEmail('registered', '', $store->getStoreId());
            Mage::dispatchEvent('customer_register_success', array('customer' => $customer));
            $result['error'] = false;
        } catch (Exception $e) {
            $result['error'] = true;
            $result['errmessage'] = $e->getMessage();
        }

        if (!$result['error']) {
            Mage::getModel('customer/session')->setCustomerAsLoggedIn($customer);
            $address = Mage::getModel('customer/address');
            $address->setCustomerId($customer->getId())
                    ->setFirstname($billing_data['firstname'])
                    ->setLastname($billing_data['lastname'])
                    ->setCountryId($billing_data['country_id'])
                    ->setRegionId($billing_data['region_id'])
                    ->setRegion($billing_data['region'])
                    ->setPostcode($billing_data['postcode'])
                    ->setCity($billing_data['city'])
                    ->setTelephone($billing_data['telephone'])
                    ->setFax($billing_data['fax'])
                    ->setCompany($billing_data['company'])
                    ->setStreet($billing_data['street'])
                    ->setEmail($billing_data['email'])
                    ->setVatId($billing_data['vat_id'])
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
            try {
                $address->save();
            } catch (Exception $e) {
                $result['error'] = true;
                $result['errmessage'] = $e->getMessage();
            }
            $this->getOnepage()->saveBilling($billing_data, false);
            $this->getOnepage()->getQuote()->getBillingAddress()->setEmail($billing_data['email'])->save();
            $this->getOnepage()->saveShipping($billing_data, false);

            $customerIds = array();
            $email = $customer->getEmail();
            $firstname = $customer->getFirstname();
            $lastname = $customer->getLastname();
            $address = $customer->getPrimaryBillingAddress();
            $telephone = (!empty($address)) ? $address->getTelephone() : '';
            $billingAddress = $customer->getDefaultBillingAddress();
            $shippingAddress = $customer->getDefaultShippingAddress();
            $billingAddress = (!empty($billingAddress)) ? $billingAddress->getData() : array();
            $shippingAddress = (!empty($shippingAddress)) ? $shippingAddress->getData() : array();
            $customerIds[$customer->getId()] = array(
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'telephone' => $telephone,
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress
            );
            $result['customerIds'] = $customerIds;
            $result['customer_id'] = $customer->getId();
        }
        $result['customer_name'] = $billing_data['firstname'] . " " . $billing_data['lastname'];
        $result['customer_email'] = $billing_data['email'];

        /* S: Daniel - updated v2.5 */
        $productHelper = Mage::helper('webpos/product');
        $result['customerData'] = $productHelper->getCustomerInfoResponse($customer);
        /* E: Daniel - updated v2.5 */
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function editCustomerAction() {
        $result = array();
        $billing_data = $this->getRequest()->getPost('billing', false);
        /* Daniel - updated - Customer address dropdown 20151118 */
        $billing_address_id = $this->getRequest()->getPost('billing_address_id', false);
        /* Daniel - updated - Customer address dropdown 20151118 */
        $customerDefaultInfo = Mage::helper('webpos/customer')->getAllDefaultCustomerInfo();
        //set default info if it is null
        if ($billing_data['country_id'] == null) {
            $billing_data['country_id'] = $customerDefaultInfo['country_id'];
        }
        if ($billing_data['region_id'] == null) {
            $billing_data['region_id'] = $customerDefaultInfo['region_id'];
        }
        if ($billing_data['street']['0'] == null) {
            $billing_data['street']['0'] = $customerDefaultInfo['street'];
        }
        if ($billing_data['city'] == null) {
            $billing_data['city'] = $customerDefaultInfo['city'];
        }
        if ($billing_data['postcode'] == null) {
            $billing_data['postcode'] = $customerDefaultInfo['postcode'];
        }
        if ($billing_data['telephone'] == null) {
            $billing_data['telephone'] = $customerDefaultInfo['telephone'];
        }
        //set default info if it is null
        try {
            /* Daniel - updated - Customer address dropdown 20151118 */
            if ($billing_address_id && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $billing_address = Mage::getModel('customer/address')->load($billing_address_id);
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $billing_data['email'] = ($billing_address->getData('email')) ? $billing_address->getData('email') : $customer->getData('email');
                $billing_data['firstname'] = ($billing_address->getData('firstname')) ? $billing_address->getData('firstname') : $customer->getData('firstname');
                $billing_data['lastname'] = ($billing_address->getData('lastname')) ? $billing_address->getData('lastname') : $customer->getData('lastname');
                Mage::getModel('checkout/session')->setData('billing_address_id', $billing_address_id);
            } else {
                Mage::getModel('checkout/session')->setData('billing_address_id', null);
            }
            Mage::getModel('checkout/session')->setData('typecommunication', $typecommunication);
            $this->getOnepage()->saveBilling($billing_data, $billing_address_id);
            /* Daniel - updated - Customer address dropdown 20151118 */
            $this->getOnepage()->getQuote()->getBillingAddress()->setEmail($billing_data['email'])->save();
        } catch (Exception $e) {
            $result['error'] = true;
            $result['errmessage'] = $e->getMessage();
        }
        $result['customer_name'] = $billing_data['firstname'] . " " . $billing_data['lastname'];
        $result['customer_email'] = $billing_data['email'];
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function showShippingAddressAction() {
        $result = $this->getLayout()->createBlock('webpos/customer')
                ->setTemplate('webpos/webpos/shipping.phtml')
                ->toHtml();
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function saveShippingAddressAction() {
        $result = array();
        $result['error'] = false;
        $shipping_data = $this->getRequest()->getPost('shipping', false);
        /* Daniel - updated - Customer address dropdown 20151118 */
        $shipping_address_id = $this->getRequest()->getPost('shipping_address_id', false);
        /* Daniel - updated - Customer address dropdown 20151118 */
        $customerDefaultInfo = Mage::helper('webpos/customer')->getAllDefaultCustomerInfo();
        //set default info if it is null
        if ($shipping_data['country_id'] == null) {
            $shipping_data['country_id'] = $customerDefaultInfo['country_id'];
        }
        if ($shipping_data['region_id'] == null) {
            $shipping_data['region_id'] = $customerDefaultInfo['region_id'];
        }
        if ($shipping_data['street']['0'] == null) {
            $shipping_data['street']['0'] = $customerDefaultInfo['street'];
        }
        if ($shipping_data['city'] == null) {
            $shipping_data['city'] = $customerDefaultInfo['city'];
        }
        if ($shipping_data['postcode'] == null) {
            $shipping_data['postcode'] = $customerDefaultInfo['postcode'];
        }
        if ($shipping_data['telephone'] == null) {
            $shipping_data['telephone'] = $customerDefaultInfo['telephone'];
        }
        //set default info if it is null
        $save_in_address_book = $this->getRequest()->getPost('save_in_address_book', false);
        $address = Mage::getModel('customer/address');
        $address->setFirstname($shipping_data['firstname'])
                ->setLastname($shipping_data['lastname'])
                ->setCountryId($shipping_data['country_id'])
                ->setRegionId($shipping_data['region_id'])
                ->setRegion($shipping_data['region'])
                ->setPostcode($shipping_data['postcode'])
                ->setCity($shipping_data['city'])
                ->setTelephone($shipping_data['telephone'])
                ->setFax($shipping_data['fax'])
                ->setCompany($shipping_data['company'])
                ->setStreet($shipping_data['street']);
        try {
            /* Daniel - updated - Customer address dropdown 20151118 */
            if ($shipping_address_id && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $shipping_address = Mage::getModel('customer/address')->load($billing_address_id);
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $shipping_data['firstname'] = ($shipping_address->getData('firstname')) ? $shipping_address->getData('firstname') : $customer->getData('firstname');
                $shipping_data['lastname'] = ($shipping_address->getData('lastname')) ? $shipping_address->getData('lastname') : $customer->getData('lastname');
                Mage::getModel('checkout/session')->setData('shipping_address_id', $shipping_address_id);
            } else {
                Mage::getModel('checkout/session')->setData('shipping_address_id', null);
            }
            $this->getOnepage()->saveShipping($shipping_data, $shipping_address_id);
            /* Daniel - updated - Customer address dropdown 20151118 */
        } catch (Exception $e) {
            $result['error'] = true;
            $result['errmessage'] = $e->getMessage();
        }
        //save address in address book
        /* Daniel - updated - Customer address dropdown 20151118 */
        if ($shipping_data['customer_id'] && $save_in_address_book == true && $shipping_address_id) {
            /* Daniel - updated - Customer address dropdown 20151118 */
            $address->setCustomerId($shipping_data['customer_id'])
                    ->setSaveInAddressBook('1');
            try {
                $address->save();
            } catch (Exception $ex) {
                $result['error'] = true;
                $result['errmessage'] = $ex->getMessage();
            }
        }
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();
        $grandTotal = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
//        $downgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50);
//        $upgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50 + 50);
        $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
        $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
        $result['grandTotals'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
        $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function showCustomerInformationAction() {
        $result = $this->getLayout()->createBlock('webpos/customer')
                ->setTemplate('webpos/webpos/createcustomer.phtml')
                ->toHtml();
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function applyDiscountAction() {
        Mage::helper('webpos')->getDefaultCustomer();
        $customDiscount = $this->getRequest()->getParam('customDiscount');
        $type = $this->getRequest()->getParam('type');
        $discountValue = $this->getRequest()->getParam('discountValue');
        $discountName = $this->getRequest()->getParam('discountName');
        $couponCode = $this->getRequest()->getParam('couponCode');
        if ($customDiscount == 'true') {
            $this->_getWebposSession()->setCustomDiscount($customDiscount);
            $this->_getWebposSession()->setType($type);
            $this->_getWebposSession()->setDiscountValue($discountValue);
            $this->_getWebposSession()->setDiscountName($discountName);
        } else {
            if ($couponCode != '') {
                Mage::getSingleton("checkout/session")->setData("coupon_code", $couponCode);
                Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($couponCode)->save();
            }
        }
        Mage::getSingleton('checkout/cart')->save();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function removeCouponAction() {
        Mage::getSingleton("checkout/session")->setData("coupon_code", null);
        Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode(null)->save();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function removeDiscountAction() {
        $this->_getWebposSession()->setDiscountValue(0);
        Mage::getSingleton('checkout/cart')->getQuote()->collectTotals()->save();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**/

    public function editUserInfoAction() {
        $name = $this->getRequest()->getPost('name');
        $email = $this->getRequest()->getPost('email');
        $userId = $this->getRequest()->getPost('userId');
        $oldpassword = $this->getRequest()->getPost('oldpassword');
        $password = $this->getRequest()->getPost('password');
        $userModel = Mage::getModel('webpos/user')->load($userId);
        $userPassword = $userModel->getPassword();

        $result = array();

        if (isset($oldpassword)) {

            $userPassword = $userModel->getPassword();
            if (Mage::helper('core')->validateHash($oldpassword, $userPassword)) {

                if (isset($password) && ($password != '')) {

                    $newPassword = Mage::helper('core')->getHash($password, Magestore_Webpos_Model_User::HASH_SALT_LENGTH);
                    $userModel->setPassword($newPassword);
                }
                if (isset($name)) {
                    $userModel->setDisplayName($name);
                }
                if (isset($email)) {
                    $userModel->setEmail($email);
                }


                try {
                    $userModel->save();
                    $result['success'] = 'Your information has been saved successfully.';
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            } else {
                $result['error'] = 'Your current password is not correct. Please try again to change your account information.';
            }


            $result['display_name'] = $userModel->getDisplayName();
            $result['email'] = $userModel->getEmail();
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function editAutoLogoutAction() {
        $userId = $this->getRequest()->getPost('userId');
        $autoLogout = $this->getRequest()->getPost('time');
        $userModel = Mage::getModel('webpos/user')->load($userId);
        $userModel->setAutoLogout($autoLogout);
        $userModel->save();

        $result = array();
        $result['settings'] = $this->getLayout()->createBlock('core/template')->setTemplate('webpos/webpos/setting.phtml')->toHtml();
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

}
