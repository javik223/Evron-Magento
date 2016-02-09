<?php
class Magestore_Webpos_Model_Sales_Order_Creditmemo_Total_Discount extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {
	/* old code - error
	public function collect(Mage_Sales_Model_Order_Invoice $creditmemo) {
	*/
	/* Daniel - fixed */
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
		$creditmemo->setWebposDiscountAmount(0);        
        $orderWebposDiscount = $creditmemo->getOrder()->getWebposDiscountAmount();		
        if ($orderWebposDiscount) {
            $creditmemo->setWebposDiscountAmount($orderWebposDiscount);           
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal()-$orderWebposDiscount);
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()-$orderWebposDiscount);			
        }
        return $this;
	}
}