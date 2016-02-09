<?php

/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 08/07/2015
 * Time: 1:10 CH
 */
class Magestore_Webpos_Helper_Customer extends Mage_Core_Helper_Abstract {

    public function getDefaultFirstName() {
        return Mage::getStoreConfig('webpos/guest_checkout/first_name');
    }

    public function getDefaultLastName() {
        return Mage::getStoreConfig('webpos/guest_checkout/last_name');
    }

    public function getDefaultStreet() {
        return Mage::getStoreConfig('webpos/guest_checkout/street');
    }

    public function getDefaultCountry() {
        return Mage::getStoreConfig('webpos/guest_checkout/country_id');
    }

    public function getDefaultState() {
        return Mage::getStoreConfig('webpos/guest_checkout/region_id');
    }

    public function getDefaultCity() {
        return Mage::getStoreConfig('webpos/guest_checkout/city');
    }

    public function getDefaultZip() {
        return Mage::getStoreConfig('webpos/guest_checkout/zip');
    }

    public function getDefaultTelephone() {
        return Mage::getStoreConfig('webpos/guest_checkout/telephone');
    }

    public function getDefaultEmail() {
        return Mage::getStoreConfig('webpos/guest_checkout/email');
    }

    public function getDefaultCustomerId() {
        return Mage::getStoreConfig('webpos/guest_checkout/customer_id');
    }

    public function getAllDefaultCustomerInfo() {
        $customerData = array();
        $customerData['customer_id'] = Mage::helper('webpos/customer')->getDefaultCustomerId();
        $customerData['country_id'] = Mage::helper('webpos/customer')->getDefaultCountry();
        $customerData['region_id'] = Mage::helper('webpos/customer')->getDefaultState();
        $customerData['postcode'] = Mage::helper('webpos/customer')->getDefaultZip();
        $customerData['street'] = Mage::helper('webpos/customer')->getDefaultStreet();
        $customerData['telephone'] = Mage::helper('webpos/customer')->getDefaultTelephone();
        $customerData['city'] = Mage::helper('webpos/customer')->getDefaultCity();
        $customerData['firstname'] = Mage::helper('webpos/customer')->getDefaultFirstName();
        $customerData['lastname'] = Mage::helper('webpos/customer')->getDefaultLastName();
        $customerData['email'] = Mage::helper('webpos/customer')->getDefaultEmail();
        if (isset($customerData['customer_id'])) {
            $customer = Mage::getModel('customer/customer')->load($customerData['customer_id']);
			if($customer->getId()){
				$billingDefault = $customer->getDefaultBillingAddress();
				if (isset($billingDefault)) {
					$billingData = $billingDefault->getData();
					if (isset($billingData['country_id']))
						$customerData['country_id'] = $billingData['country_id'];
					if (isset($billingData['region_id']))
						$customerData['region_id'] = $billingData['region_id'];
					if (isset($billingData['postcode']))
						$customerData['postcode'] = $billingData['postcode'];
					if (isset($billingData['street']))
						$customerData['street'] = $billingData['street'];
					if (isset($billingData['telephone']))
						$customerData['telephone'] = $billingData['telephone'];
					if (isset($billingData['city']))
						$customerData['city'] = $billingData['city'];
					if (isset($billingData['firstname']))
						$customerData['firstname'] = $billingData['firstname'];
					if (isset($billingData['lastname']))
						$customerData['lastname'] = $billingData['lastname'];
					if (isset($billingData['email']))
						$customerData['email'] = $billingData['email'];
				}
			}
        }
        return $customerData;
    }

    public function getCustomerHtml(Mage_Customer_Model_Customer $customer) {
        $html = "<li onclick=\"addCustomerToCart(" . $customer->getId() . ")\" style='width:100%; float:left; cursor: pointer' class='email-customer col-lg-6 col-md-6'><span style='float:left;'>" . $customer->getFirstname() . " " . $customer->getLastname() . "</span><span style='float:right'><a href='mailto:" . $customer->getEmail() . "'>" . $customer->getEmail() . "</a></span><br/><span style='float:right'>" . $customer->getTelephone() . "</span></li>";
        return $html;
    }

    public function isEnableAutoSendEmail($type) {
        $config = false;
        switch ($type) {
            case 'order':
                $config = Mage::getStoreConfig('webpos/email_configuration/auto_email_orders');
                break;
            case 'invoice':
                $config = Mage::getStoreConfig('webpos/email_configuration/auto_email_invoice');
                break;
            case 'shipment':
                $config = Mage::getStoreConfig('webpos/email_configuration/auto_email_shipment');
                break;
            case 'creditmemo':
                $config = Mage::getStoreConfig('webpos/email_configuration/auto_email_creditmemo');
                break;
        }
        return $config;
    }

    public function loginDefaultCustomer() {
        $storeId = Mage::app()->getStore(true)->getId();
        $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        $customerSession = Mage::getModel('customer/session');
        $posorder = Mage::getModel('webpos/posorder');
        $helper = Mage::helper('webpos/customer');
        $customerModel = Mage::getModel('customer/customer');
        $customerDefault = $helper->getAllDefaultCustomerInfo();
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
        }else {
            $customer = $posorder->getCustomer();
        }

        if (!$customerSession->isLoggedIn()) {
            $customerSession->setCustomerAsLoggedIn($customer);
        }
    }

    public function getWebposEmailTemplate($type) {
        $config = array();
        switch ($type) {
            case 'order':
                $guestTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_guest_order');
                $customerTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_customer_order');
                $config['guest'] = ($guestTemplate != 'webpos_email_configuration_email_template_guest_invoice') ? $guestTemplate : '';
                $config['customer'] = ($customerTemplate != 'webpos_email_configuration_email_template_customer_order') ? $customerTemplate : '';
                break;
            case 'invoice':
                $guestTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_guest_invoice');
                $customerTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_customer_invoice');
                $config['guest'] = ($guestTemplate != 'webpos_email_configuration_email_template_guest_invoice') ? $guestTemplate : '';
                $config['customer'] = ($customerTemplate != 'webpos_email_configuration_email_template_customer_invoice') ? $customerTemplate : '';
                break;
            case 'shipment':
                $guestTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_guest_shipment');
                $customerTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_customer_shipment');
                $config['guest'] = ($guestTemplate != 'webpos_email_configuration_email_template_guest_shipment') ? $guestTemplate : '';
                $config['customer'] = ($customerTemplate != 'webpos_email_configuration_email_template_customer_shipment') ? $customerTemplate : '';
                break;
            case 'creditmemo':
                $guestTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_guest_creditmemo');
                $customerTemplate = Mage::getStoreConfig('webpos/email_configuration/email_template_customer_creditmemo');
                $config['guest'] = ($guestTemplate != 'webpos_email_configuration_email_template_guest_creditmemo') ? $guestTemplate : '';
                $config['customer'] = ($customerTemplate != 'webpos_email_configuration_email_template_customer_creditmemo') ? $customerTemplate : '';
                break;
        }
        return $config;
    }

}
