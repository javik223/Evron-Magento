<?php
class Magestore_Webpos_Model_Sales_Quote_Address_Total_Giftwrap extends Mage_Sales_Model_Quote_Address_Total_Abstract {
	public function collect(Mage_Sales_Model_Quote_Address $address) {

        $_helper = Mage::helper('webpos');
		$active = $_helper->enableGiftWrap();
		if (!$active)
		{
			return;
		} 
        $session = Mage::getSingleton('checkout/session');
		$giftwrap = $session->getData('webpos_giftwrap');
        if(!$giftwrap){
            return $this;
        }
		
		$items = $address->getAllItems();
		if (!count($items)) {
			return $this;
		}
        
		$giftwrapType = $_helper->getGiftwrapType();
		$giftwrapAmount = $_helper->getGiftwrapAmount();
     
        $wrapTotal = 0;
        if($giftwrapType == 1) {
            foreach ($items as $item){
				if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $wrapTotal += $giftwrapAmount * ($item->getQty());
            }
        }
        else {
            $wrapTotal = $giftwrapAmount;
        }		
		$session->setData('webpos_giftwrap_amount', $wrapTotal);
		$address->setWebposGiftwrapAmount($wrapTotal);		
		$address->setGrandTotal($address->getGrandTotal() + $address->getWebposGiftwrapAmount());
		$address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getWebposGiftwrapAmount());	
		return $this;
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address) 
	{
		$_helper = Mage::helper('webpos');
		$active = $_helper->enableGiftWrap();
		if (!$active)
		{
			return;
		} 
		$amount = $address->getWebposGiftwrapAmount();		
		$title = Mage::helper('sales')->__('Gift Wrap');
		if ($amount!=0) {
			$address->addTotal(array(
					'code'=>$this->getCode(),
					'title'=>$title,
					'value'=>$amount
			));
		}
		return $this;
	}
}
