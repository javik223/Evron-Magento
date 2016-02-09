<?php 
class Magestore_Webpos_Model_Shipping_Carrier_Webposshipping extends Mage_Shipping_Model_Carrier_Abstract
									implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'webpos_shipping';
	

	public function getAllowedMethods(){
		return array(
			'standard'    =>  'Standard delivery',
			'express'     =>  'Express delivery',
		);
	}

	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		$result = Mage::getModel('shipping/rate_result');
		foreach ($request->getAllItems() as $item) {
			
		}
		$result->append($this->_getStandardRate());
	 
		return $result;
	}
	
	protected function _getStandardRate()
	{
		$rate = Mage::getModel('shipping/rate_result_method');
		$rate->setCarrier($this->_code);
		$rate->setCarrierTitle($this->getConfigData('carrier_title'));
		$rate->setMethod('free');
		$rate->setMethodTitle($this->getConfigData('method_title'));
		$rate->setPrice($this->getConfigData('price'));
		$rate->setCost();
	 
		return $rate;
	}
	
	/**
     * Get Express rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getExpressRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('free');
        $rate->setMethodTitle('Free Shipping');
        $rate->setPrice(0);
        $rate->setCost(0);

        return $rate;
    }
	
	public function isTrackingAvailable(){
		return true;
	}
	
	/**
     * Determine whether current carrier enabled for activity
     *
     * @return bool
     */
    public function isActive()
    {
		$active = $this->getConfigData('active');
		$routeName = Mage::app()->getRequest()->getRouteName();
		if($routeName == "webpos" && ($active==1 || $active=='true'))
			return true;
        return false;
    }
	
	 public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
		$active = $this->getConfigData('active');
		$routeName = Mage::app()->getRequest()->getRouteName();
		if($routeName == "webpos" && ($active==1 || $active=='true'))
			return true;
        return false;
	}
	
	public function getCarrierName(){
		return $this->getConfigData('carrier_title');
	}
	
	public function getMethodCode(){
		return $this->_code . '_free';
	}
	
	public function getMethodName(){
		return $this->getConfigData('method_title');
	}
	
	public function getMethodPrice($a = 0, $b = 0){
		return $this->getConfigData('price');
	}
	
}
?>