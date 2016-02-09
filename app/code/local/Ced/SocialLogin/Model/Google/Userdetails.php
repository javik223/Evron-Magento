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

 * SocialLogin 	google/Userdetails Model

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */



class Ced_SocialLogin_Model_Google_Userdetails

{

    protected $client = null;

    protected $userInfo = null;



    public function __construct() {

        if(!Mage::getSingleton('customer/session')->isLoggedIn())

            return;



        $this->client = Mage::getSingleton('sociallogin/google_client');

        if(!($this->client->isEnabled())) {

            return;

        }



        $customer = Mage::getSingleton('customer/session')->getCustomer();
        
        if(($sociallogintGid = $customer->getCedSocialloginGid()) &&

                ($socialloginGtoken = $customer->getCedSocialloginGtoken())) {

            $helper = Mage::helper('sociallogin/google');



            try{

                $this->client->setAccessToken($socialloginGtoken);



                $this->userInfo = $this->client->api('/userinfo');



                /* The access token may have been updated automatically due to

                 * access type 'offline' */

                $customer->setCedSocialloginGtoken($this->client->getAccessToken());

                $customer->save();



            } catch(Ced_SocialLogin_GoogleOAuthException $e) {

                $helper->disconnect($customer);

                Mage::getSingleton('core/session')->addNotice($e->getMessage());

            } catch(Exception $e) {

                $helper->disconnect($customer);

                Mage::getSingleton('core/session')->addError($e->getMessage());

            }



        }

    }



    public function getUserDetails()

    {

        return $this->userInfo;

    }

}