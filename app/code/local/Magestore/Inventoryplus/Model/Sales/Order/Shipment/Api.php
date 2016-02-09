<?php
class Magestore_Inventoryplus_Model_Sales_Order_Shipment_Api extends Mage_Sales_Model_Order_Shipment_Api
{
    public function __construct()
    {
        $this->_attributesMap['shipment'] = array('shipment_id' => 'entity_id');

        $this->_attributesMap['shipment_item'] = array('item_id'    => 'entity_id');

        $this->_attributesMap['shipment_comment'] = array('comment_id' => 'entity_id');

        $this->_attributesMap['shipment_track'] = array('track_id'   => 'entity_id');
    }

    /**
     * Create new shipment for order
     *
     * @param string $orderIncrementId
     * @param array $itemsQty
     * @param string $comment
     * @param booleam $email
     * @param boolean $includeComment
     * @return string
     */
    public function create($orderIncrementId, $itemsQty = array(), $comment = null, $email = false,
        $includeComment = false
    ) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        /**
          * Check order existing
          */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }

        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
             $this->_fault('data_invalid', Mage::helper('sales')->__('Cannot do shipment for order.'));
        }
		/* Added by Magnus */
		if(empty($itemsQty)){
			$orderItems = Mage::getModel('sales/order_item')->getCollection()
				->addFieldToFilter('order_id',$order->getId());
			$_data = array();	
			foreach($orderItems as $_orderItem){
				$_prepareShip = $_orderItem->getQtyOrdered() - $_orderItem->getCanceled() - $_orderItem->getRefunded() - $_orderItem->getShipped();
				$_prepareShip = max($_prepareShip, 0);
				$_data[$_orderItem->getItemId()] = $_prepareShip;
			}
			$itemsQty = $_data;
		}
		/* Endl Added by Magnus */
         /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $order->prepareShipment($itemsQty);
        if ($shipment) {
            $shipment->register();
            $shipment->addComment($comment, $email && $includeComment);
            if ($email) {
                $shipment->setEmailSent(true);
            }
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                $shipment->sendEmail($email, ($includeComment ? $comment : ''));
            } catch (Mage_Core_Exception $e) {
                $this->_fault('data_invalid', $e->getMessage());
            }
            return $shipment->getIncrementId();
        }
        return null;
    }

} // Class Magestore_Inventoryplus_Model_Sales_Order_Shipment_Api End
