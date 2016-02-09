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
 * Webpos Model
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
if (!function_exists('array_replace_recursive')) {

    function array_replace_recursive($array, $array1) {

        function recurse($array, $array1) {
            foreach ($array1 as $key => $value) {
                // create new key in $array, if it is empty or not an array
                if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
                    $array[$key] = array();
                }

                // overwrite the value in the base array
                if (is_array($value)) {
                    $value = recurse($array[$key], $value);
                }
                $array[$key] = $value;
            }
            return $array;
        }

        // handle the arguments, merge one by one
        $args = func_get_args();
        $array = $args[0];
        if (!is_array($array)) {
            return $array;
        }
        for ($i = 1; $i < count($args); $i++) {
            if (is_array($args[$i])) {
                $array = recurse($array, $args[$i]);
            }
        }
        return $array;
    }

}

class Magestore_Webpos_Model_Posorder extends Mage_Core_Model_Abstract {

    const CUSTOMER_RANDOM = null;

    protected $_quote = '';
    protected $_shippingMethod = 'webpos_shipping_free';
    protected $_paymentMethod = 'cashforpos';
    protected $_billingAddress = '';
    protected $_shippingAddress = '';
    protected $_customer = self::CUSTOMER_RANDOM;
    protected $_subTotal = 0;
    protected $_order;
    protected $_storeId;

    /**
     * @var Mage_Core_Model_Resource_Resource $_resource
     */
    protected $_resource;

    /**
     * @var Varien_Db_Adapter_Interface $_adapter
     */
    protected $_adapter;
    protected $_defaultData = array(
        'account' => array(
            'website_id' => '1',
            'group_id' => '1',
            'prefix' => '',
            'firstname' => 'Firstname{id}',
            'middlename' => '',
            'lastname' => 'Lastname',
            'suffix' => '',
            'email' => 'email{id}@example.net',
            'dob' => '',
            'taxvat' => '',
            'gender' => '',
            'sendemail_store_id' => '1',
            'password' => 'a111111',
            'default_billing' => '_item1',
            'default_shipping' => '_item1',
        ),
        'address' => array(
            '_item1' => array(
                'prefix' => '',
                'firstname' => 'Firstname',
                'middlename' => '',
                'lastname' => 'Lastname',
                'suffix' => '',
                'company' => '',
                'street' => array(
                    0 => 'Address',
                    1 => '',
                ),
                'city' => 'City',
                'country_id' => 'US',
                'region_id' => '12',
                'region' => '',
                'postcode' => '123123',
                'telephone' => '123123123',
                'fax' => '',
                'vat_id' => '',
            ),
        ),
    );

    public function _construct() {
        parent::_construct();
        $this->_init('webpos/posorder');
        $this->_resource = Mage::getResourceSingleton('core/resource');
        $magentoVersion = Mage::getVersion();
        //vietdq fix checkout 1.5
        if (version_compare($magentoVersion, '1.5', '>=') && version_compare($magentoVersion, '1.6', '<')) {
            $this->_adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        } else {
            $this->_adapter = $this->_resource->getReadConnection();
        }
    }

    public function setShippingMethod($methodName) {
        $this->_shippingMethod = $methodName;
    }

    public function setPaymentMethod($methodName) {
        $this->_paymentMethod = $methodName;
    }

    public function setBillingAddress($adressData) {
        $this->_billingAddress = $adressData;
    }

    public function setShippingAddress($adressData) {
        $this->_shippingAddress = $adressData;
    }

    public function setCustomer($customer) {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $this->_customer = $customer;
        }
        if (is_numeric($customer)) {
            $this->_customer = Mage::getModel('customer/customer')->load($customer);
        } else if ($customer === self::CUSTOMER_RANDOM) {
            $helper = Mage::helper('webpos/customer');
            $customerModel = Mage::getModel('customer/customer');
            $configModel = new Mage_Core_Model_Config();
            $customerDefault = $helper->getAllDefaultCustomerInfo();
            if (isset($customerDefault['customer_id']) && $customerDefault['customer_id'] != 0)
                $this->_customer = $customerModel->load($customerDefault['customer_id']);
            else {
                $website_id = Mage::app()->getWebsite()->getId();
                $customerData = array(
                    'account' => array(
                        'website_id' => $website_id,
                        'group_id' => '1',
                        'prefix' => '',
                        'firstname' => $customerDefault['firstname'],
                        'middlename' => '',
                        'lastname' => $customerDefault['lastname'],
                        'suffix' => '',
                        'email' => $customerDefault['email'],
                        'dob' => '',
                        'taxvat' => '',
                        'gender' => '',
                        'sendemail_store_id' => '1',
                        'password' => 'a111111',
                        'default_billing' => '_item1',
                        'default_shipping' => '_item1',
                    ),
                    'address' => array(
                        '_item1' => array(
                            'prefix' => '',
                            'firstname' => $customerDefault['firstname'],
                            'middlename' => '',
                            'lastname' => $customerDefault['lastname'],
                            'suffix' => '',
                            'company' => '',
                            'street' => array(
                                0 => $customerDefault['street'],
                                1 => '',
                            ),
                            'city' => $customerDefault['city'],
                            'country_id' => $customerDefault['country_id'],
                            'region_id' => $customerDefault['region_id'],
                            'region' => '',
                            'postcode' => $customerDefault['postcode'],
                            'telephone' => $customerDefault['telephone'],
                            'fax' => '',
                            'vat_id' => '',
                        ),
                    )
                );
                $customerModel->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $customerModel->loadByEmail($customerDefault['email']);
                if ($customerModel->getId() != null && $customerModel->getId() != 0) {
                    $this->_customer = $customerModel;
                    $configModel->saveConfig('webpos/guest_checkout/customer_id', $customerModel->getId(), 'default', 0);
                } else {
                    $this->_customer = $this->createCustomer($customerData);
                    if ($this->_customer instanceof Mage_Customer_Model_Customer) {
                        $customerId = $this->_customer->getId();
                        $configModel->saveConfig('webpos/guest_checkout/customer_id', $customerId, 'default', 0);
                    }
                }
            }
        }
    }

    public function getCustomer() {
        if (!$this->_customer instanceof Mage_Customer_Model_Customer) {
            $this->setCustomer(self::CUSTOMER_RANDOM);
        }
        return $this->_customer;
    }

    public function createOrder($products) {
        if (!($this->_customer instanceof Mage_Customer_Model_Customer)) {
            $this->setCustomer(self::CUSTOMER_RANDOM);
        }

        $transaction = Mage::getModel('core/resource_transaction');
        $this->_storeId = $this->_customer->getStoreId();
        $reservedOrderId = Mage::getSingleton('eav/config')
                ->getEntityType('order')
                ->fetchNewIncrementId($this->_storeId);

        $currencyCode = Mage::app()->getBaseCurrencyCode();
        $this->_order = Mage::getModel('sales/order')
                ->setIncrementId($reservedOrderId)
                ->setStoreId($this->_storeId)
                ->setQuoteId(0)
                ->setGlobalCurrencyCode($currencyCode)
                ->setBaseCurrencyCode($currencyCode)
                ->setStoreCurrencyCode($currencyCode)
                ->setOrderCurrencyCode($currencyCode);


        $this->_order->setCustomerEmail($this->_customer->getEmail())
                ->setCustomerFirstname($this->_customer->getFirstname())
                ->setCustomerLastname($this->_customer->getLastname())
                ->setCustomerGroupId($this->_customer->getGroupId())
                ->setCustomerIsGuest(0)
                ->setCustomer($this->_customer);


        $billing = $this->_customer->getDefaultBillingAddress();
        $billingAddress = Mage::getModel('sales/order_address')
                ->setStoreId($this->_storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
                ->setCustomerId($this->_customer->getId())
                ->setCustomerAddressId($this->_customer->getDefaultBilling());

        $shipping = $this->_customer->getDefaultShippingAddress();
        $shippingAddress = Mage::getModel('sales/order_address')
                ->setStoreId($this->_storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($this->_customer->getId())
                ->setCustomerAddressId($this->_customer->getDefaultShipping());

        if (!isset($billing) || !$billing) {
            $this->setCustomer();
            $billing = $this->_customer->getDefaultBillingAddress();
        }
        $billingAddress->setCustomerAddress_id($billing->getEntityId())
                ->setPrefix($billing->getPrefix())
                ->setFirstname($billing->getFirstname())
                ->setMiddlename($billing->getMiddlename())
                ->setLastname($billing->getLastname())
                ->setSuffix($billing->getSuffix())
                ->setCompany($billing->getCompany())
                ->setStreet($billing->getStreet())
                ->setCity($billing->getCity())
                ->setCountry_id($billing->getCountryId())
                ->setRegion($billing->getRegion())
                ->setRegion_id($billing->getRegionId())
                ->setPostcode($billing->getPostcode())
                ->setTelephone($billing->getTelephone())
                ->setFax($billing->getFax());

        if ($this->_billingAddress != '') {
            $bill = $this->_billingAddress;
            foreach ($billingAddress as $key => $value) {
                if (isset($bill[$key]) && $bill[$key] != '')
                    $billingAddress->setData($key, $bill[$key]);
            }
        }
        $this->_order->setBillingAddress($billingAddress);
        if (!isset($shipping) || !$shipping) {
            $this->setCustomer();
            $shipping = $this->_customer->getDefaultShippingAddress();
        }
        $shippingAddress->setCustomer_address_id($shipping->getEntityId())
                ->setPrefix($shipping->getPrefix())
                ->setFirstname($shipping->getFirstname())
                ->setMiddlename($shipping->getMiddlename())
                ->setLastname($shipping->getLastname())
                ->setSuffix($shipping->getSuffix())
                ->setCompany($shipping->getCompany())
                ->setStreet($shipping->getStreet())
                ->setCity($shipping->getCity())
                ->setCountry_id($shipping->getCountryId())
                ->setRegion($shipping->getRegion())
                ->setRegion_id($shipping->getRegionId())
                ->setPostcode($shipping->getPostcode())
                ->setTelephone($shipping->getTelephone())
                ->setFax($shipping->getFax());

        if ($this->_shippingAddress != '') {
            $ship = $this->_shippingAddress;
            foreach ($shippingAddress as $key => $value) {
                if (isset($ship[$key]) && $ship[$key] != '')
                    $shippingAddress->setData($key, $ship[$key]);
            }
        }
        $this->_order->setShippingAddress($shippingAddress);
        $this->_order->getShippingAddress()->setCollectShippingRates(false)->setShippingMethod($this->_shippingMethod);
        $orderPayment = Mage::getModel('sales/order_payment')
                ->setStoreId($this->_storeId)
                ->setCustomerPaymentId(0)
                ->setMethod($this->_paymentMethod)
                ->setPoNumber(' – ');
        /*
          zend_debug::dump($billing->getData());
          zend_debug::dump($shipping->getData());
          die('123');
         */
        $this->_order->setPayment($orderPayment);

        $this->_addProducts($products);

        $this->_order->setSubtotal($this->_subTotal)
                ->setBaseSubtotal($this->_subTotal)
                ->setGrandTotal($this->_subTotal)
                ->setBaseGrandTotal($this->_subTotal);

        $transaction->addObject($this->_order);
        $transaction->addCommitCallback(array($this->_order, 'place'));
        $transaction->addCommitCallback(array($this->_order, 'save'));
        try {
            $transaction->save();
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
        return array('orderId' => $this->_order->getId());
    }

    protected function _addProducts($products) {
        $this->_subTotal = 0;

        foreach ($products as $productRequest) {
            if ($productRequest['product'] == 'rand') {

                $productsCollection = Mage::getResourceModel('catalog/product_collection');

                $productsCollection->addFieldToFilter('type_id', 'simple');
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productsCollection);

                $productsCollection->getSelect()
                        ->order('RAND()')
                        ->limit(rand($productRequest['min'], $productRequest['max']));

                foreach ($productsCollection as $product) {
                    $this->_addProduct(array(
                        'product' => $product->getId(),
                        'qty' => rand(1, 2)
                    ));
                }
            } else {
                $this->_addProduct($productRequest);
            }
        }
    }

    protected function _addProduct($requestData) {
        $request = new Varien_Object();
        $request->setData($requestData);

        $product = Mage::getModel('catalog/product')->load($request['product']);

        $cartCandidates = $product->getTypeInstance(true)
                ->prepareForCartAdvanced($request, $product);

        if (is_string($cartCandidates)) {
            throw new Exception($cartCandidates);
        }

        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($cartCandidates as $candidate) {
            $item = $this->_productToOrderItem($candidate, $candidate->getCartQty());

            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }
            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->setQty($item->getQty() + $candidate->getCartQty());

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) { // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

        foreach ($items as $item) {
            $this->_order->addItem($item);
        }

        return $items;
    }

    function _productToOrderItem(Mage_Catalog_Model_Product $product, $qty = 1) {
        $rowTotal = $product->getFinalPrice() * $qty;

        $options = $product->getCustomOptions();

        $optionsByCode = array();

        foreach ($options as $option) {
            $quoteOption = Mage::getModel('sales/quote_item_option')->setData($option->getData())
                    ->setProduct($option->getProduct());

            $optionsByCode[$quoteOption->getCode()] = $quoteOption;
        }

        $product->setCustomOptions($optionsByCode);

        $options = $product->getTypeInstance(true)->getOrderOptions($product);

        $orderItem = Mage::getModel('sales/order_item')
                ->setStoreId($this->_storeId)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(NULL)
                ->setProductId($product->getId())
                ->setProductType($product->getTypeId())
                ->setQtyBackordered(NULL)
                ->setTotalQtyOrdered($product['rqty'])
                ->setQtyOrdered($product['qty'])
                ->setName($product->getName())
                ->setSku($product->getSku())
                ->setPrice($product->getFinalPrice())
                ->setBasePrice($product->getFinalPrice())
                ->setOriginalPrice($product->getFinalPrice())
                ->setRowTotal($rowTotal)
                ->setBaseRowTotal($rowTotal)
                ->setWeeeTaxApplied(serialize(array()))
                ->setBaseWeeeTaxDisposition(0)
                ->setWeeeTaxDisposition(0)
                ->setBaseWeeeTaxRowDisposition(0)
                ->setWeeeTaxRowDisposition(0)
                ->setBaseWeeeTaxAppliedAmount(0)
                ->setBaseWeeeTaxAppliedRowAmount(0)
                ->setWeeeTaxAppliedAmount(0)
                ->setWeeeTaxAppliedRowAmount(0)
                ->setProductOptions($options);

        $this->_subTotal += $rowTotal;

        return $orderItem;
    }

    protected function _processTemplates(&$data) {
        $config = $this->_adapter->getConfig();

        $select = $this->_adapter->select();
        $magentoVersion = Mage::getVersion();
        //vietdq fix checkout 1.5
        if (version_compare($magentoVersion, '1.5', '>=') && version_compare($magentoVersion, '1.6', '<')) {
            $tableName = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        } else {
            $tableName = $this->_adapter->getTableName('customer_entity');
        }
        $select
                ->from('information_schema.tables', 'AUTO_INCREMENT')
                ->where('table_schema = ?', $config['dbname'])
                ->where(
                        'table_name = ?', $tableName
        );

        $nextId = $this->_adapter->fetchOne($select);

        foreach ($data['account'] as &$field) {
            $field = str_replace('{id}', $nextId, $field);
        }

        foreach ($data['address'] as &$address) {
            foreach ($address as &$field) {
                $field = str_replace('{id}', $nextId, $field);
            }
        }
    }

    public function createCustomer($data = array()) {
        $data = array_replace_recursive($this->_defaultData, $data);

        $this->_processTemplates($data);

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');

        $customer->setData($data['account']);

        foreach (array_keys($data['address']) as $index) {
            $address = Mage::getModel('customer/address');

            $addressData = array_merge($data['account'], $data['address'][$index]);

            // Set default billing and shipping flags to address
            $isDefaultBilling = isset($data['account']['default_billing']) && $data['account']['default_billing'] == $index;
            $address->setIsDefaultBilling($isDefaultBilling);
            $isDefaultShipping = isset($data['account']['default_shipping']) && $data['account']['default_shipping'] == $index;
            $address->setIsDefaultShipping($isDefaultShipping);

            $address->addData($addressData);

            // Set post_index for detect default billing and shipping addresses
            $address->setPostIndex($index);

            $customer->addAddress($address);
        }

        // Default billing and shipping
        if (isset($data['account']['default_billing'])) {
            $customer->setData('default_billing', $data['account']['default_billing']);
        }
        if (isset($data['account']['default_shipping'])) {
            $customer->setData('default_shipping', $data['account']['default_shipping']);
        }
        if (isset($data['account']['confirmation'])) {
            $customer->setData('confirmation', $data['account']['confirmation']);
        }

        if (isset($data['account']['sendemail_store_id'])) {
            $customer->setSendemailStoreId($data['account']['sendemail_store_id']);
        }

        $customer
                ->setPassword($data['account']['password'])
                ->setForceConfirmed(true)
                ->save()
                ->cleanAllAddresses()
        ;

        return $customer;
    }

    public function getQuote() {
        return $this->_quote;
    }

    public function setQuote($quote) {
        $this->_quote = $quote;
    }

    public function createQuote() {
        $this->_quote = $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
        if (!($this->_customer instanceof Mage_Customer_Model_Customer)) {
            $this->setCustomer(self::CUSTOMER_RANDOM);
        }
        $this->_quote->assignCustomer($this->_customer);
    }

    public function setCashinToQuote($cashin) {
        Mage::getSingleton('webpos/session')->setWebposCash($cashin);
    }

    public function addProductToQuote($product, $buyInfo) {
        $this->_quote->addProduct($product, new Varien_Object($buyInfo));
    }

    public function addProductsToQuote($cartData) {
        foreach ($cartData as $productRequest) {
            $request = new Varien_Object();
            $request->setData($productRequest);
            $product = Mage::getModel('catalog/product')->load($request['product']);
            $this->_quote->addProduct($product, $request);
        }
    }

    public function saveOrderFromQuote() {
        $billingAddress = $this->_quote->getBillingAddress()->addData();
        $shippingAddress = $this->_quote->getShippingAddress()->addData();
        if ($this->_billingAddress != '')
            $this->_quote->getBillingAddress()->addData($this->_billingAddress);
        if ($this->_shippingAddress != '')
            $this->_quote->getShippingAddress()->addData($this->_shippingAddress);
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod($this->_shippingMethod);
        $this->_quote->getPayment()->importData(array('method' => $this->_paymentMethod));
        $this->_quote->collectTotals()->save();
        $service = Mage::getModel('sales/service_quote', $this->_quote);
        $service->submitAll();
        $order = $service->getOrder();
        return $order;
    }

    public function getTillNameFromOrder() {
        if ($this->getTillId() != null) {
            $till = Mage::getModel('webpos/till')->load($this->getTillId());
            if ($till->getTillId()) {
                return $till->getTillName();
            }
        }
        return '';
    }

}
