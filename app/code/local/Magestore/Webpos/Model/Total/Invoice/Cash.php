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
 * SimiPOS Invoice Cash Total Model
 * 
 * @category    Magestore
 * @package     Magestore_SimiPOS
 * @author      Magestore Developer
 */
class Magestore_WebPOS_Model_Total_Invoice_Cash extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order = $invoice->getOrder();
        if ($order->getWebposCash() < 0.0001 || $order->getGrandTotal() < 0.0001) {
            return;
        }
        if ($invoice->isLast()) {
            $cash = $order->getWebposCash();
            $baseCash = $order->getWebposBaseCash();
            foreach ($order->getInvoiceCollection() as $existedInvoice) {
                if ($cash > 0.0001) {
                    $cash -= $existedInvoice->getWebposCash();
                    $baseCash -= $existedInvoice->getWebposBaseCash();
                }
            }
        } else {
            $ratio = $invoice->getGrandTotal() / $order->getGrandTotal();
            $cash = $order->getWebposCash() * $ratio;
            $baseCash = $order->getWebposBaseCash() * $ratio;

            $maxcash = $order->getWebposCash();
            $maxbaseCash = $order->getWebposBaseCash();
            foreach ($order->getInvoiceCollection() as $existedInvoice) {
                if ($maxcash > 0.0001) {
                    $maxcash -= $existedInvoice->getWebposCash();
                    $maxbaseCash -= $existedInvoice->getWebposBaseCash();
                }
            }
            if ($cash > $maxcash) {
                $cash = $maxcash;
                $baseCash = $maxbaseCash;
            }
        }
        if ($cash > 0.0001) {
            $invoice->setWebposCash($cash)
                ->setWebposBaseCash($baseCash);
            /*
            if ($invoice->getGrandTotal() <= $cash) {
                $invoice->setBaseGrandTotal(0.0)
                    ->setGrandTotal(0.0);
            } else {
                $invoice->setGrandTotal($invoice->getGrandTotal() - $cash)
                    ->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseCash);
            }
             */
        }
    }

}
