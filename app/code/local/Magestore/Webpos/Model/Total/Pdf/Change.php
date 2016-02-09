<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Model_Total_Pdf_Change extends Mage_Sales_Model_Order_Pdf_Total_Default {

    public function getTotalsForDisplay() {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }
        $label = Mage::helper('webpos')->__($this->getTitle()) . ':';
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = array(
            'amount' => $amount,
            'label' => $label,
            'font_size' => $fontSize
        );
        return array($total);
    }

    public function getAmount() {
        return $this->getSource()->getWebposChange();
    }

}
