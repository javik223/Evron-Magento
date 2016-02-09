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
 * Inventorydashboard Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydashboard
 * @author      Magestore Developer
 */
class Magestore_Inventorydashboard_Block_Adminhtml_Inventorydashboard extends Mage_Adminhtml_Block_Template {

    public function getReportUrl($reportCode) {
        if(!in_array($this->helper('inventoryplus')->getEdition(), array('pro','enterprise'))){
            return null;
        }
        $reportUrl = '';
        $reportType = $this->helper('inventorydashboard')->getReportTypeByCode($reportCode);
        if ($reportType) {
            $top_filter = '';
            $defaultTime = 'last_30_days';
            $top_filter .= "select_time=$defaultTime&report_radio_select=$reportCode";
            $top_filter .= '&order_status=' . Mage_Sales_Model_Order::STATE_COMPLETE;
            $top_filter = base64_encode($top_filter);
            $reportUrl = $this->getUrl('adminhtml/inr_report/' . $reportType, array("top_filter" => $top_filter,
                '_secure' => Mage::app()->getStore()->isCurrentlySecure()));
        }
        return $reportUrl;
    }

}
