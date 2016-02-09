<?php
class Magestore_Webpos_Model_Sales_Order_Creditmemo_Total_Giftwrap extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {
	/*  old code - error
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
	*/
	
	/* Daniel - fixed */
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
		$creditmemo->setWebposGiftwrapAmount(0);        

        $orderGiftwrapAmount = $creditmemo->getOrder()->getWebposGiftwrapAmount();		
        $baseOrderShippingAmount = $creditmemo->getOrder()->getWebposGiftwrapAmount();
        if ($orderGiftwrapAmount) {
            $creditmemo->setWebposGiftwrapAmount($orderGiftwrapAmount);           
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$orderGiftwrapAmount);
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$orderGiftwrapAmount);			
        }
        return $this;
	}
}