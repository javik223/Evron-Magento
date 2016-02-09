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

 * SocialLogin 	Twitter/Account block

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */



class Ced_SocialLogin_Block_Twitter_Account extends Mage_Core_Block_Template

{

    protected $client = null;

    protected $userInfo = null;



    protected function _construct() {

        parent::_construct();



        $this->client = Mage::getSingleton('sociallogin/twitter_client');

        if(!($this->client->isEnabled())) {

            return;

        }



        $this->userInfo = Mage::registry('ced_sociallogin_twitter_userdetails');

        $this->setTemplate('ced/sociallogin/twitter/account.phtml');



    }



    protected function _hasUserInfo()

    {

        return (bool) $this->userInfo;

    }



    protected function _getTwitterId()

    {

        return $this->userInfo->id;

    }



    protected function _getStatus()

    {

        return '<a href="'.sprintf('https://twitter.com/%s', $this->userInfo->screen_name).'" target="_blank">'.

                    $this->htmlEscape($this->userInfo->screen_name).'</a>';

    }



    protected function _getPicture()

    {

        if(!empty($this->userInfo->profile_image_url)) {

            return Mage::helper('sociallogin/twitter')

                    ->getProperDimensionsPictureUrl($this->userInfo->id,

                            $this->userInfo->profile_image_url);

        }



        return null;

    }



    protected function _getName()

    {

        return $this->userInfo->name;

    }



}

