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
class Magestore_Webpos_Block_Checkout_Cart extends Mage_Checkout_Block_Cart {

    public function addWebposItemRender(){
        if(Mage::helper('webpos')->getActiveRewardPointsRule()){
            $template = 'webpos/admin/webpos/checkout/cart/item/default.phtml';
            $nameModule = 'rewardpointsrule';
        }
        else{
            $template = 'checkout/cart/item/default.phtml';
            $nameModule = 'checkout';
        }
        $this->addItemRender('default', $nameModule.'/cart_item_renderer', $template);
        $this->addItemRender('simple', $nameModule.'/cart_item_renderer', $template);
        $this->addItemRender('grouped', $nameModule.'/cart_item_renderer_grouped', $template);
        $this->addItemRender('configurable', $nameModule.'/cart_item_renderer_configurable', $template);
        $this->addItemRender('bundle', 'bundle/checkout_cart_item_renderer', $template);
		/* Daniel - add downloadable item renderer */
        $this->addItemRender('downloadable','downloadable/checkout_cart_item_renderer', 'downloadable/checkout/cart/item/default.phtml');
		/* end */
	}
}