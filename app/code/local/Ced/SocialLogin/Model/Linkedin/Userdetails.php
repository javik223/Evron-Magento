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

 * SocialLogin 	Linkedin/Userdetails Model

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */



class Ced_SocialLogin_Model_Linkedin_Userdetails

{

    protected $client = null;

    protected $userDetails = null;

    protected $userInfoApi = array(
                        'id',
                        'first-name',
                        'last-name',
                        'headline',
                        'picture-url',
                        'email-address',
                        'phone-numbers',
                        'location'
                    );



    public function __construct() {
	

        if(!Mage::getSingleton('customer/session')->isLoggedIn())

            return;



        $this->client = Mage::getSingleton('sociallogin/linkedin_client');

        if(!($this->client->isEnabled())) {
            return;

        }



        $customer = Mage::getSingleton('customer/session')->getCustomer();
        
		
		
        if(($sociallogintLid = $customer->getCedSocialloginLid()) &&

                ($socialloginLtoken = $customer->getCedSocialloginLtoken())) {

            $helper = Mage::helper('sociallogin/linkedin');



            try{

                $this->client->setAccessToken($socialloginLtoken);


                //$this->userDetails = $this->client->api($this->userInfoApi);
				
				
				$userInfoApi = array(
                        'id',
                        'first-name',
                        'last-name',
                        'headline',
                        'picture-url',
                        'email-address',
                        'phone-numbers',
                        'location'
                    );
					
				$this->userDetails = $this->client->api('/people/~:('.implode(',', $userInfoApi).')?format=json');
				
				/****/



                /* The access token may have been updated automatically due to

                 * access type 'offline' */

                $customer->setCedSocialloginGtoken($this->client->getAccessToken());

                $customer->save();



            } catch(Ced_SocialLogin_LinkedinOAuthException $e) {

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