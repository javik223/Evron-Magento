<?php

class Magestore_Webpos_Model_Payment_Method_Custompayment2 extends Mage_Payment_Model_Method_Abstract {
    /* This model define payment method */

    protected $_code = 'cp2forpos';
    protected $_infoBlockType = 'webpos/payment_method_cc_info_cp2';
    protected $_formBlockType = 'webpos/payment_method_cc_ccforpos';

    public function isAvailable($quote = null) {
        $routeName = Mage::app()->getRequest()->getRouteName();
        $ccenabled = Mage::helper('webpos/payment')->isCp2PaymentEnabled();
        if ($routeName == "webpos" && $ccenabled == true)
            return true;
        else
            return false;
    }

    public function assignData($data) {
        $info = $this->getInfoInstance();

        if ($data->getData('cp2forpos_ref_no')) {
            $info->setData('cp2forpos_ref_no', $data->getData('cp2forpos_ref_no'));
        }

        return $this;
    }

    public function validate() {
        parent::validate();
        $info = $this->getInfoInstance();
        /*
          if (!$info->getData('ccforpos_ref_no')) {
          $errorCode = 'invalid_data';
          $errorMsg = $this->_getHelper()->__("Reference No is a required field.\n");
          }
         */
        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }
        return $this;
    }

}

?>
