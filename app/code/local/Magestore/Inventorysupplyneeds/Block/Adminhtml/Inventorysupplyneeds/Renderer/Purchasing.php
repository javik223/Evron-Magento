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
 * Inventorysupplyneeds Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Renderer_Purchasing extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
		$productId = $row->getProductId();
		$filter = $this->getRequest()->getParam('top_filter');
		$helperClass = Mage::helper('inventorysupplyneeds');
		$helperClass->setTopFilter($filter);
		$warehouseSelected = $helperClass->getWarehouseSelected();
		$warehouseSelectedStr = implode(',',$warehouseSelected);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $sql = 'SELECT IFNULL(SUM(`qty_order` - `qty_received` + `qty_returned`),0) as in_po from ' . $resource->getTableName("inventorypurchasing/purchaseorder_productwarehouse") . ' WHERE (warehouse_id IN ('.$warehouseSelectedStr.')) AND product_id = '.$productId;
        $results = $readConnection->fetchAll($sql);
        return $results[0]['in_po'];
    }

}

?>
