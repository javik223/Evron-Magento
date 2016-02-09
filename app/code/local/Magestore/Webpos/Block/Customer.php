<?php

class Magestore_Webpos_Block_Customer extends Mage_Checkout_Block_Onepage_Abstract {

    public function getCountryHtmlSelect($type) {
        if ($type == 'shipping') {
            $address = $this->getQuote()->getShippingAddress();
        } else {
            $address = $this->getQuote()->getBillingAddress();
        }
        $countryId = $address->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type . '[country_id]')
                ->setId($type . ':country_id')
                ->setClass('billingdata')
                ->setTitle(Mage::helper('webpos')->__('Country'))
                ->setValue($countryId)
                ->setOptions($this->getCountryOptions())
                ->setExtraParams('fieldkey="country"');
        if ($type == 'shipping') {
            $select->setClass('shippingdata');
        }

        return $select->getHtml();
    }

    public function getBillingAddress() {
        $address = Mage::getModel('sales/quote_address');
        if ($this->getQuote()->getBillingAddress()->getCustomerId() && $this->getQuote()->getBillingAddress()->getCustomerId() != $this->getDefaultCustomerId())
            $address = $this->getQuote()->getBillingAddress();
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $customer = $customerSession->getCustomer();
            if ($customer->getId() != $this->getDefaultCustomerId())
                $address->setData('email', $customer->getEmail());
        }
        return $address;
    }

    public function getShippingAddress() {
        return $this->getQuote()->getShippingAddress();
    }

    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getTitle() {
        if ($this->getBillingAddress()->getCustomerId() && $this->getBillingAddress()->getCustomerId() != $this->getDefaultCustomerId()) {
            $title = Mage::helper('webpos')->__('Edit Customer');
        } else {
            $title = Mage::helper('webpos')->__('New Customer');
        }
        return $title;
    }

    public function getCustomer() {
        $customer = Mage::getModel('customer/customer');
        if ($this->getBillingAddress()->getCustomerId() && $this->getBillingAddress()->getCustomerId() != $this->getDefaultCustomerId()) {
            $customer->load($this->getBillingAddress()->getCustomerId());
        }
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $customer = $customerSession->getCustomer();
        }
        return $customer;
    }

    public function getSaveCustomerUrl() {
        if ($this->getCustomer()->getId()) {
            return Mage::getUrl('webpos/index/editCustomer', array('_secure' => true));
        } else {
            return Mage::getUrl('webpos/index/createCustomer', array('_secure' => true));
        }
    }

    public function getCustomerGroupHtmlSelect() {
        $group = '';
        $checkoutSession = Mage::getModel('checkout/session');
        $customerSession = Mage::getModel('customer/session');
        if ($customerSession->isLoggedIn())
            $group = $customerSession->getCustomer()->getGroupId();
        elseif ($checkoutSession->getQuote()->getCustomer() != null) {
            $group = $checkoutSession->getQuote()->getCustomer()->getGroupId();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
                ->setName('billing[group_id]')
                ->setId('billing:group_id')
                ->setClass('billingdata')
                ->setTitle(Mage::helper('webpos')->__('Group'))
                ->setValue($group)
                ->setOptions($this->getCustomerGroupOptions())
                ->setExtraParams('fieldkey="group_id"');

        return $select->getHtml();
    }

    public function getCustomerGroupOptions() {
        $groups = array();
        $groupsCol = Mage::getModel('customer/group')->getCollection();
        if (count($groupsCol) > 0) {
            foreach ($groupsCol as $group) {
                $groups[$group->getId()] = $group->getCustomerGroupCode();
            }
        }
        return $groups;
    }

    public function getAddress() {
        if ($this->isCustomerLoggedIn()) {
            $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
            $addressIdFromQuote = Mage::getModel('checkout/session')->getData('billing_address_id');
            if ($addressIdFromQuote) {
                $billing = Mage::getModel('customer/address')->load($addressIdFromQuote);
            }if ($customerAddressId) {
                $billing = Mage::getModel('customer/address')->load($customerAddressId);
            } else {
                $billing = $this->getQuote()->getBillingAddress();
            }
            if (!$billing->getCustomerAddressId()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $default_address = $customer->getDefaultBillingAddress();
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

    public function getAddressesHtmlSelect($type) {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
            $addressId = $this->getAddress()->getId();
            /* Daniel - updated - Customer address dropdown 20151118 */
            $billing_address_id = Mage::getModel('checkout/session')->getData('billing_address_id');
            $addressId = ($billing_address_id) ? $billing_address_id : $addressId;

            $shipping_address_id = Mage::getModel('checkout/session')->getData('shipping_address_id');
            $shippingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
            if (($shippingAddressId != $addressId || $shipping_address_id != $addressId) && $type == 'shipping') {
                $addressId = ($shipping_address_id) ? $shipping_address_id : $shippingAddressId;
            }
            /* Daniel - updated - Customer address dropdown 20151118 */
            if (empty($addressId)) {
                if ($type == 'billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                    ->setName($type . '_address_id')
                    ->setId($type . '-address-select')
                    ->setClass('address-select')
                    ->setExtraParams('style="width:350px"')
                    ->setValue($addressId)
                    ->setOptions($options);
            $select->addOption('', Mage::helper('checkout')->__('New Address'));
            return $select->getHtml();
        }
        return '';
    }

    public function getDefaultCustomerId() {
        $data = Mage::helper('webpos/customer')->getAllDefaultCustomerInfo();
        return $data['customer_id'];
    }

}
