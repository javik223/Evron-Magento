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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Helper_Warehouseshipment extends Magestore_Inventoryplus_Helper_Shipment
{
	protected $_observerOb;
	protected $_postParams;
	protected $_shipmentOb;
	protected $_orderOb;
	
	public function sendObject($observer,$postParams,$shipment,$order){
		$this->_observerOb	= $observer;
		$this->_postParams	= $postParams;
		$this->_shipmentOb	= $shipment;
		$this->_orderOb	= $order;
		return;
	}
	/*
	 * check product status is waiting for transfer
	 * 
	 * @return boolean
	 */
	public function checkOrderItemWaittingFortransfer($orderItemId,$productId,$orderId,$warehouseId){
		$shipmentTransferModel = Mage::getModel('inventoryplus/warehouse_shipment')
										->getCollection()
										->addFieldToFilter('item_id',$orderItemId)
										->addFieldToFilter('product_id',$productId)
										->addFieldToFilter('order_id',$orderId)
										->addFieldToFilter('warehouse_id',$warehouseId);
		$data = $shipmentTransferModel->getFirstItem()->getData();
		if($data){                
			return true;
		}
		else{
			return false;
		}
	}
	
	public function getWarehouseNameByShipmentIdAndOrderitemId($shipmentId,$orderItemId){
		$inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
		$warehouse = $inventoryShipmentModel->getCollection()
											->addFieldToFilter('shipment_id',$shipmentId)
											->addFieldToFilter('item_id',$orderItemId)
											->getFirstItem();
		if($warehouseName = $warehouse->getWarehouseName())
			return $warehouseName;
	}
	/*
	 *	get warehouse ID to ship this order item
	 *	@param	var orderItem,var data, var qty_shipped
	 *	@return	warehouse_id
	 */
	public function _getWarehouseIdToShip($orderItem,$data,$qtyShipped){
		if (isset($data['warehouse-shipment']['items'][$orderItem->getItemId()]) && $data['warehouse-shipment']['items'][$orderItem->getItemId()]) {
			$warehouse_id = $data['warehouse-shipment']['items'][$orderItem->getItemId()];
		} elseif ($orderItem->getParentItemId() && isset($data['warehouse-shipment']['items'][$orderItem->getParentItemId()])) {
			$warehouse_id = $data['warehouse-shipment']['items'][$orderItem->getParentItemId()];
		} else {
			/* integrate with WebPOS 2.0 or later	*/
			if (Mage::helper('core')->isModuleEnabled('Magestore_Webpos') && Mage::helper('core')->isModuleEnabled('Magestore_Inventorywebpos')) {
				if (Mage::helper('inventoryplus')->isWebPOS20Active()) {
					$warehouse_id = Mage::helper('inventorywebpos')->_getCurrentWarehouseId();
				}
			}
			if (!isset($warehouse_id) || !$warehouse_id) {
				$product_id = $orderItem->getProductId();
				$warehouse_id = Mage::helper('inventoryplus')->selectWarehouseToShip($product_id, $qtyShipped);
			}
		}
		return $warehouse_id;
	}
	/*
	 *	prepare and save warehouse_order model
	 *	@param	var warehouse_id,var product_id, var qtyShipped, model warehouseProduct
	 *	@return	object
	 */
	public function _saveModelWarehouseOrder($warehouse_id,$product_id,$qtyShipped,$warehouseProduct){
		$warehouseOr = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                            ->addFieldToFilter('order_id', $this->_orderOb->getId())
                            ->addFieldToFilter('product_id', $product_id)
                            ->getFirstItem();
		if(!$warehouseOr->getId())return;
		if ($warehouseOr->getWarehouseId() != $warehouse_id) {
			/* Ship from other warehouse (not in warehouse_order) */
			$newQtyAvailable = $warehouseProduct->getAvailableQty() - $qtyShipped;
			$warehouseProduct->setAvailableQty($newQtyAvailable);
			$warehouseProduct->save();
			/* Tru from warehouse dc select thi phai tra lai cho warehouse da order */
			$warehouseOrderedProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
					->addFieldToFilter('warehouse_id', $warehouseOr->getWarehouseId())
					->addFieldToFilter('product_id', $product_id)
					->getFirstItem();
			$newQtyAvailWarehouseOrdered = $warehouseOrderedProduct->getAvailableQty() + $qtyShipped;
			$warehouseOrderedProduct->setAvailableQty($newQtyAvailWarehouseOrdered)
					->save();
		}
		$OnHoldQty = $warehouseOr->getQty() - $qtyShipped;
		if ($OnHoldQty >= 0) {
			$warehouseOr->setQty($OnHoldQty)
					->save();
		} else {
			$warehouseOr->setQty(0)
					->save();
		}
		return $warehouseOr;
	}
/*
	 *	prepare and save warehouse_transaction_product model
	 *	@param	var transactionId,var product_id, var transactionProduct
	 *	@return	null
	 */
	public function _saveModelTransactionProduct($transactionId,$productId,$transactionProduct){
		try{
			Mage::getModel('inventorywarehouse/transaction_product')
			->setWarehouseTransactionId($transactionId)
			->setProductId($productId)
			->setProductSku($transactionProduct['product_sku'])
			->setProductName($transactionProduct['product_name'])
			->setQty(-$transactionProduct['qty_shipped'])
			->save();
		}catch(Exception $e){
			Mage::log($e->getMessage(), null, 'inventory_management.log');
		}
		return;
	}
	/*
	 *	prepare and save warehouse_transaction model
	 *	@param	var transactionSendData
	 *	@return	object
	 */
	public function _saveModelTransaction($transactionSendData){
		$transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
		$transactionSendModel->addData($transactionSendData);
		try {
            $transactionSendModel->save();
		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, 'inventory_management.log');
		}
		return $transactionSendModel;
	}	
}