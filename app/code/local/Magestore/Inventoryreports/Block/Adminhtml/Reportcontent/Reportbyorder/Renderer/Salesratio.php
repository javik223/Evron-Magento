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
 * @package     Magestore_Inventoryplus
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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Renderer_Salesratio
    extends Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Renderer_Salesratio {
     
    protected $_chartBlock = 'inventoryreports/adminhtml_reportcontent_reportbyorder_chart';
     
    /**
     * Render row
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        if($row->getData('sales_ratio')){
            return $row->getData('sales_ratio').' %';
        }
        $ratio = 0;
        if($this->getTotalData()) {
            $ratio = round($row->getData('sum_base_grand_total') / $this->getTotalData() * 100, 2);
        }
        return $ratio.' %';
    }
}