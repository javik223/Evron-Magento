<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_WebPOS_Block_Adminhtml_Invoice_Change extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $invoice = $totalsBlock->getInvoice();
        
        if ($invoice->getWebposChange() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'webpos_change',
                'label' => $this->__('POS Change'),
                'value' => $invoice->getWebposChange(),
            )));
        }
    }
}
