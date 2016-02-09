<?php

class Magestore_Webpos_Model_Giftwrap extends Mage_Core_Model_Abstract {

    public function toOptionArray() {
        return array(
            0 => Mage::helper('webpos')->__('Per Order'),
            1 => Mage::helper('webpos')->__('Per Item')
        );
    }

    public function paypal_prepare_line_items($observer) {
        $paypalCart = $observer->getEvent()->getPaypalCart();
        if ($paypalCart) {
            $salesEntity = $paypalCart->getSalesEntity();
            if (Mage::getModel('checkout/session')->getData('webpos_giftwrap_amount') > 0) {
                $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL, abs((float) Mage::getModel('core/session')->getData('webpos_giftwrap_amount')), Mage::helper('webpos')->__('Giftwrap'));
            }
        }
        /* webpos cashin */
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            if ($paypalCart = $observer->getPaypalCart()) {
                $salesEntity = $paypalCart->getSalesEntity();

                $baseDiscount = $salesEntity->getWebposBaseCash();
                if ($baseDiscount > 0.0001) {
                    $paypalCart->updateTotal(
                        Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, (float) $baseDiscount, Mage::helper('webpos')->__('Amount Tendered')
                    );
                }
            }
            return;
        }
        $salesEntity = $observer->getSalesEntity();
        $additional = $observer->getAdditional();
        if ($salesEntity && $additional) {
            $baseDiscount = $salesEntity->getWebposBaseCash();
            if ($baseDiscount > 0.0001) {
                $items = $additional->getItems();
                $items[] = new Varien_Object(array(
                    'name' => Mage::helper('webpos')->__('Cash In'),
                    'qty' => 1,
                    'amount' => -(float) $baseDiscount,
                ));
                $additional->setItems($items);
            }
        }
        /**/
    }

}
