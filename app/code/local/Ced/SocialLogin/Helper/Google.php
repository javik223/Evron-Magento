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
 * SocialLogin Google Helper
 *
 * @category    Ced
 * @package     Ced_SocialLogin
 * @author      CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */

class Ced_SocialLogin_Helper_Google extends Mage_Core_Helper_Abstract
{

    public function disconnect(Mage_Customer_Model_Customer $customer) {
        $client = Mage::getSingleton('sociallogin/google_client');
        
        try {
            $client->setAccessToken($customer->getCedSocialloginGtoken());
            $client->revokeToken();   
        } catch (Exception $e) { }
        
        $pictureFilename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'ced'
                .DS
                .'sociallogin'
                .DS
                .'google'
                .DS                
                .$customer->getCedSocialloginGid();
        
        if(file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }
        
        $customer->setCedSocialloginGid(null)
        ->setCedSocialloginGtoken(null)
        ->save();   
    }
    
    public function connectByGoogleId(
            Mage_Customer_Model_Customer $customer,
            $googleId,
            $token)
    {
        $customer->setCedSocialloginGid($googleId)
                ->setCedSocialloginGtoken($token)
                ->save();
        
        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }
    
    public function connectByCreatingAccount(
            $email,
            $firstName,
            $lastName,
            $googleId,
            $token)
    {
        $customer = Mage::getModel('customer/customer');
        
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId())
                ->setEmail($email)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setCedSocialloginGid($googleId)
                ->setCedSocialloginGtoken($token)
                ->setPassword($customer->generatePassword(10))
                ->save();

        $customer->setConfirmation(null);
        $customer->save();
   
        $customer->sendNewAccountEmail('confirmed', '', Mage::app()->getStore()->getId());

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }
    
    public function loginByCustomer(Mage_Customer_Model_Customer $customer)
    {
        if($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);        
    }
    
    public function getCustomersByGoogleId($googleId)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
            ->addAttributeToFilter('ced_sociallogin_gid', $googleId)
            ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $collection->addFieldToFilter(
                    'entity_id',
                    array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
                );
            }
        }

        return $collection;
    }
    
    public function getCustomersByEmail($email)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
                ->addFieldToFilter('email', $email)
                ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }  
        
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
            );
        }        
        
        return $collection;
    }    
    
    public function getProperDimensionsPictureUrl($googleId, $pictureUrl)
    {
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .'ced'
                .'/'
                .'SocialLogin'
                .'/'
                .'google'
                .'/'                
                .$googleId;

        $filename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'ced'
                .DS
                .'sociallogin'
                .DS
                .'google'
                .DS                
                .$googleId;

        $directory = dirname($filename);

        if (!file_exists($directory) || !is_dir($directory)) {
            if (!@mkdir($directory, 0777, true))
                return null;
        }

        if(!file_exists($filename) || 
                (file_exists($filename) && (time() - filemtime($filename) >= 3600))){
            $client = new Zend_Http_Client($pictureUrl);
            $client->setStream();
            $response = $client->request('GET');
            stream_copy_to_stream($response->getStream(), fopen($filename, 'w'));

            $imageObj = new Varien_Image($filename);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(false);
            $imageObj->resize(150, 150);
            $imageObj->save($filename);
        }
        
        return $url;
    }
    
}
