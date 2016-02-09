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
class Magestore_Inventoryreports_Block_Adminhtml_Header_Reportselect extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_Inventoryreports_Block_Inventoryreports
     */
    public function _prepareLayout() {
        $this->setTemplate('inventoryreports/header/report_select.phtml');
        return parent::_prepareLayout();
    }

    /**
     * Get reports by type
     * 
     * @return array
     */
    public function getReports() {
        $type = $this->getRequest()->getParam('type_id');
        $reportGroup = $this->helper('inventoryreports')->getReports($type);
        return $reportGroup['reports'];
    }

    /**
     * Get report code
     * 
     * @return string
     */
    public function getReportCode() {
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        return $requestData['report_radio_select'];
    }

}
