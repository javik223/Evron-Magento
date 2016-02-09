<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Helper_Email extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SENDSTOCK_EMAIL = 'inventoryplus/transaction/sendstock_email';
    const XML_PATH_WAREHOUSE_EMAIL = 'inventoryplus/transaction/warehouse_email';
    

    public function sendSendstockEmail($warehouse,$stockId,$isSendstock,$stockName) {
        $user = Mage::getModel('admin/session')->getUser();
        $storeId = Mage::app()->getStore()->getId();
        $template = Mage::getStoreConfig(self::XML_PATH_SENDSTOCK_EMAIL, $storeId);             
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $from_name = $user->getUsername();
        $from_email = $user->getEmail();
        $sender = array('email' => $from_email, 'name' => $from_name);
        $mailTemplate
            ->setTemplateSubject('Stock Notification')
            ->sendTransactional(
                $template, $sender, $warehouse->getManagerEmail(), $warehouse->getManagerName(), array(
                'requeststockid' => $stockId,
				'issendstock'	=> $isSendstock,
				'stockName' => $stockName
                ),$storeId
        );
        $translate->setTranslateInline(true);        
    }
   
}