<?php

/*
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Sales_Totals extends Mage_Core_Block_Template {

    protected $additionFieldsToDisplay = array();

    public function _construct() {
        $this->setTemplate('webpos/webpos/orderlist/ordertotals.phtml');
    }

    /*
     * Get Subtotal
     */

    public function getSubtotal() {
        $subtotal = Mage::helper('tax')->displaySalesSubtotalExclTax() ? $this->getOrder()->getSubtotal() : $this->getOrder()->getSubtotalInclTax();
        return $this->getOrder()->formatPrice($subtotal);
    }

    /*
     * Get Discount amount
     */

    public function getDiscountAmount() {
        return $this->getOrder()->formatPrice(str_replace('-', '', $this->getOrder()->getData('discount_amount')));
    }

    public function getGiftcardDiscount() {
        if (!$this->getOrder()->getGiftVoucherDiscount() || $this->getOrder()->getGiftVoucherDiscount() <= 0) {
            return false;
        }
        return $this->getOrder()->formatPrice($this->getOrder()->getGiftVoucherDiscount());
    }

    public function getShippingAmount() {
        switch (Mage::getStoreConfig('tax/sales_display/shipping')) {
            case '1': #Exclude
                return $this->getOrder()->formatPrice($this->getOrder()->getShippingAmount());
            case '2': #Include
                return $this->getOrder()->formatPrice($this->getOrder()->getShippingInclTax());
            case '3': #Exclude & Include
                #return $this->getOrder()->formatPrice($this->getOrder()->getShippingAmount()) . " (Incl. Tax {$this->getOrder()->formatPrice($this->getOrder()->getShippingInclTax())})";
                return $this->getOrder()->formatPrice($this->getOrder()->getShippingInclTax());
        }
    }

    public function getTaxesAmount() {
        return $this->getOrder()->formatPrice($this->getOrder()->getTaxAmount());
    }

    public function getStoreCredit() {
        if ($this->getOrder()->getCustomercreditDiscount() != 0) {
            return $this->getOrder()->formatPrice($this->getOrder()->getCustomercreditDiscount());
        }
        return false;
    }

    public function getGrandTotal() {
        return $this->getOrder()->formatPrice($this->getOrder()->getGrandTotal());
    }

    public function getTotalPaid() {
        if ($this->getOrder()->getStatus() == 'pending')
            return $this->getOrder()->formatPrice($this->getOrder()->getTotalPaid());
        return false;
    }

    public function getBalance() {
        if ($this->getOrder()->getStatus() == 'pending') {
            return $this->getOrder()->formatPrice($this->getOrder()->getTotalDue());
            /*
              $balance = $this->getOrder()->getTotalPaid() - $this->getOrder()->getGrandTotal();

              if ($balance > 0) {
              return $this->getOrder()->formatPrice($balance);

              }
             */
        }
        return false;
    }

    public function getWebposCash() {
        if ($this->getOrder()->getWebposCash() > 0) {
            return $this->getOrder()->formatPrice($this->getOrder()->getWebposCash());
        }
        return false;
    }
    public function getWebposChange() {
        if ($this->getOrder()->getWebposChange() > 0) {
            return $this->getOrder()->formatPrice($this->getOrder()->getWebposChange());
        }
        return false;
    }

    public function getTotalPaidFromTransaction() {
        $orderId = $this->getOrder()->getIncrementId();
        $transaction = $this->loadByField($this->getTransaction(), 'order_id', $orderId);
        $cashIn = 0;
        foreach ($transaction as $tran) {
            $cashIn+= $tran->getData('cash_in');
        }
        return $this->getOrder()->formatPrice($cashIn);
    }

    private function getTransaction() {
        return Mage::getSingleton('webpos/transaction')->getCollection();
    }

    public function loadByField($collection, $field, $value) {
        $collection = $collection
                ->addFieldToFilter($field, array('eq' => $value));

        return $collection;
    }

    public function getRefundedAmount() {
        if (!$this->getOrder()->getTotalRefunded() || $this->getOrder()->getTotalRefunded() <= 0) {
            return false;
        }
        return $this->getOrder()->formatPrice($this->getOrder()->getTotalRefunded());
    }

	public function getWebposCc() {
        if ($this->getOrder()->getData('webpos_base_ccforpos') > 0) {
            return $this->getOrder()->formatPrice($this->getOrder()->getData('webpos_base_ccforpos'));
        }
        return false;
    }
	public function getWebposCp1() {
        if ($this->getOrder()->getData('webpos_base_cp1forpos') > 0) {
            return $this->getOrder()->formatPrice($this->getOrder()->getData('webpos_base_cp1forpos'));
        }
        return false;
    }
	public function getWebposCp2() {
        if ($this->getOrder()->getData('webpos_base_cp2forpos') > 0) {
            return $this->getOrder()->formatPrice($this->getOrder()->getData('webpos_base_cp2forpos'));
        }
        return false;
    }	
	public function getWebposPaypal() {
		$payment = $this->getOrder()->getPayment()->getMethod();
        if ($payment == 'paypal_direct') {
            return $this->getOrder()->formatPrice($this->getOrder()->getData('grand_total'));
        }
        return false;
    }
}
