<?php 
class Magestore_Webpos_Model_Sales_Quote extends Mage_Sales_Model_Quote
{
	/**
     * Validate quote state to be integrated with Web POS page process
     */
    public function isAllowedGuestCheckout()
    {	
		$routeName = Mage::app()->getRequest()->getRouteName();
        if ($routeName == "webpos" ) return true;
		return parent::isAllowedGuestCheckout();
    }
}
?>