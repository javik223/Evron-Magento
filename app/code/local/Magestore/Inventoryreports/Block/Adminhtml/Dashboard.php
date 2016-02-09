<?php

class Magestore_Inventoryreports_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template {

    /**
     * Get reports by type
     * 
     * @param string $type
     * @return array
     */
    public function getReports($type = null) {
        return $this->helper('inventoryreports')->getReports($type);
    }

    /**
     * Get report url
     * 
     * @param array $report
     * @return string
     */
    public function getReportUrl($type, $report) {
        $top_filter = '';
        $code = $report['code'];
        $defaultTime = $report['default_time_range'];
        $top_filter .= "select_time=$defaultTime&report_radio_select=$code";
        if(isset($report['order_status'])){
            $top_filter .= '&order_status='.$report['order_status'];
        }
        if ($code == 'order_attribute') {
            $top_filter .= '&attribute_select=shipping_method';
        }
        $top_filter = base64_encode($top_filter);
        $reportUrl = $this->getUrl('adminhtml/inr_report/'.$type, array("top_filter" => $top_filter,
            '_secure' => Mage::app()->getStore()->isCurrentlySecure()));
        return $reportUrl;
    }

}
