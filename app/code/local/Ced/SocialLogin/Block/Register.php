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
 * SocialLogin 	register block
 *
 * @category   	Ced
 * @package    	Ced_SocialLogin
 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */

class Ced_SocialLogin_Block_Register extends Mage_Core_Block_Template
{
    protected $clientGoogle = null;
    protected $clientFacebook = null;
    protected $clientTwitter = null;		protected $clientLinkedin = null;
    
    protected $numEnabled = 0;
    protected $numShown = 0;

    protected function _construct() {
        parent::_construct();

        $this->clientGoogle = Mage::getSingleton('sociallogin/google_client');
        $this->clientFacebook = Mage::getSingleton('sociallogin/facebook_client');
        $this->clientTwitter = Mage::getSingleton('sociallogin/twitter_client');		$this->clientLinkedin = Mage::getSingleton('sociallogin/linkedin_client');

        if( !$this->_googleEnabled() &&
            !$this->_facebookEnabled() &&
            !$this->_twitterEnabled() &&            !$this->_linkedinEnabled())
            return;

        if($this->_googleEnabled()) {
            $this->numEnabled++;
        }

        if($this->_facebookEnabled()) {
            $this->numEnabled++;
        }

        if($this->_twitterEnabled()) {
            $this->numEnabled++;
        }		if($this->_linkedinEnabled()) {            $this->numEnabled++;        }

        Mage::register('ced_sociallogin_button_text', $this->__('Register'));

        $this->setTemplate('ced/sociallogin/register.phtml');
    }

    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }

    protected function _getCol()
    {
        return 'col-'.++$this->numShown;
    }

    protected function _googleEnabled()
    {
        return (bool) $this->clientGoogle->isEnabled();
    }

    protected function _facebookEnabled()
    {
        return (bool) $this->clientFacebook->isEnabled();
    }

    protected function _twitterEnabled()
    {
        return (bool) $this->clientTwitter->isEnabled();
    }		protected function _linkedinEnabled()    {        return (bool) $this->clientLinkedin->isEnabled();    }

}