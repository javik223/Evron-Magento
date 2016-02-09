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
 * Inventory Supplier Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */

class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Edit_Tab_Renderer_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $columnName = $this->getColumn()->getName();
        $columnName = explode('_', $columnName);
        if ($columnName[1]) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $installer = Mage::getModel('core/resource');
            $warehouseId = $columnName[1];
            $purchase_order_id = $this->getRequest()->getParam('id');
            if ($row->getProductId()) {
                $producId = $row->getProductId();
            } else {
                $producId = $row->getEntityId();
            }

            $sql = 'SELECT `qty_order` from ' . $installer->getTableName("erp_inventory_purchase_order_product_warehouse") . ' WHERE (purchase_order_id = ' . $purchase_order_id . ') AND (product_id = ' . $producId . ') AND (warehouse_id = ' . $warehouseId . ')';
            $results = $readConnection->fetchAll($sql);
            if (count($results) > 0) {
                foreach ($results as $result) {
                    if (!array_key_exists('qty_order', $result) || (array_key_exists('qty_order', $result) && !$result['qty_order'])) {
                        $result['qty_order'] = 0;
                    }
                    if ($this->getColumn()->getEditable()) {
                        echo $result['qty_order'] . '<input name="warehouse_' . $warehouseId . '" class="input-text" type="text" value="' . $result['qty_order'] . '"/>';
                    } else {
                        echo $result['qty_order'];
                    }
                }
            } else {
                $result['qty_order'] = 0;
                if ($this->getColumn()->getEditable()) {
                    echo $result['qty_order'] . '<input name="warehouse_' . $warehouseId . '" class="input-text" type="text" value="' . $result['qty_order'] . '"/>';
                } else {
                    echo $result['qty_order'];
                }
            }
        } else {
            parent::render($row);
        }
    }

}
