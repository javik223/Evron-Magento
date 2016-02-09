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

 * SocialLogin 	Twitter/Userdetails block

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */



class Ced_SocialLogin_Model_Twitter_Userdetails

{

    protected $client = null;

    protected $userInfo = null;



    public function __construct() {

        if(!Mage::getSingleton('customer/session')->isLoggedIn())

            return;



        $this->client = Mage::getSingleton('sociallogin/twitter_client');

        if(!($this->client->isEnabled())) {

            return;

        }



        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if(($socialloginTid = $customer->getCedSocialloginTid()) &&

                ($socialloginTtoken = $customer->getCedSocialloginTtoken())) {

            $helper = Mage::helper('sociallogin/twitter');



            try{

                $this->client->setAccessToken($socialloginTtoken);

                

                $this->userInfo = $this->client->api('/account/verify_credentials.json', 'GET', array('skip_status' => true)); 



            }  catch (Ced_SocialLogin_TwitterOAuthException $e) {

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