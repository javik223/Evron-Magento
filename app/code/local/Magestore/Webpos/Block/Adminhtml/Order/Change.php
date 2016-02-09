<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Block_Adminhtml_Order_Change extends Mage_Sales_Block_Order_Totals
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $order = $totalsBlock->getOrder();
        
        if ($order->getWebposChange() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'webpos_change',
                'label' => $this->__('POS Change'),
                'value' => $order->getWebposChange(),
                'strong'=> true,
            )));
        }
    }
}
