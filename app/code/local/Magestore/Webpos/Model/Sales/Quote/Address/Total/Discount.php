<?php
class Magestore_Webpos_Model_Sales_Quote_Address_Total_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract {
	public function collect(Mage_Sales_Model_Quote_Address $address) {
		$store = $address->getQuote()->getStore();
        $session = Mage::getSingleton('checkout/session');
		$discount = $session->getData('webpos_admin_discount');
        if(!$discount){
            return $this;
        }
		
		$items = $address->getAllItems();
		if (!count($items)) {
			return $this;
		}

		$session->setData('webpos_admin_discount',$discount);
		$address->setWebposDiscountAmount($discount);		
		$address->setData('webpos_discount_amount',$discount);
		/* Daniel - tax for discount */
		$afterDiscount = Mage::getStoreConfig('tax/calculation/apply_after_discount');
		if($afterDiscount){	
			$country = Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->getCountry();
			$oldTax =  $address->getTaxAmount();
			$oldBaseTax =  $address->getBaseTaxAmount();
			if($country){
				$rateTax = Mage::getModel('tax/calculation_rate')->getCollection()
										->addFieldToFilter('tax_country_id',$country)
										->setOrder('rate','DESC')
										->getFirstItem()
										;		
				$address->setTaxAmount(($address->getBaseSubtotal() -$discount+$address->getShippingAmount()+$address->getDiscountAmount())*$rateTax->getRate()/100)
						->setBaseTaxAmount(($address->getBaseSubtotal()  -$discount+$address->getShippingAmount()+$address->getDiscountAmount())*$rateTax->getRate()/100);
					
				$taxCalculationModel = Mage::getSingleton('tax/calculation');
				$request = Mage::getSingleton('tax/calculation')->getRateRequest(
					$address,
					$address->getQuote()->getBillingAddress(),
					$address->getQuote()->getCustomerTaxClassId(),
					$store
				);
				$rate = Mage::getSingleton('tax/calculation')->getRate($request);
					$this->_saveAppliedTaxes(
						  $address,
						  $taxCalculationModel->getAppliedRates($request),
						  $address->getTaxAmount(),
						  $address->getBaseTaxAmount(),
						  $rate
						 );
			}
			$address->setGrandTotal($address->getGrandTotal() - $address->getWebposDiscountAmount() );
			$address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getWebposDiscountAmount()  );
		}else{
			$address->setGrandTotal($address->getGrandTotal() - $address->getWebposDiscountAmount()  );
			$address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getWebposDiscountAmount());
		}	
		/* end */
		return $this;
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address) 
	{
		$amount = $address->getWebposDiscountAmount();		
		$title = Mage::helper('sales')->__('Discount(Admin)');
		if ($amount!=0) {
			$address->addTotal(array(
					'code'=>$this->getCode(),
					'title'=>$title,
					'value'=>'-'.$amount
			));
		}
		return $this;
	}
	
	protected function _saveAppliedTaxes(Mage_Sales_Model_Quote_Address $address, $applied, $amount, $baseAmount, $rate)
    {
       $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount;
                $baseAppliedAmount = $baseAmount;
			
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }


            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] = $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] = $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
		
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }
	
}
