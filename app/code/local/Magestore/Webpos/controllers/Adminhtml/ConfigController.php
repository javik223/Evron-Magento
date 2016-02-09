<?php
require_once("Mage/Adminhtml/controllers/System/ConfigController.php");
class Magestore_Webpos_Adminhtml_ConfigController extends Mage_Adminhtml_System_ConfigController
{
	/* Daniel - link to webpos settings */
	protected function _validateSecretKey()
    {
        $isWebPOS = $this->getRequest()->getParam('frompos');
		if($isWebPOS) 
			return true;
		else
			return parent::_validateSecretKey();
    }
	/* end */
}
?>