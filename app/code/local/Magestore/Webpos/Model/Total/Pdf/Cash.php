<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_SimiPOS
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * SimiPOS Pdf Cash Model
 * 
 * @category    Magestore
 * @package     Magestore_SimiPOS
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Total_Pdf_Cash extends Mage_Sales_Model_Order_Pdf_Total_Default {

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
        return -$this->getSource()->getWebposCash();
    }

}
