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

 * SocialLogin installer 0.0.1

 *

 * @category   Ced

 * @package    Ced_SocialLogin

 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */


$installer = $this;

$installer->startSetup();



$installer->setSocialCustomerAttributes(

    array(

        'ced_sociallogin_gid' => array(
            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_gid",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""           

        ),            

        'ced_sociallogin_gtoken' => array(

            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_gtoken",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""          

        ),

        'ced_sociallogin_fid' => array(

            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_fid",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""          

        ),            

        'ced_sociallogin_ftoken' => array(

            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_ftoken",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""          

        ), 

		'ced_sociallogin_tid' => array(

         
            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_tid",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""      

        ),            

        'ced_sociallogin_ttoken' => array(

           
            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_ttoken",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""               

        ),     
        'ced_sociallogin_lid' => array(

           
            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_lid",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""               

        ),   
        'ced_sociallogin_ltoken' => array(

           
            "type"     => "text",
            "backend"  => "",
            "label"    => "ced_sociallogin_ltoken",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""               

        ),        

    )

);



// Install our custom attributes

$installer->installSocialCustomerAttributes();

//$installer->removeSocialCustomerAttributes();

$installer->endSetup();