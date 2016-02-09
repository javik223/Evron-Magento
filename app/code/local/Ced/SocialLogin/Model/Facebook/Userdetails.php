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

 * SocialLogin Facebook/Userdetails Model

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */

 

 

class Ced_SocialLogin_Model_Facebook_Userdetails

{



    protected $client = null;

    protected $userDetails = null;



    public function __construct() {

        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            return;
        }


        $this->client = Mage::getSingleton('sociallogin/facebook_client');

        if(!($this->client->isEnabled())) {
            return;

        }



        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if(($socialLoginFid = $customer->getCedSocialloginFid()) &&

                ($socialLoginFtoken = $customer->getCedSocialloginFtoken())) {

            $helper = Mage::helper('sociallogin/facebook');



            try{

                $this->client->setAccessToken($socialLoginFtoken);

                $this->userDetails = $this->client->api(

                    '/me',

                    'GET',

                    array(

                        'fields' =>

                        'id,name,first_name,last_name,link,birthday,gender,email,picture.type(large)'

                    )

                );


            } catch(FacebookOAuthException $e) {
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

        return $this->userDetails;

    }

}