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
 * Inventoryreports Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Model_Observer {

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Inventoryreports_Model_Observer
     */
    public function controllerActionPredispatch($observer) {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }

    // add menu
    public function addMenu($observer) {
        $menu = $observer->getEvent()->getMenus()->getMenu();
        $report = Mage::getModel('inventoryreports/reporttype');
        $attributes = $report->getOrderAttributeOptions();
        $reportCollection = $report->getCollection();
        $reportCollection->setOrder('code','asc');
        $menuLevel1 = array();
        $menuLevel2 = array();
        $menuLevel3 = array();
        $i = 50;
        foreach ($reportCollection as $reportData) {
            $i += 10;
            $top_filter = '';
            $reportData = $reportData->getData();
            $type = $reportData['type'];
            if(!array_key_exists($type, $menuLevel2)){
                $menuLevel2[$type] = array();
            }
            $code = $reportData['code'];
            $title = $reportData['title'];
            $defaultTime = $reportData['default_time_range'];
            $top_filter .= "select_time=$defaultTime&report_radio_select=$code&";
            if ($code == 'total_stock_different_when_physical_stocktaking_by_warehouse' || $code == 'warehousing_time_longest') {
                $top_filter .= "warehouse_select=1&supplier_select=0";
            } else {
                $top_filter .= "warehouse_select=0&supplier_select=0";
            }
            if ($code == 'order_attribute') {

                foreach ($attributes as $key => $attribute) {
                    $top_filter_attribute = $top_filter . "&attribute_select=$key";
                    $top_filter_attribute = base64_encode($top_filter_attribute);
                    $menuLevel3[$key] = array(
                        'label' => Mage::helper('inventoryreports')->__(ucwords($attribute)),
                        'sort_order' => $i,
                        'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inr_report/$type/", array("top_filter" => $top_filter_attribute, "_secure" => Mage::app()->getStore()->isCurrentlySecure(), 'type_id' => $type)),
                        'active' => false,
                        'level' => 3,
                    );
                }
                $top_filter = base64_encode($top_filter);
                if (!array_key_exists($code, $menuLevel2[$type])) {
                    $menuLevel2[$type][$code] = array(
                        'label' => Mage::helper('inventoryreports')->__(ucwords($title)),
                        'sort_order' => $i,
                        'active' => false,
                        'children' => $menuLevel3
                    );
                }
            } else {
                $top_filter = base64_encode($top_filter);
                if (!array_key_exists($code, $menuLevel2[$type])) {
                    $menuLevel2[$type][$code] = array(
                        'label' => Mage::helper('inventoryreports')->__(ucwords($title)),
                        'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inr_report/$type/", array("top_filter" => $top_filter, "_secure" => Mage::app()->getStore()->isCurrentlySecure(), 'type_id' => $type)),
                        'sort_order' => $i,
                        'active' => false,
                    );
                } 
            }
            if (!array_key_exists($type, $menuLevel1)) {
                if($type == 'sales'){
                    $filter_sales = '';
                    $filter_sales .= "select_time=last_30_days&report_radio_select=days_of_month";
                    $filter_sales = base64_encode($filter_sales);
                    $menuLevel1[$type] = array(
                        'label' => Mage::helper('inventoryreports')->__(ucwords($type)),
                        'sort_order' => $i,
                        'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inr_report/$type/", array("top_filter" => $filter_sales, "_secure" => Mage::app()->getStore()->isCurrentlySecure(), 'type_id' => $type)),
                        'active' => false,
                        'children' => $menuLevel3
                    );
                } else {
                    $menuLevel1[$type] = array(
                        'label' => Mage::helper('inventoryreports')->__(ucwords($type)),
                        'sort_order' => $i,
                        'active' => false,
                        'children' => $menuLevel2[$type]
                    );
                }
            } else {
                $menuLevel1[$type]['children'] = $menuLevel2[$type];
            }
        }
        $menu['reports'] = array('label' => Mage::helper('inventoryreports')->__('Reports'),
            'sort_order' => 1000,
            'url' => Mage::helper("adminhtml")->getUrl("adminhtml/inr_report/dashboard", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
            'active' => (in_array(Mage::app()->getRequest()->getRouteName(), array('inventoryreportsadmin'))) ? true : false,
            'level' => 0,
            'children' => $menuLevel1
        );
        $observer->getEvent()->getMenus()->setData('menu', $menu); 
    }

}
