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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Renderer_Ordertimerange extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render time column
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        if ($row->getTimeRange() == $this->__('Totals'))
            return $row->getTimeRange();
        $html = '';
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        switch ($requestData['report_radio_select']) {
            case 'hours_of_day':
                $html = $row->getTimeRange() . 'h';
                break;
            case 'days_of_week':
                $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                $html .= $daysofweek[$row->getTimeRange()];
                break;
            case 'days_of_month':
                $html .= 'Day ' . $row->getTimeRange();
                break;
            case 'sales_days':
                $html .= Mage::getModel('core/date')->date('d/m/Y', strtotime($row->getTimeRange()));
                break;
        }
        return $html;
    }

}
