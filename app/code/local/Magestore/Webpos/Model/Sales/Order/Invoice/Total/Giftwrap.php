<?php
class Magestore_Webpos_Model_Sales_Order_Invoice_Total_Giftwrap extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
	
	public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
		$invoice->setWebposGiftwrapAmount(0);        
        $orderGiftwrapAmount = $invoice->getOrder()->getWebposGiftwrapAmount();		
        $baseOrderShippingAmount = $invoice->getOrder()->getWebposGiftwrapAmount();
        if ($orderGiftwrapAmount) {
            $invoice->setWebposGiftwrapAmount($orderGiftwrapAmount);           
            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderGiftwrapAmount);
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$orderGiftwrapAmount);			
        }
        return $this;
	}
}