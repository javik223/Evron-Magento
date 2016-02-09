<?php
/**
 * Magento Community Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Clarion
 * @package     Clarion_OnestepCheckout
 * @created     28th May 3.25pm
 * @author      Clarion magento team<magento@clariontechnologies.co.in>
 * @purpose     AgreementOptions field values model  
 * @copyright   Copyright (c) 2014 Clarion Technologies Pvt.Ltd
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License
 */
class Clarion_OnestepCheckout_Adminhtml_Model_System_Config_Source_AgreementOptions
{
    public function toOptionArray()
    {
    	$help_obj	= Mage::helper('onestepcheckout');
    	$options	= array(
            array('value' => 'standard','label' => $help_obj->__('Standard')),
            array('value' => 'minimal',	'label' => $help_obj->__('Minimal'))
        );
        return $options; 
    }
}
