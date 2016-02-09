<?php
class Magestore_Webpos_Model_Customer_Config_Share extends Mage_Customer_Model_Config_Share
{
	/* Daniel -  share customer account in webpos page 
	
	public function isWebsiteScope()
    {

		$actionName = Mage::app()->getRequest()->getActionName();
		$routeName = Mage::app()->getRequest()->getRouteName();
		$params = Mage::app()->getRequest()->getParams();
		if(Mage::getStoreConfig(self::XML_PATH_CUSTOMER_ACCOUNT_SHARE) == self::SHARE_WEBSITE){
			
			if( $actionName == "is_valid_email"){		
				return true;
			}
			else {
				if($routeName != 'webpos' || $routeName != 'rewardpointsrule') return true;
				return false;	
			}
		}else return false;
    }*/
}
?>