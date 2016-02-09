<?php
class Magestore_Webpos_Model_Source_Reloadpayment {

	public function toOptionArray() 
	{
		$options = array();		
		$options[] = array('label' => 'When all required fields are filled', 'value' => '1');
		$options[] = array('label' => 'When any triggering field changes', 'value' => '2');
		return $options;
	}
}