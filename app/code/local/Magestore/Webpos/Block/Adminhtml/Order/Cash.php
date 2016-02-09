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
 * @package     Magestore_SimiPOS
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * WebPOS Order Cash Total Block
 * 
 * @category    Magestore
 * @package     Magestore_WebPOS
 * @author      Magestore Developer
 */
class Magestore_Webpos_Block_Adminhtml_Order_Cash extends Mage_Sales_Block_Order_Totals
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $order = $totalsBlock->getOrder();
        
        if ($order->getWebposCash() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'webpos_cash',
                'label' => $this->__('Amount Tendered'),
                'value' => $order->getWebposCash(),
                'strong'=> true,
            )));
        }
    }
}
