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
 * @author      CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * SocialLogin Twitter Controller
 *
 * @category    Ced
 * @package     Ced_SocialLogin
 * @author      CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */
 
class Ced_SocialLogin_TwitterController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;

    public function requestAction()
    {
        $client = Mage::getSingleton('sociallogin/twitter_client');
        if(!($client->isEnabled())) {
            Mage::helper('sociallogin')->redirect404($this);
        }

        $client->fetchRequestToken();
    }   

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
    
    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        $this->referer = Mage::getUrl('cedsociallogin/account/twitter');  
        
        Mage::helper('sociallogin/twitter')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Twitter account from our store account.')
            );
    }     

    protected function _connectCallback() {
        if (!($params = $this->getRequest()->getParams())
            ||
            !($requestToken = unserialize(Mage::getSingleton('core/session')
                ->getTwitterRequestToken()))
            ) {
            // Direct route access - deny
            return;
        }

        $this->referer = Mage::getSingleton('core/session')->getTwitterRedirect();
        
        if(isset($params['denied'])) {
            Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Twitter Connect process aborted.')
                    );
            
            return;
        }       

        $client = Mage::getSingleton('sociallogin/twitter_client');

        $token = $client->getAccessToken();
        
        $userInfo = (object) array_merge(
                (array) ($userInfo = $client->api('/account/verify_credentials.json', 'GET', array('skip_status' => true))),
                array('email' => sprintf('%s@twitter-user.com', strtolower($userInfo->screen_name)))
        );

        $customersByTwitterId = Mage::helper('sociallogin/twitter')
            ->getCustomersByTwitterId($userInfo->id);

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            // Logged in user
            if($customersByTwitterId->count()) {
                // Twitter account already connected to other account - deny
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Your Twitter account is already connected to one of our store accounts.')
                    );
                return;
            }

            // Connect from account dashboard - attach
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            Mage::helper('sociallogin/twitter')->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Twitter account is now connected to your store accout. You can now login using our Twitter SocialLogin button or using store account credentials you will receive to your email address.')
            );

            return;
        }

        if($customersByTwitterId->count()) {
            // Existing connected user - login
            $customer = $customersByTwitterId->getFirstItem();

            Mage::helper('sociallogin/twitter')->loginByCustomer($customer);

            Mage::getSingleton('core/session')
                ->addSuccess(
                    $this->__('You have successfully logged in using your Twitter account.')
                );

            return;
        }

        $customersByEmail = Mage::helper('sociallogin/twitter')
            ->getCustomersByEmail($userInfo->email);

        if($customersByEmail->count()) {
            // Email account already exists - attach, login
            $customer = $customersByEmail->getFirstItem();

            Mage::helper('sociallogin/twitter')->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('We have discovered you already have an account at our store. Your Twitter account is now connected to your store account.')
            );

            return;
        }

        // New connection - create, attach, login
        if(empty($userInfo->name)) {
            throw new Exception(
                $this->__('Sorry, could not retrieve your Twitter last name. Please try again.')
            );
        }

        Mage::helper('sociallogin/twitter')->connectByCreatingAccount(
            $userInfo->email,
            $userInfo->name,
            $userInfo->id,
            $token
        );


        Mage::getSingleton('core/session')->addSuccess(
            $this->__('Your Twitter account is now connected to your new user accout at our store. You can login next time by the Google SocialLogin button.')
        );        
        Mage::getSingleton('core/session')->addNotice(

            sprintf($this->__('Since Twitter doesn\'t support third-party access to your email address, we were unable to send you your store accout credentials. To be able to login using store account credentials you will need to update your email address and password using our <a href="%s">Edit Account Information</a>.'), Mage::getUrl('customer/account/edit'))
        );        
    }

}
