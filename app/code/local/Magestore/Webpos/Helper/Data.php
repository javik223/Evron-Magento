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
 * Webpos Helper
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Helper_Data extends Mage_Core_Helper_Abstract {

    public function __construct() {
        $this->settings = $this->getConfigData();
    }

    public function getOnePage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function isSignUpNewsletter() {
        if ($this->isCustomerLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if (isset($customer))
                $customerNewsletter = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if (isset($customerNewsletter) && $customerNewsletter->getId() != null && $customerNewsletter->getData('subscriber_status') == 1) {
                return true;
            }
        }
        return false;
    }

    public function getFieldValue() {
        return array(
            '0' => Mage::helper('webpos')->__('Null'),
            'firstname' => Mage::helper('webpos')->__('First Name'),
            'lastname' => Mage::helper('webpos')->__('Last Name'),
            'prefix' => Mage::helper('webpos')->__('Prefix Name'),
            'middlename' => Mage::helper('webpos')->__('Middle Name'),
            'suffix' => Mage::helper('webpos')->__('Suffix Name'),
            'email' => Mage::helper('webpos')->__('Email Address'),
            'company' => Mage::helper('webpos')->__('Company'),
            'street' => Mage::helper('webpos')->__('Address'),
            'country' => Mage::helper('webpos')->__('Country'),
            'region' => Mage::helper('webpos')->__('State/Province'),
            'city' => Mage::helper('webpos')->__('City'),
            'postcode' => Mage::helper('webpos')->__('Zip/Postal Code'),
            'telephone' => Mage::helper('webpos')->__('Telephone'),
            'fax' => Mage::helper('webpos')->__('Fax'),
            'birthday' => Mage::helper('webpos')->__('Date of Birth'),
            'gender' => Mage::helper('webpos')->__('Gender'),
            'taxvat' => Mage::helper('webpos')->__('Tax/VAT number'),
        );
    }

    public function getFieldEnable($number) {
        return Mage::getStoreConfig('webpos/field_position_management/row_' . $number);
    }

    public function getFieldRequire($field) {
        return Mage::getStoreConfig('webpos/field_require_management/' . $field);
    }

    public function getConfigData() {
        $configData = array();
        $configItems = array('general/active', 'general/checkout_title', 'general/checkout_description',
            'general/show_shipping_address', 'general/country_id',
            'general/default_payment', 'general/default_shipping',
            'general/postcode', 'general/region_id', 'general/city',
            'general/use_for_disabled_fields', 'general/hide_shipping_method',
            'general/page_layout',
            'field_management/show_city', 'field_management/show_zipcode',
            'field_management/show_company', 'field_management/show_fax',
            'field_management/show_telephone', 'field_management/show_region',
            'general/show_comment', 'general/show_newsletter',
            'general/show_discount', 'general/newsletter_default_checked',
            'field_management/enable_giftmessage',
            'checkout_mode/show_login_link', 'checkout_mode/enable_registration',
            'checkout_mode/allow_guest', 'checkout_mode/login_link_title',
            'ajax_update/enable_ajax', 'ajax_update/ajax_fields',
            'ajax_update/update_payment',
            'ajax_update/reload_payment',
            'terms_conditions/enable_terms', 'terms_conditions/term_html',
            'terms_conditions/term_width', 'terms_conditions/term_height',
            'order_notification/enable_notification', 'order_notification/notification_email');
        foreach ($configItems as $configItem) {
            $config = explode('/', $configItem);
            $value = $config[1];
            $configData[$value] = Mage::getStoreConfig('webpos/' . $configItem);
        }
        return $configData;
    }

    public function isHideShippingMethod() {
        $_isHide = $this->settings['hide_shipping_method'];
        if ($_isHide) {
            $_quote = $this->getOnepage()->getQuote();
            $rates = $_quote->getShippingAddress()->getShippingRatesCollection();
            $rateCodes = array();
            foreach ($rates as $rate) {
                if (!in_array($rate->getCode(), $rateCodes)) {
                    $rateCodes[] = $rate->getCode();
                }
            }
            if (count($rateCodes) > 1) {
                $_isHide = false;
            }
        }

        return $_isHide;
    }

    public function saveShippingMethod($shippingMethod) {
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $rate = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        $this->getOnepage()->getQuote()->collectTotals()->save();
        return array();
    }

    public function savePaymentMethod($data) {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $onepage = Mage::getSingleton('checkout/session')->getQuote();
        if ($onepage->isVirtual()) {
            $onepage->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $onepage->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }
        $payment = $onepage->getPayment();
        $payment->importData($data);

        $onepage->save();

        return array();
    }

    public function enableTermsAndConditions() {
        return $this->settings['enable_terms'];
    }

    public function getTermsConditionsHtml() {
        return $this->settings['term_html'];
    }

    public function isShowShippingAddress() {
        if ($this->getOnepage()->getQuote()->isVirtual()) {
            return false;
        }
        if ($this->settings['show_shipping_address']) {
            return true;
        }
        return false;
    }

    public function getTermPopupWidth() {
        return $this->settings['term_width'];
    }

    public function getTermPopupHeight() {
        return $this->settings['term_height'];
    }

    public function enableCustomSize() {
        return Mage::getStoreConfig('webpos/terms_conditions/enable_custom_size', $this->getStoreId());
    }

    public function getTermTitle() {
        return Mage::getStoreConfig('webpos/terms_conditions/term_title', $this->getStoreId());
    }

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function showDiscount() {
        return $this->settings['show_discount'];
    }

    public function enableGiftMessage() {
        //return $this->settings['enable_giftmessage'];
//		return Mage::getStoreConfig('sales/gift_options/allow_order');
        $giftMessage = Mage::getStoreConfig('webpos/giftmessage/enable_giftmessage', $this->getStoreId());
        if ($giftMessage) {
            Mage::getConfig()->saveConfig('sales/gift_options/allow_order', 1);
            Mage::getConfig()->saveConfig('sales/gift_options/allow_items', 1);
            return true;
        } else {
            Mage::getConfig()->saveConfig('sales/gift_options/allow_order', 0);
            Mage::getConfig()->saveConfig('sales/gift_options/allow_items', 0);
            return false;
        }
    }

    public function enableOrderComment() {
        return $this->settings['show_comment'];
    }

    public function isShowNewsletter() {
        if ($this->settings['show_newsletter'] && !$this->isSignUpNewsletter())
            return true;
        else
            return false;
    }

    public function isSubscribeByDefault() {
        return $this->settings['newsletter_default_checked'];
    }

    public function enableGiftWrap() {
        return Mage::getStoreConfig('webpos/giftwrap/enable_giftwrap', $this->getStoreId());
    }

    public function enableGiftwrapModule() {
        $moduleGiftwrap = Mage::getConfig()->getModuleConfig('Magestore_Giftwrap')->is('active', 'true');
        return $moduleGiftwrap;
    }

    public function getGiftwrapAmount() {
        return Mage::getStoreConfig('webpos/giftwrap/giftwrap_amount', $this->getStoreId());
    }

    public function getGiftwrapType() {
        return Mage::getStoreConfig('webpos/giftwrap/giftwrap_type', $this->getStoreId());
    }

    public function getOrderGiftwrapAmount() {
        $amount = $this->getGiftwrapAmount();
        $giftwrapAmount = 0;
        // $freeBoxes = 0;
        $items = Mage::getSingleton('checkout/cart')->getItems();
        if ($this->getGiftwrapType() == 1) {
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $giftwrapAmount += $amount * ($item->getQty());
            }
        } elseif (count($items) > 0) {
            $giftwrapAmount = $amount;
        }
        return $giftwrapAmount;
    }

    public function checkGiftwrapSession() {
        $session = Mage::getSingleton('checkout/session');
        return $session->getData('webpos_giftwrap');
    }

    public function getSurveyQuestion() {
        return Mage::getStoreConfig('webpos/survey/survey_question', $this->getStoreId());
    }

    public function getSurveyValues() {
        return Mage::getStoreConfig('webpos/survey/survey_values', $this->getStoreId());
    }

    public function enableFreeText() {
        return Mage::getStoreConfig('webpos/survey/enable_survey_freetext', $this->getStoreId());
    }

    public function enableSurvey() {
        return Mage::getStoreConfig('webpos/survey/enable_survey', $this->getStoreId());
    }

    public function saveOrderComment($observer) {
        $billing = $this->_getRequest()->getPost('billing');
        if ($this->enableOrderComment()) {
            $comment = $billing['onestepcheckout_comment'];
            $comment = trim($comment);
            if ($comment != '') {
                $order = $observer->getEvent()->getOrder();
                try {
                    $order->setWebposOrderComment($comment);
                } catch (Exception $e) {
                    
                }
            }
        }
        if ($this->enableSurvey()) {
            $surveyQuestion = $this->getSurveyQuestion();
            $surveyValues = unserialize($this->getSurveyValues());
            $surveyValue = $billing['onestepcheckout-survey'];
            $surveyFreeText = $billing['onestepcheckout-survey-freetext'];
            if (!empty($surveyValue)) {
                if ($surveyValue != 'freetext') {
                    $surveyAnswer = $surveyValues[$surveyValue]['value'];
                } else {
                    $surveyAnswer = $surveyFreeText;
                }
            }

            $order = $observer->getEvent()->getOrder();
            $session = Mage::getSingleton('checkout/session');
            if ($surveyQuestion)
                $session->setData('survey_question', $surveyQuestion);
            if (isset($surveyAnswer))
                $session->setData('survey_answer', $surveyAnswer);
        }
    }

    /**
     * Hai.Ta 28.5.2013
     * */
    public function getUrlSetCustomerToQuote() {
        return $this->_getUrl('webpos/index/loadCustomerToQuote', array('_secure' => true, 'auth' => 1));
    }

    /**
     * Hai.Ta 28.5.2013
     * */
    public function getConfigCheckEmail() {
        return (int) Mage::getStoreConfig('webpos/ajax_update/check_email', $this->getStoreId());
    }

    /**
     * Hai.Ta 28.5.2013
     * */
    public function getConfigShowNotice() {
        return (int) Mage::getStoreConfig('webpos/ajax_update/show_popup', $this->getStoreId());
    }

    /**
     * Intergrated RewardPoints 
     */
    public function getActiveRewardPoints() {
        if (Mage::getConfig()->getModuleConfig('Magestore_RewardPoints')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/general/enable') && Mage::getStoreConfig('webpos/rewardpoints/enable_rewardpoints')) {
            return true;
        } else
            return false;
    }

    public function getActiveRewardPointsRule() {
        if ($this->getActiveRewardPoints() && Mage::getConfig()->getModuleConfig('Magestore_RewardPointsRule')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/rewardpointsrule/enable')) {
            return true;
        } else
            return false;
    }

    public function showEarningPointsCart() {
        if (Mage::getConfig()->getModuleConfig('Magestore_RewardPoints')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/general/enable') && Mage::getConfig()->getModuleConfig('Magestore_RewardPointsRule')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/rewardpointsrule/enable')) {
            return true;
        } else
            return false;
    }

    public function getBackgroundColor($style) {
        if ($style == 'orange')
            return '#EE9600';
        if ($style == 'green')
            return '#539222';
        if ($style == 'black')
            return '#363636';
        if ($style == 'blue')
            return '#417290';
        if ($style == 'darkblue')
            return '#094171';
        if ($style == 'pink')
            return '#EA4A72';
        if ($style == 'red')
            return '#BA200F';
        if ($style == 'violet')
            return '#C246AE';

        return '#EE9600';
    }

    /* Daniel - get Web POS logo */

    public function getPOSLogo() {
        $showGiftLabel = Mage::getStoreConfig('webpos/general/webpos_logo', $this->getStoreId());
        if ($showGiftLabel)
            return Mage::getStoreConfig('webpos/general/webpos_logo', $this->getStoreId());
        return null;
    }

    /* end */

    /* fix error - Multiple store */

    /* Daniel - updated  - function getWebPosCookies() return current Web POS cookies */

    public function getWebPosCookies() {
        $cookiesData = array();
        $cookiesData['cookieTime'] = '';
        $cookiesData['webpos_admin_key'] = '';
        $cookiesData['webpos_admin_code'] = '';
        $cookiesData['webpos_admin_id'] = '';
        $cookiesData['webpos_admin_adminlogin'] = '';
        $cookiesData['webpos_admin_settingslink'] = '';
        $cookiesData['webpos_admin_adminlogout'] = '';
        $cookiesData['adminhtml'] = '';
        $settingslink = Mage::getUrl("webposadmin/adminhtml_config/edit/", array("section" => "webpos", "frompos" => true));


        $cookie = Mage::getSingleton('core/cookie');
        $coreCookiesData = $cookie->get();

        if (isset($_COOKIE['cookieTime'])) {
            $cookiesData['cookieTime'] = $_COOKIE['cookieTime'];
        } elseif (isset($coreCookiesData['cookieTime'])) {
            $cookiesData['cookieTime'] = $coreCookiesData['cookieTime'];
        }
        if (isset($_COOKIE['webpos_admin_key'])) {
            $cookiesData['webpos_admin_key'] = $_COOKIE['webpos_admin_key'];
        } elseif (isset($coreCookiesData['webpos_admin_key'])) {
            $cookiesData['webpos_admin_key'] = $coreCookiesData['webpos_admin_key'];
        }
        if (isset($_COOKIE['webpos_admin_code'])) {
            $cookiesData['webpos_admin_code'] = $_COOKIE['webpos_admin_code'];
        } elseif (isset($coreCookiesData['webpos_admin_code'])) {
            $cookiesData['webpos_admin_code'] = $coreCookiesData['webpos_admin_code'];
        }
        if (isset($_COOKIE['webpos_admin_id'])) {
            $cookiesData['webpos_admin_id'] = $_COOKIE['webpos_admin_id'];
        } elseif (isset($coreCookiesData['webpos_admin_id'])) {
            $cookiesData['webpos_admin_id'] = $coreCookiesData['webpos_admin_id'];
        }
        if (isset($_COOKIE['adminhtml'])) {
            $cookiesData['adminhtml'] = $_COOKIE['adminhtml'];
        } elseif (isset($coreCookiesData['adminhtml'])) {
            $cookiesData['adminhtml'] = $coreCookiesData['adminhtml'];
        }

        if (isset($_COOKIE['webpos_admin_adminlogin'])) {
            $cookiesData['webpos_admin_adminlogin'] = $_COOKIE['webpos_admin_adminlogin'];
        } elseif (isset($coreCookiesData['webpos_admin_adminlogin'])) {
            $cookiesData['webpos_admin_adminlogin'] = $coreCookiesData['webpos_admin_adminlogin'];
        }
        if (isset($_COOKIE['webpos_admin_settingslink'])) {
            $cookiesData['webpos_admin_settingslink'] = $_COOKIE['webpos_admin_settingslink'];
        } elseif (isset($coreCookiesData['webpos_admin_settingslink'])) {
            $cookiesData['webpos_admin_settingslink'] = $coreCookiesData['webpos_admin_settingslink'];
        }
        if (isset($_COOKIE['webpos_admin_adminlogout'])) {
            $cookiesData['webpos_admin_adminlogout'] = $_COOKIE['webpos_admin_adminlogout'];
        } elseif (isset($coreCookiesData['webpos_admin_adminlogout'])) {
            $cookiesData['webpos_admin_adminlogout'] = $coreCookiesData['webpos_admin_adminlogout'];
        }
        if (isset($_COOKIE['storeid'])) {
            $cookiesData['storeid'] = $_COOKIE['storeid'];
        } elseif (isset($coreCookiesData['storeid'])) {
            $cookiesData['storeid'] = $coreCookiesData['storeid'];
        }
        if (isset($_COOKIE['websiteid'])) {
            $cookiesData['websiteid'] = $_COOKIE['websiteid'];
        } elseif (isset($coreCookiesData['websiteid'])) {
            $cookiesData['websiteid'] = $coreCookiesData['websiteid'];
        }
        return $cookiesData;
    }

    /* Daniel - updated - function setWebPosCookies() reset Web POS cookies */

    public function setWebPosCookies($cookiesData) {
        if (count($cookiesData) > 0) {
            $cookie = Mage::getSingleton('core/cookie');
            foreach ($cookiesData as $cookie_name => $cookie_value) {
                if (isset($cookiesData['cookieTime'])) {
                    setcookie($cookie_name, $cookie_value, time() + $cookiesData['cookieTime'], "/");
                    $cookie->setLifeTime($cookiesData['cookieTime']);
                } else {
                    $cookie->setLifeTime(3600);
                    setcookie($cookie_name, $cookie_value, time() + 3600, "/");
                }
                $cookie->set($cookie_name, $cookie_value);
            }
        }
    }

    /* end */

    /**
     * @author: Adam 07/07/2015
     * return array
     * list product attribute to search form
     */
    public function getProductAttributeForSearch() {
        return Mage::getStoreConfig('webpos/product_search/product_attribute', $this->getStoreId());
    }

    /**
     * @author: Adam 07/07/2015
     * return boole
     * 
     */
    public function isEnableProductSearchOffline() {
        return Mage::getStoreConfig('webpos/product_search/search_offline', $this->getStoreId());
    }

    /**
     * Get custom sales product
     * 
     * @return boolean | Mage_Catalog_Model_Product
     */
    public function createCustomSaleProduct() {
        $product = Mage::getModel('catalog/product');

        if ($productId = $product->getIdBySku('webpos-customsale')) {
            return $product->load($productId);
        }

        $entityType = $product->getResource()->getEntityType();
        $attributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityType->getId())
                ->getFirstItem();

        $product->setAttributeSetId($attributeSet->getId())
                ->setTypeId('customsale')
                ->setSku('webpos-customsale')
                ->setWebsiteIds(array_keys(Mage::app()->getWebsites()))
                ->setStockData(array(
                    'manage_stock' => 0,
                    'use_config_manage_stock' => 0,
        ));
        $product->addData(array(
            'name' => 'Custom Sale',
            'weight' => 1,
            'status' => 1,
            'visibility' => 1,
            'price' => 0,
            'description' => 'Custom Sale for POS system',
            'short_description' => 'Custom Sale for POS system',
        ));

        if (!is_array($errors = $product->validate())) {
            try {
                $product->save();
            } catch (Exception $e) {
                return false;
            }
        }
        return $product;
    }

    /* vietdq - Daniel - Modified - 26/07/2015 */

    public function getDefaultCustomer() {
        $storeId = Mage::app()->getStore(true)->getId();
        $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        $customerSession = Mage::getModel('customer/session');
        $posorder = Mage::getModel('webpos/posorder');
        $helper = Mage::helper('webpos/customer');
        $customerModel = Mage::getModel('customer/customer');
        $customerDefault = $helper->getAllDefaultCustomerInfo();
        $quote = $this->getOnepage()->getQuote();
        $shipping = $quote->getShippingAddress();
        $shippingData = $shipping->getData();
        $billing = $quote->getBillingAddress();
        $billingData = $billing->getData();
        $billingChanged = $shippingChanged = false;
        $countryCode = Mage::getStoreConfig('general/country/default');
        $allowGuestCheckout = Mage::helper('checkout')->isAllowedGuestCheckout($quote);
        if (isset($customerDefault['customer_id']) && $customerDefault['customer_id'] != 0) {
            $customer = $customerModel->load($customerDefault['customer_id']);
            if ($customer->getWebsiteId() != $websiteId) {
                $email = $customer->getEmail();
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($websiteId);
                $customer->loadByEmail($email);
                if (!$customer->getId() || $customer->getId() == '')
                    $customer = $posorder->getCustomer();
            }
            if (!$allowGuestCheckout) {
                //$this->deleteCustomerQuote($customerDefault['customer_id']);
                //$customerSession->setCustomerAsLoggedIn($customer);
            }
            if (!$quote->getCustomer())
                $quote->setCustomer($customer)->save();
            $billingDefault = $customer->getDefaultBillingAddress();
            $shippingDefault = $customer->getDefaultShippingAddress();
            if (!empty($billingDefault)) {
                $billingDefault = $billingDefault->getData();

                if ($customer->getDefaultBillingAddress()->getCountryId() != $countryCode) {
                    $customer->getDefaultBillingAddress()->setCountryId($countryCode)->save();
                    $billingDefault['country_id'] = $countryCode;
                }
            } else
                $billingDefault = $customerDefault;
            if (!empty($shippingDefault))
                $shippingDefault = $shippingDefault->getData();
            else
                $shippingDefault = $customerDefault;
        }else {
            $customer = $posorder->getCustomer();
            if (!$allowGuestCheckout) {
                //$this->deleteCustomerQuote($customer->getId());
                //$customerSession->setCustomerAsLoggedIn($customer);
            }
            if (!$quote->getCustomer())
                $quote->setCustomer($customer)->save();
            $billingDefault = $customer->getDefaultBillingAddress();
            $shippingDefault = $customer->getDefaultShippingAddress();
            if (!empty($billingDefault)) {
                $billingDefault = $billingDefault->getData();

                if ($customer->getDefaultBillingAddress()->getCountryId() != $countryCode) {
                    $customer->getDefaultBillingAddress()->setCountryId($countryCode)->save();
                    $billingDefault['country_id'] = $countryCode;
                }
            } else
                $billingDefault = $customerDefault;
            if (!empty($shippingDefault))
                $shippingDefault = $shippingDefault->getData();
            else
                $shippingDefault = $customerDefault;
        }
        foreach ($billingDefault as $key => $value) {
            if ($key == 'customer_id')
                continue;
            if (!isset($billingData[$key]) || empty($billingData[$key])) {
                $quote->getBillingAddress()->setData($key, $value);
                $billingChanged = true;
            }
        }
        foreach ($shippingDefault as $key => $value) {
            if ($key == 'customer_id')
                continue;
            if (!isset($shippingData[$key]) || empty($shippingData[$key])) {
                $quote->getShippingAddress()->setData($key, $value);
                $shippingChanged = true;
            }
        }
        if ($billingChanged == true)
            $quote->getBillingAddress()->save();
        if ($shippingChanged == true)
            $quote->getShippingAddress()->setCollectShippingRates(true)->save();
        if ($billingChanged == true || $shippingChanged == true)
            $this->getOnepage()->getQuote()->save();
    }

    public function isShowCancelButton() {
        if (Mage::getSingleton("checkout/session")->getCouponCode() || Mage::getSingleton("webpos/session")->getDiscountValue())
            return true;
        return false;
    }

    public function getCurrencySymbol() {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $symbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
        return $symbol;
    }

    public function getOrderClass($orderStatus) {
        switch ($orderStatus) {
            case "canceled":
                $statusOrderClass = 'type2';
                break;
            case "closed":
                $statusOrderClass = 'type2';
                break;
            case "pending":
                $statusOrderClass = 'type4';
                break;
            case "pending_payment":
                $statusOrderClass = 'type4';
                break;
            case "complete":
                $statusOrderClass = 'type1';
                break;
            case "processing":
                $statusOrderClass = 'type3';
                break;
        }
        return $statusOrderClass;
    }

    public function getOfflineConfig() {
        $configData = array();
        $configItems = array('offline/enable',
            'offline/network_check_interval',
            'offline/check_stock_interval',
            'offline/data_load_interval',
            'offline/product_per_request',
            'offline/customer_per_request',
            'product_search/search_offline'
        );
        foreach ($configItems as $configItem) {
            $config = explode('/', $configItem);
            $value = $config[1];
            $configData[$value] = Mage::getStoreConfig('webpos/' . $configItem);
        }
        if (empty($configData['data_load_interval']))
            $configData['data_load_interval'] = 0;
        if (empty($configData['product_per_request']))
            $configData['product_per_request'] = 50;
        if (empty($configData['customer_per_request']))
            $configData['customer_per_request'] = 100;
        return $configData;
    }

    /**
     * @author Adam
     * @param float $cashin 
     * @param string $separateSymbol (separator the decimal: . or ,)
     * @return type array();
     */
    protected function round_cashin($cashin, $separateSymbol = '.') {
//        $arrCashin = explode($separateSymbol, $cashin);
//        $intCashin = $arrCashin[0];
//        $floCashin = $arrCashin[1];
        $intCashin = (int) $cashin;
        $stepAmount = 5;
        $len = strlen($intCashin);
        if ($len == 1 || $len == 2) {
            $stepAmount = 5;
        } else {
            for ($i = 0; $i < $len - 2; $i++) {
                $stepAmount .= '0';
            }
        }
        if ($cashin <= 0) {
            $up = $down = 0;
        } elseif (ceil($cashin) == $cashin) {
            if ($cashin % $stepAmount == 0) {
                $up = (($cashin / $stepAmount) + 1) * $stepAmount;
                $down = (($cashin / $stepAmount) - 1) * $stepAmount;
            } else {
                $up = (ceil($cashin / $stepAmount) * $stepAmount);
                $down = ((ceil($cashin / $stepAmount) - 1) * $stepAmount);
            }
        } else {
            $up = (ceil($cashin / $stepAmount) * $stepAmount);
            $down = ((ceil($cashin / $stepAmount) - 1) * $stepAmount);
        }

        $arrCashin['up'] = $up;
        $arrCashin['down'] = $down;

        return $arrCashin;
    }

    /**
     * @author Adam
     * @param float $cashin
     * @param string $separateSymbol
     * @return type float
     */
    public function round_down_cashin($cashin, $separateSymbol = '.') {
        $arrUp = $this->round_cashin($cashin, $separateSymbol);
        return $arrUp['down'];
    }

    /**
     * @author Adam
     * @param float $cashin
     * @param string $separateSymbol
     * @return float 
     */
    public function round_up_cashin($cashin, $separateSymbol = '.') {
        $arrUp = $this->round_cashin($cashin, $separateSymbol);
        return $arrUp['up'];
    }

    //vietdq
    public function formatPrice($price) {
        return Mage::helper('checkout')->getQuote()->getStore()->formatPrice($price, false);
    }

    public function convertCurrency($order, $price) {
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $orderSymbolCode = $order->getOrderCurrencyCode();
        $price = Mage::helper('directory')->currencyConvert($price, $orderSymbolCode, $currentCurrencyCode);
        return $price;
        //$grand_total = $_order->getData('grand_total');
    }

    public function checkEE() {
        return Mage::getEdition() == Mage::EDITION_ENTERPRISE;
    }

    /**
     * Get version of Inventory WebPOS extension
     * 
     * @return string
     */
    public function getInventoryWebPOSVersion() {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywebpos')) {
            return (string) Mage::getConfig()->getModuleConfig('Magestore_Inventorywebpos')->version;
        }
        return null;
    }

    /**
     * Check if Inventory WebPOS is active with version from 1.1
     * 
     * @return string
     */
    public function isInventoryWebPOS11Active() {
        if (((int) str_replace('.', '', $this->getInventoryWebPOSVersion())) >= 11) {
            return true;
        }
        return false;
    }

    public function deleteCustomerQuote($customerId) {
        $customerQuote = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter('customer_id', $customerId);

        if ($customerQuote->getSize() > 0)
            foreach ($customerQuote as $quote) {
                $quote->delete();
            }
    }

    public function columnExist($tableName, $columnName) {
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');

        Zend_Db_Table::setDefaultAdapter($writeAdapter);
        $table = new Zend_Db_Table($tableName);
        if (!in_array($columnName, $table->info('cols'))) {
            return false;
        } return true;
    }

    public function tableExist($tableName) {
        $exists = (boolean) Mage::getSingleton('core/resource')
                        ->getConnection('core_write')
                        ->showTableStatus(trim($tableName, '`'));
        return $exists;
    }

    public function setTillData($till_id) {
        try {
            if (!$till_id instanceof Magestore_Webpos_Model_Till) {
                $till = Mage::getModel('webpos/till')->load($till_id);
            } else {
                $till = $till_id;
            }
            $currentTill = Mage::getModel('webpos/session')->getTill();
            if ($till->getId() && (!$currentTill->getTillId() || ($till->getId() != $currentTill->getTillId()))) {
                Mage::getModel('webpos/session')->setTill($till);
            }
        } catch (Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
    }

    public function getReceiptSettings($storeId = null) {
        $storeId = ($storeId != null) ? $storeId : Mage::app()->getStore()->getStoreId();
        $settings = array();
        $settings['date_format'] = Mage::getStoreConfig('webpos/receipt/date_format', $storeId);
        $settings['show_store_information'] = Mage::getStoreConfig('webpos/receipt/show_store_information', $storeId);
        $settings['show_cashier_name'] = Mage::getStoreConfig('webpos/receipt/show_cashier_name', $storeId);
        $settings['show_comment'] = Mage::getStoreConfig('webpos/receipt/show_comment', $storeId);
        $settings['show_barcode'] = Mage::getStoreConfig('webpos/receipt/show_barcode', $storeId);
        $settings['show_receipt_logo'] = Mage::getStoreConfig('webpos/receipt/show_receipt_logo', $storeId);
        $settings['show_shipping_method'] = Mage::getStoreConfig('webpos/receipt/show_shipping_method', $storeId);
        $settings['show_payment_method'] = Mage::getStoreConfig('webpos/receipt/show_payment_method', $storeId);
        $settings['header_text'] = htmlentities(Mage::getStoreConfig('webpos/receipt/header_text', $storeId));
        $settings['footer_text'] = htmlentities(Mage::getStoreConfig('webpos/receipt/footer_text', $storeId));
        $settings['font_type'] = Mage::getStoreConfig('webpos/receipt/font_type', $storeId);
        $settings['webpos_logo'] = Mage::getStoreConfig('webpos/general/webpos_logo', $storeId);
        return $settings;
    }

    public function getBarcodeImgSource($value) {
        if (!isset($value)) {
            return '';
        }
        $type = "code128";
        $barcodeOptions = array('text' => $value,
            'fontSize' => "14",
            'withQuietZones' => true
        );
        $rendererOptions = array();
        $imageResource = Zend_Barcode::factory(
                        $type, 'image', $barcodeOptions, $rendererOptions
        );
        return $imageResource;
    }

    public function getStoreInformation($storeId = null) {
        $settings = array();
        $storeId = ($storeId != null) ? $storeId : Mage::app()->getStore()->getStoreId();
        $storePhone = Mage::getStoreConfig('general/store_information/phone', $storeId);
        $storeName = Mage::getStoreConfig('general/store_information/name', $storeId);
        $storeAddress = Mage::getStoreConfig('general/store_information/address', $storeId);
        $userData = Mage::getModel('webpos/session')->getUser()->getData();
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            $pos_user_id = $userData['user_id'];
            $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                    ->addFieldToFilter('user_id', $pos_user_id);
            $warehouseId = $userCollection->getfirstItem()->getWarehouseId();
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
            $storeName = $warehouse->getData('warehouse_name');
            $street = $warehouse->getData('street');
            $city = $warehouse->getData('city');
            $country_id = $warehouse->getData('country_id');
            $postcode = $warehouse->getData('postcode');
            $storeAddress = $street . "," . $city . "," . $country_id . "," . $postcode;
        }
        $settings['storeName'] = $storeName;
        $settings['storePhone'] = $storePhone;
        $settings['storeAddress'] = $storeAddress;
        return $settings;
    }

    public function getPdfinvoiceVersion() {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Pdfinvoiceplus')) {
            return (string) Mage::getConfig()->getModuleConfig('Magestore_Pdfinvoiceplus')->version;
        }
        return null;
    }

    public function isPdfinvoice21Active() {
        if (((int) str_replace('.', '', $this->getPdfinvoiceVersion())) >= 21) {
            return true;
        }
        return false;
    }

    public function addDefaultData() {
        try {
            $till = Mage::getModel('webpos/till')->getCollection()->getFirstItem();
            $location = Mage::getModel('webpos/userlocation')->getCollection()->getFirstItem();
            $role = Mage::getModel('webpos/role')->getCollection()->getFirstItem();
            $locationId = '';
            if (!$location->getId()) {
                try {
                    $locationModel = Mage::getModel('webpos/userlocation');
                    $locationModel->setData('display_name', Mage::helper('webpos')->__('Default Location'));
                    $locationModel->setData('address', Mage::helper('webpos')->__('Default Location'));
                    $locationModel->setData('description', Mage::helper('webpos')->__('Default Location'));
                    $locationModel->setData('status', 1);
                    $locationModel->save();
                    $locationId = $locationModel->getId();
                } catch (Exception $e) {
                    
                }
            } else {
                $locationId = $location->getId();
            }
            if (!$till->getId() && $locationId != '') {
                try {
                    $tillModel = Mage::getModel('webpos/till');
                    $tillModel->setData('till_name', Mage::helper('webpos')->__('Cash Drawer Default'));
                    $tillModel->setData('location_id', $locationId);
                    $tillModel->setData('status', 1);
                    $tillModel->save();
                } catch (Exception $e) {
                    
                }
            }
            if (!$role->getId()) {
                try {
                    $roleModel = Mage::getModel('webpos/role');
                    $roleModel->setData('display_name', Mage::helper('webpos')->__('Admin'));
                    $roleModel->setData('description', Mage::helper('webpos')->__('Default Role'));
                    $roleModel->setData('permission_ids', '1');
                    $roleModel->setData('active', 1);
                    $roleModel->save();
                } catch (Exception $e) {
                    
                }
            }
        } catch (Exception $e) {
            
        }
    }

    public function emptyPOSdata() {
        /*
          if (Mage::helper('persistent/session')->isPersistent()) {
          Mage::helper('persistent/session')->getSession()->removePersistentCookie();
          $customerSession = Mage::getSingleton('customer/session');
          if (!$customerSession->isLoggedIn()) {
          $customerSession->setCustomerId(null)->setCustomerGroupId(null);
          }
          Mage::getSingleton('persistent/observer')->setQuoteGuest();
          }
         */
        Mage::getModel('checkout/session')->clear();
        Mage::getSingleton('checkout/cart')->truncate();
        Mage::getSingleton('checkout/cart')->save();
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $customer = Mage::getModel('customer/customer')->load(0);
            $customerSession->setCustomerAsLoggedIn($customer);
        };
        Mage::getModel('checkout/session')->setData('reloading_order_id', null);
        Mage::getModel('checkout/session')->setData('holded_key', null);
        Mage::getSingleton('webpos/session')->setCustomDiscount(null);
        Mage::getSingleton('webpos/session')->setType(null);
        Mage::getSingleton('webpos/session')->setDiscountValue(null);
        Mage::getSingleton('webpos/session')->setDiscountName(null);
    }

}
