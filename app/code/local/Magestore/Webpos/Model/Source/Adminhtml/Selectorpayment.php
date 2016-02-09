<?php

class Magestore_Webpos_Model_Source_Adminhtml_Selectorpayment
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('webpos')->__('All Allowed Payments')),
            array('value'=>1, 'label'=>Mage::helper('webpos')->__('Specific Payments')),
        );
    }
}
