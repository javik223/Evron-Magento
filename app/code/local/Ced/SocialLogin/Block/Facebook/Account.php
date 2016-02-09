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

 * SocialLogin 	Facebook/Account block

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */





class Ced_SocialLogin_Block_Facebook_Account extends Mage_Core_Block_Template

{

    protected $client = null;

    protected $userDetails = null;



    protected function _construct() {

        parent::_construct();



        $this->client = Mage::getSingleton('sociallogin/facebook_client');

        if(!($this->client->isEnabled())) {

            return;

        }



        $this->userInfo = Mage::registry('ced_sociallogin_facebook_userdetails');


        $this->setTemplate('ced/sociallogin/facebook/account.phtml');

    }



    protected function _hasUserInfo()

    {

        return (bool) $this->userInfo;

    }



    protected function _getFacebookId()

    {

        return $this->userInfo->id;

    }



    protected function _getStatus()

    {

        if(!empty($this->userInfo->link)) {

            $link = '<a href="'.$this->userInfo->link.'" target="_blank">'.

                    $this->htmlEscape($this->userInfo->name).'</a>';

        } else {

            $link = $this->userInfo->name;

        }



        return $link;

    }



    protected function _getEmail()

    {

        return $this->userInfo->email;

    }



    protected function _getPicture()

    {

        if(!empty($this->userInfo->picture)) {

            return Mage::helper('sociallogin/facebook')

                    ->getProperDimensionsPictureUrl($this->userInfo->id,

                            $this->userInfo->picture->data->url);

        }



        return null;

    }



    protected function _getName()

    {

        return $this->userInfo->name;

    }



    protected function _getGender()

    {

        if(!empty($this->userInfo->gender)) {

            return ucfirst($this->userInfo->gender);

        }



        return null;

    }



    protected function _getBirthday()

    {

        if(!empty($this->userInfo->birthday)) {

            $birthday = date('F j, Y', strtotime($this->userInfo->birthday));

            return $birthday;

        }



        return null;

    }



}