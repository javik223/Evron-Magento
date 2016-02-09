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
 * @created    5th Dec, 2014 2:13pm
 * @author     Clarion magento team<magento@clariontechnologies.co.in>
 * @purpose    One step checkout observer
 * @copyright  Copyright (c) 2014 Clarion Technologies Pvt.Ltd
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License
 */ 
class Clarion_OnestepCheckout_Model_Observer
{
    public function addHistoryComment($data)
    {
        $comment	= Mage::getSingleton('customer/session')->getOrderCustomerComment();
        $comment	= trim($comment); 
        if (!empty($comment))
			$data['order']->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(false);
    }

    public function removeHistoryComment()
    {
        Mage::getSingleton('customer/session')->setOrderCustomerComment(null);
    }
    
	public function setEmptyCartTemplate()
	{
		if (Mage::helper('onestepcheckout')->isOnestepCheckoutEnabled())
		{
			$cartHelper = Mage::helper('checkout/cart');
			$layout = Mage::getSingleton('core/layout');
	 
	        if (!$cartHelper->getItemsCount()){
				$layout->getBlock('checkout.cart')->setTemplate('onestepcheckout/cart/noItems.phtml');
			}
		}
    }    
}