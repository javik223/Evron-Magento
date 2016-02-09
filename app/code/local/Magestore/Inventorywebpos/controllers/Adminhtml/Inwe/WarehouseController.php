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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorywebpos_Adminhtml_Inwe_WarehouseController extends Mage_Adminhtml_Controller_Action {
    
    public function webpospermissionsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('warehouse.edit.tab.webpos.permission')
                ->setSelectedUser($this->getRequest()->getPost('selected_user', null));
        $this->renderLayout();
    }

    public function webpospermissionsgridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('warehouse.edit.tab.webpos.permission')
                ->setSelectedUser($this->getRequest()->getPost('selected_user', null));
        $this->renderLayout();
    }

}
