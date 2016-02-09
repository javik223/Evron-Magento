<?php
class Magestore_Webpos_Model_Sales_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
	
	public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
		$invoice->setWebposDiscountAmount(0);        
        $orderWebposDiscount = $invoice->getOrder()->getWebposDiscountAmount();		
        if ($orderWebposDiscount) {
            $invoice->setWebposDiscountAmount($orderWebposDiscount);           
            $invoice->setGrandTotal($invoice->getGrandTotal()-$orderWebposDiscount);
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()-$orderWebposDiscount);			
        }
        return $this;
	}
}