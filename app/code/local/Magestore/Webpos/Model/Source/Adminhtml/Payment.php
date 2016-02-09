<?php

class Magestore_Webpos_Model_Source_Adminhtml_Payment {

    protected $_allowPayments = array();

    public function __construct() {
        $this->_allowPayments = array('cp1forpos', 'cp2forpos', 'cashforpos', 'ccforpos', 'paypal_direct');
    }

    public function toOptionArray() {
        $collection = Mage::getModel('payment/config')->getAllMethods();

        if (!count($collection))
            return;

        $options = array();
        foreach ($collection as $item) {
            if (!in_array($item->getId(), $this->_allowPayments))
                continue;
            $title = $item->getTitle() ? $item->getTitle() : $item->getId();
            $options[] = array('value' => $item->getId(), 'label' => $title);
        }

        return $options;
    }

    public function getAllowPaymentMethods() {
        return $this->_allowPayments;
    }

}
