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

 * SocialLogin Facebook Controller

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */

 



class Ced_SocialLogin_FacebookController extends Mage_Core_Controller_Front_Action

{

    protected $referer = null;

	

	/**

     * Action connect

     */

    public function connectAction()

    { 


        try {

            $this->_connectCallback();

        } catch (Exception $e) {

            Mage::getSingleton('core/session')->addError($e->getMessage());

        }



        if(!empty($this->referer)) {

            $this->_redirectUrl($this->referer);

        } else {

            Mage::helper('sociallogin')->redirect404($this);

        }

    }



	/**

     * Action disconnect

     */

    public function disconnectAction()

    {

        $customer = Mage::getSingleton('customer/session')->getCustomer();



        try {

            $this->_disconnectCallback($customer);

        } catch (Exception $e) {

            Mage::getSingleton('core/session')->addError($e->getMessage());

        }



        if(!empty($this->referer)) {

            $this->_redirectUrl($this->referer);

        } else {

            Mage::helper('sociallogin')->redirect404($this);

        }

    }



	/**

     * disconnect from facebook account

     */

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {

        $this->referer = Mage::getUrl('cedsociallogin/account/facebook');  

        

        Mage::helper('sociallogin/facebook')->disconnect($customer);



        Mage::getSingleton('core/session')

            ->addSuccess(

                $this->__('You have successfully disconnected your Facebook account from our store account.')

            );

    }



	/**

     * connect to facebook account

     */

    protected function _connectCallback() {

        $errorCode = $this->getRequest()->getParam('error');

        $code = $this->getRequest()->getParam('code');

        $state = $this->getRequest()->getParam('state');

        if(!($errorCode || $code) && !$state) {

            // Direct route access - deny
            return;

        }

        $this->referer = Mage::getSingleton('core/session')

            ->getFacebookRedirect();
		

        if(!$state || $state != Mage::getSingleton('core/session')->getFacebookCsrf()) {
            return;

        }
		
		Mage::getSingleton('core/session')->setFacebookCsrf('');


        if($errorCode) {

            // Facebook API read light - abort

            if($errorCode === 'access_denied') {

                Mage::getSingleton('core/session')

                    ->addNotice(

                        $this->__('Facebook Connect process aborted.')

                    );



                return;

            }



            throw new Exception(

                sprintf(

                    $this->__('Sorry, "%s" error occured. Please try again.'),

                    $errorCode

                )

            );



            return;

        }



        if ($code) {

            $client = Mage::getSingleton('sociallogin/facebook_client');



            $userInfo = $client->api('/me?fields=id,name,first_name,last_name,email');

            $token = $client->getAccessToken();



            $customersByFacebookId = Mage::helper('sociallogin/facebook')

                ->getCustomersByFacebookId($userInfo->id);



            if(Mage::getSingleton('customer/session')->isLoggedIn()) {

                // Logged in user

                if($customersByFacebookId->count()) {

                    // Facebook account already connected to other account - deny

                    Mage::getSingleton('core/session')

                        ->addNotice(

                            $this->__('Your Facebook account is already connected to one of our store accounts.')

                        );



                    return;

                }



                // Connect from account dashboard - attach

                $customer = Mage::getSingleton('customer/session')->getCustomer();



                Mage::helper('sociallogin/facebook')->connectByFacebookId(

                    $customer,

                    $userInfo->id,

                    $token

                );



                Mage::getSingleton('core/session')->addSuccess(

                    $this->__('Your Facebook account is now connected to your new user accout at our store. You can login next time by the Facebook SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')

                );



                return;

            }



            if($customersByFacebookId->count()) {

                // Existing connected user - login

                $customer = $customersByFacebookId->getFirstItem();



                Mage::helper('sociallogin/facebook')->loginByCustomer($customer);



                Mage::getSingleton('core/session')

                    ->addSuccess(

                        $this->__('You have successfully logged in using your Facebook account.')

                    );



                return;

            }



            $customersByEmail = Mage::helper('sociallogin/facebook')

                ->getCustomersByEmail($userInfo->email);



            if($customersByEmail->count()) {                

                // Email account already exists - attach, login

                $customer = $customersByEmail->getFirstItem();

                

                Mage::helper('sociallogin/facebook')->connectByFacebookId(

                    $customer,

                    $userInfo->id,

                    $token

                );



                Mage::getSingleton('core/session')->addSuccess(

                    $this->__('We find you already have an account at our store. Your Facebook account is now connected to your store account. Account confirmation mail has been sent to your email.')

                );



                return;

            }



            // New connection - create, attach, login

            if(empty($userInfo->first_name)) {

                throw new Exception(

                    $this->__('Sorry, could not retrieve your Facebook first name. Please try again.')

                );

            }



            if(empty($userInfo->last_name)) {

                throw new Exception(

                    $this->__('Sorry, could not retrieve your Facebook last name. Please try again.')

                );

            }



            Mage::helper('sociallogin/facebook')->connectByCreatingAccount(

                $userInfo->email,

                $userInfo->first_name,

                $userInfo->last_name,

                $userInfo->id,

                $token

            );



            Mage::getSingleton('core/session')->addSuccess(

                $this->__('Your Facebook account is now connected to your new user accout at our store. You can login next time by the Facebook SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')

            );

        }

    }



}