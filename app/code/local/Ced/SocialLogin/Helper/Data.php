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
 * SocialLogin data Helper
 *
 * @category   	Ced
 * @package    	Ced_SocialLogin
 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */
class Ced_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
     * redirect to 404 page
     */
public function redirect404($frontController)
    {
        $frontController->getResponse()
            ->setHeader('HTTP/1.1','404 Not Found');
        $frontController->getResponse()
            ->setHeader('Status','404 File not found');

        $pageId = Mage::getStoreConfig('web/default/cms_no_route');
        if (!Mage::helper('cms/page')->renderPage($frontController, $pageId)) {
				$frontController->_forward('defaultNoRoute');
			}
    }
	
}
	 