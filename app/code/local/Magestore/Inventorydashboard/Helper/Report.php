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
 * @package     Magestore_Inventorydashboard
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydashboard Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydashboard
 * @author      Magestore Developer
 */
class Magestore_Inventorydashboard_Helper_Report extends Mage_Core_Helper_Abstract {

    /**
     * Get days of week
     * 
     */
    public function getDaysOfWeek() {
        return array(
            '1' => $this->__('Sunday'),
            '2' => $this->__('Monday'),
            '3' => $this->__('Tuesday'),
            '4' => $this->__('Wednesday'),
            '5' => $this->__('Thusday'),
            '6' => $this->__('Friday'),
            '7' => $this->__('Saturday'),
        );
    }
    
    public function checkNullDataChart($series) {
        $seriesCheckNull = explode(',', $series);
        $seriesCheckNull = array_filter($seriesCheckNull);
        $newSeries = implode(',', $seriesCheckNull);
        return $newSeries;
    }    
    
    public function getMovementTypeText($type) {
        switch ($type) {
            case '1' : return $this->__('Send stock to another Warehouse or other destination');
                break;
            case '2' : return $this->__('Receive stock from another Warehouse or other source');
                break;
            case '3' : return $this->__('Receive stock from Purchase Order Delivery');
                break;
            case '4' : return $this->__('Send stock to Supplier for Return Order');
                break;
            case '5' : return $this->__('Send stock to Customer for Shipment');
                break;
            case '6' : return $this->__('Receive stock from Customer Refund');
                break;
        }
    }    
}
