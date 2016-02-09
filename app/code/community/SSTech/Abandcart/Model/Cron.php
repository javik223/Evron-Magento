<?php
/**
 * Run cron for abandoned cart
 * 
 * Stech Abandcart Cron Model
 *
 * @category   SSTech
 * @package    SSTech_Abandcart
 * @author     SSTech
 * */
class SSTech_Abandcart_Model_Cron
{
	/**
	 *  Send email report to users
	 */
    public function emailreport()
    {
        $reporter = Mage::getModel('sstech_abandcart/report');
        // If disabled don't send the email
        if ($reporter->isEnabled()) Mage::log($reporter->sendReport());;
    }
}