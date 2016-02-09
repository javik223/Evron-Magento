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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Block_Items extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Items
{
   public function getColumnHtml(Varien_Object $item, $column, $field = null)
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($item->getOrderItem()) {
            $block = $this->getColumnRenderer($column, $item->getOrderItem()->getProductType());
        } else {
            $block = $this->getColumnRenderer($column, $item->getProductType());
        }

        if ($block) {
            $block->setItem($item);
            if (!is_null($field)) {
                $block->setField($field);
            }
            if($column == 'qty'){
                $html = $block->toHtml();
                $allDropships = Mage::getModel('inventorydropship/inventorydropship')
                                                    ->getCollection()
                                                    ->addFieldToFilter('order_id',$orderId)
                                                    ->addFieldToFilter('status',array('in'=>array('3','4','6')));
                $allDropshipIds = array();
                foreach($allDropships as $dropship)
                    $allDropshipIds[] = $dropship->getId();
                $dropshipProductNumber = 0;
                $dropshipProducts = Mage::getModel('inventorydropship/inventorydropship_product') 
                                                    ->getCollection()
                                                    ->addFieldToFilter('dropship_id',array('in'=>$allDropshipIds))
                                                    ->addFieldToFilter('item_id',$item->getOrderItemId());
                foreach($dropshipProducts as $dropshipProduct)
                    $dropshipProductNumber += $dropshipProduct->getQtyApprove();
                if($dropshipProductNumber > 0){
                    $addMoreHtml = '<table cellspacing="0" class="qty-table"><tbody><tr><td>';
                    $addMoreHtml .= Mage::helper('inventorydropship')->__('Dropship');
                    $addMoreHtml .= '</td><td><strong>'.$dropshipProductNumber.'</strong></td></tr></tbody></table>';
                    $html .= $addMoreHtml;
                }
                return $html;
            }else{
                return $block->toHtml();
            }
        }
        return '&nbsp;';
    }
}