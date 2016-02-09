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

 * SocialLogin Account Controller

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */
class Ced_SocialLogin_AccountController extends Mage_Core_Controller_Front_Action
{
	/**

     * Action predispatch

     */

    public function preDispatch()

    {

        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {

            return;

        }

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {

            $this->setFlag('', 'no-dispatch', true);

        }

    }    



    public function googleAction()

    {        



        $userDetails = Mage::getSingleton('sociallogin/google_userdetails')

                ->getUserDetails();

        
        Mage::register('ced_sociallogin_google_userdetails', $userDetails);

        

        $this->loadLayout();

        $this->renderLayout();

    }

    

    public function facebookAction()

    {      

        $userDetails = Mage::getSingleton('sociallogin/facebook_userdetails')

            ->getUserDetails();

        Mage::register('ced_sociallogin_facebook_userdetails', $userDetails);

        

        $this->loadLayout();

        $this->renderLayout();

    }    

    

    public function twitterAction()

    {        

        // Cache user info inside customer session due to Twitter window frame rate limits

        if(!($userDetails = Mage::getSingleton('customer/session')

                ->getCedSocialLoginTwitterUserdetails())) {

            $userDetails = Mage::getSingleton('sociallogin/twitter_userdetails')

                ->getUserDetails();

            

            Mage::getSingleton('customer/session')->setCedSocialLoginTwitterUserdetails($userDetails);

        }

        

        Mage::register('ced_sociallogin_twitter_userdetails', $userDetails);

        

        $this->loadLayout();

        $this->renderLayout();

    }    
	
	
	
	public function linkedinAction()

    {      

        $userDetails = Mage::getSingleton('sociallogin/linkedin_userdetails')

            ->getUserDetails();
        Mage::register('ced_sociallogin_linkedin_userdetails', $userDetails);

        

        $this->loadLayout();

        $this->renderLayout();

    }    

    

}

