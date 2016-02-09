<?php
 /**
 * Magento Community Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Clarion
 * @package    Clarion_OnestepCheckout
 * @created    5th Dec, 2014 5:25pm
 * @author     Clarion magento team<magento@clariontechnologies.co.in>
 * @purpose    Quote service model validate quote data before converting to order
 * @copyright  Copyright (c) 2014 Clarion Technologies Pvt.Ltd
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License
 */
class Clarion_OnestepCheckout_Model_Service_Quote extends Mage_Sales_Model_Service_Quote
{
    protected function _validate()
    {
        $helper = Mage::helper('sales');
        if (!$this->getQuote()->isVirtual())
        {
            $address = $this->getQuote()->getShippingAddress();
            $addrValidator = Mage::getSingleton('onestepcheckout/type_geo')->validateAddress($address);
            if ($addrValidator !== true)
                Mage::throwException($helper->__('Please check shipping address information. %s', implode(' ', $addrValidator)));

            $ship_method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($ship_method);
            if (!$this->getQuote()->isVirtual() && (!$ship_method || !$rate))
                Mage::throwException($helper->__('Please specify a shipping method.'));
        }

        $addrValidator = Mage::getSingleton('onestepcheckout/type_geo')->validateAddress($this->getQuote()->getBillingAddress());

        if ($addrValidator !== true)
            Mage::throwException($helper->__('Please check billing address information. %s', implode(' ', $addrValidator)));

        if (!($this->getQuote()->getPayment()->getMethod()))
			Mage::throwException($helper->__('Please select a valid payment method.'));

        return $this;
    }
}
