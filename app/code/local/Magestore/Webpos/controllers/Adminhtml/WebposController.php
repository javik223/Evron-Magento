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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Adminhtml_WebposController extends Mage_Adminhtml_Controller_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$this->loadLayout();
        $this->renderLayout();
	}	
	
	public function gotowebposAction()
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$storeId = $this->getRequest()->getParam('webpos_storeid');
		$websiteId = $this->getRequest()->getParam('webpos_websiteid');
		$adminId = Mage::getModel('admin/session')->getUser()->getId();
		$key = Mage::getModel('adminhtml/session')->getSessionId();
		$adminLogin = $this->getUrl('webposadmin/adminhtml_webpos/index', array('_secure'=>true));
		$adminLogout = Mage::getBlockSingleton('adminhtml/page_header')->getLogoutLink();
		$random = md5(now());
		$cookieTime = Mage::getStoreConfig('web/cookie/cookie_lifetime');
		$code = md5($key.$random);
		
		/* Daniel - link to webpos settings */
		$settingslink = $this->getUrl("webposadmin/adminhtml_config/edit/",array("section"=>"webpos","frompos"=>true));
		/* end */
		/* Daniel - updated - fix error - Multiple store */
		$cookiesData = array();
		$cookiesData['cookieTime'] = $cookieTime;
		$cookiesData['webpos_admin_key'] = $key;
		$cookiesData['webpos_admin_code'] = $code;
		$cookiesData['webpos_admin_id'] = $adminId;
		$cookiesData['webpos_admin_adminlogin'] = $adminLogin;
		$cookiesData['webpos_admin_adminlogout'] = $adminLogout;
		$cookiesData['webpos_admin_settingslink'] = $settingslink;
		$cookiesData['storeid'] = $storeId;
		$cookiesData['websiteid'] = $websiteId;
	
		

		
		Mage::helper('webpos')->setWebPosCookies($cookiesData);

		$file = Mage::getBaseDir('media').DS.'magestore/webpos.xml';
		$user = Mage::getSingleton('admin/session');	 		
		$userFirstname = $user->getUser()->getFirstname();
		$userLastname = $user->getUser()->getLastname();
		$userUsername = $user->getUser()->getUsername();
		$data_file = array(
						   'webpos_admin_adminlogout'=>$adminLogout,
						   'webpos_admin_adminlogin'=>$adminLogin,
						   'firstname'=>$userFirstname,
						   'lastname'=>$userLastname,
						   'username'=>$userUsername,
							'webpos_admin_settingslink'=>$settingslink
						   );	
		Mage::getModel('webpos/file')->writeFile($data_file,$file);
		/* end */
		
		$webposAdmin = Mage::getModel('webpos/admin')->load($key,'key');
		if($webposAdmin->getId()){
			$webposAdmin->delete();
		}
		try{
			Mage::getModel('webpos/admin')
				->setData('key',$key)
				->setData('random',$random)
				->save();
		}catch(Exception $e){	
		}
		$urlRedirect = Mage::getModel('core/store')->load($storeId)->getUrl('webpos', array('_secure'=>true));
		header('Location:'.$urlRedirect);
		exit();
	}
        protected function _isAllowed()
        {
            return Mage::getSingleton('admin/session')->isAllowed('sales/webpos/gotopos');
        }
}