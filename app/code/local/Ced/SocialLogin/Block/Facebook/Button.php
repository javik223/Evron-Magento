<?php

/**

 * CedCommerce

 *

 * NOTICE OF LICENSE

 *

 * This source file is subject to the Open Software License (OSL 3.0)

 * that is bundled with this package in the file LICENSE.txt.

 * It is also available through the world-wide-web at this URL:

  * http://opensource.org/licenses/osl-3.0.php

 *

 * @category    Ced

 * @package     Ced_SocialLogin

 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)

 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 */



/**

 * SocialLogin 	Facebook/Button block

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */





class Ced_SocialLogin_Block_Facebook_Button extends Mage_Core_Block_Template

{

    protected $client = null;

    protected $userInfo = null;

    protected $redirectUri = null;



    protected function _construct() {

        parent::_construct();



        $this->client = Mage::getSingleton('sociallogin/facebook_client');

        if(!($this->client->isEnabled())) {

            return;

        }



        $this->userInfo = Mage::registry('ced_sociallogin_facebook_userdetails');

        

        // CSRF protection
		
		if(!Mage::getSingleton('core/session')->getFacebookCsrf() || Mage::getSingleton('core/session')->getFacebookCsrf()=='') {
		
			$csrf = md5(uniqid(rand(), TRUE));
	
			Mage::getSingleton('core/session')->setFacebookCsrf($csrf);
		} else {
			$csrf = Mage::getSingleton('core/session')->getFacebookCsrf();
		}
			$this->client->setState($csrf);
		

        
        if(!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {

            $redirect = Mage::helper('core/url')->getCurrentUrl();      

        }        

        

        // Redirect uri
        Mage::getSingleton('core/session')->setFacebookRedirect($redirect);        



        $this->setTemplate('ced/sociallogin/facebook/button.phtml');

    }



    protected function _getButtonUrl()

    {

        if(empty($this->userInfo)) {

            return $this->client->createAuthUrl();

        } else {

            return $this->getUrl('cedsociallogin/facebook/disconnect');

        }

    }



    protected function _getButtonText()

    {

        if(empty($this->userInfo)) {

            if(!($text = Mage::registry('ced_sociallogin_button_text'))){

                $text = $this->__('Connect');

            }

        } else {

            $text = $this->__('Disconnect');

        }

        

        return $text;

    }


    protected function _getButtonClass()

    {

        if(empty($this->userInfo)) {


                $text = "ced_fb_connect";


        } else {

                $text = "ced_fb_disconnect";

        }

        

        return $text;

    }



}

