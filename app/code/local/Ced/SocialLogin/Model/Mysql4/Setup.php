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
 * SocialLogin resource setup model
 *
 * @category   Ced
 * @package    Ced_SocialLogin
 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */

class Ced_SocialLogin_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    protected $_customerAttributes = array();

    public function setSocialCustomerAttributes($customerAttributes)
    {
        $this->_customerAttributes = $customerAttributes;

        return $this;
    }
    
   /**
     * Add our custom customer attributes
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function installSocialCustomerAttributes()
    {        
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->addAttribute('customer', $code, $attr);
        }

        return $this;
    }

    /**
     * Remove custom customer attributes
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function removeSocialCustomerAttributes()
    {
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->removeAttribute('customer', $code);
        }

        return $this;
    }  
}
