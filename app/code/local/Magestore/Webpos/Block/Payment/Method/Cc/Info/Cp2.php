<?php

class Magestore_Webpos_Block_Payment_Method_Cc_Info_Cp2 extends Mage_Payment_Block_Info {
    /*
      This block will show the payment method information
     */

    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $data = array();
        if ($this->getInfo()->getData('cp2forpos_ref_no')) {
            $data[Mage::helper('payment')->__('Reference No')] = $this->getInfo()->getData('cp2forpos_ref_no');
        }

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport->setData(array_merge($data, $transport->getData()));
    }

    protected function _construct() {
        parent::_construct();
    }

    public function getMethodTitle() {
        return Mage::helper('webpos/payment')->getCp2MethodTitle();
    }

}
