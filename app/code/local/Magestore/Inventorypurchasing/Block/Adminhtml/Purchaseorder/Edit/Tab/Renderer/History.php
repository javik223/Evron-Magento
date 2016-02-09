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

class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Edit_Tab_Renderer_History
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $purchaseOrderHistoryId = $row->getPurchaseOrderHistoryId();
//        $url = $this->getUrl('adminhtml/inpu_purchaseorders/showhistory');
        return '<p style="text-align:center"><a name="url" href="javascript:void(0)" onclick="showhistory('.$purchaseOrderHistoryId.')">'.Mage::helper('inventorypurchasing')->__('View').'</a></p>
                ';   
//                <script type="text/javascript">
//                    function showhistory(purchaseOrderHistoryId){
//                        var purchaseOrderHistoryId = purchaseOrderHistoryId;
//                        var url = "'.$url.'purchaseOrderHistoryId/"+purchaseOrderHistoryId;
//                        TINY.box.show(url,1, 800, 400, 1);
//                    }
//                </script>
    }
}