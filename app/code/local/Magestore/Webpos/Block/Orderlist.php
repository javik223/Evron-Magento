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
 * @package     Magestore_Marketingautomation
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Marketingautomation Adminhtml Block
 *
 * @category Magestore
 * @package Magestore_Webpos
 * @author Magestore Developer
 */
class Magestore_Webpos_Block_Orderlist extends Mage_Core_Block_Template {
    public function __construct() {
        parent::__construct();
        $this->setTemplate('webpos/webpos/orderlist/neworderlist.phtml');
    }
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }
    /*jack*/
    public function getOrder() {
        $limit = 15;
        $collection = Mage::getBlockSingleton('webpos/admin_orderlist_orderlist')->getOrderGridCollections();
        $page = Mage::app()->getRequest()->getParam('page');
        /* paginator */
        $end_page = (int)(($collection->getSize())/$limit) ;
        if (($collection->getSize())%$limit)
                $end_page = $end_page + 1;
        $collection->setPageSize($limit)->setCurPage($page);
        return $collection;
        /**/
    }
    /**/
}