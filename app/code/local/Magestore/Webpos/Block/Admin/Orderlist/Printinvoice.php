<?php

class Magestore_Webpos_Block_Admin_Orderlist_Printinvoice extends Mage_Sales_Block_Order_Print_Invoice {

    public function getOrder() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        return $order;
    }

    public function getInvoice() {
        $orderId = $this->getRequest()->getParam('order_id');
        $invoice = Mage::getModel('sales/order_invoice')->load($orderId, 'order_Id');
        return $invoice;
    }

    public function getOrderStoreId() {
        return $this->getOrder()->getStore()->getId();
    }

    public function getStoreInformation() {
        $settings = array();
        $storeId = $this->getOrderStoreId();
        $storePhone = Mage::getStoreConfig('general/store_information/phone', $storeId);
        $storeName = Mage::getStoreConfig('general/store_information/name', $storeId);
        $storeAddress = Mage::getStoreConfig('general/store_information/address', $storeId);
        $userData = $this->getUserDataFromOrder();
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            $pos_user_id = $userData['user_id'];
            $userCollection = Mage::getModel('inventorywebpos/webposuser')->getCollection()
                    ->addFieldToFilter('user_id', $pos_user_id);
            $warehouseId = $userCollection->getfirstItem()->getWarehouseId();
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
            $storeName = $warehouse->getData('warehouse_name');
            $street = $warehouse->getData('street');
            $city = $warehouse->getData('city');
            $country_id = $warehouse->getData('country_id');
            $postcode = $warehouse->getData('postcode');
            $storeAddress = $street . "," . $city . "," . $country_id . "," . $postcode;
        }
        $settings['storeName'] = $storeName;
        $settings['storePhone'] = $storePhone;
        $settings['storeAddress'] = $storeAddress;
        return $settings;
    }

    public function getUserDataFromOrder() {
        $pos_user_id = $this->getOrder()->getWebposAdminId();
        $pos_user = Mage::getModel('webpos/user')->load($pos_user_id);
        return $pos_user->getData();
    }

    public function getReceiptSettings() {
        $storeId = $this->getOrderStoreId();
        $settings = array();
        $settings['date_format'] = Mage::getStoreConfig('webpos/receipt/date_format',$storeId);
        $settings['show_store_information'] = Mage::getStoreConfig('webpos/receipt/show_store_information',$storeId);
        $settings['show_cashier_name'] = Mage::getStoreConfig('webpos/receipt/show_cashier_name',$storeId);
        $settings['show_comment'] = Mage::getStoreConfig('webpos/receipt/show_comment',$storeId);
        $settings['show_barcode'] = Mage::getStoreConfig('webpos/receipt/show_barcode',$storeId);
        $settings['show_receipt_logo'] = Mage::getStoreConfig('webpos/receipt/show_receipt_logo',$storeId);
        $settings['show_shipping_method'] = Mage::getStoreConfig('webpos/receipt/show_shipping_method',$storeId);
        $settings['show_payment_method'] = Mage::getStoreConfig('webpos/receipt/show_payment_method',$storeId);
        $settings['header_text'] = htmlentities(Mage::getStoreConfig('webpos/receipt/header_text',$storeId));
        $settings['footer_text'] = htmlentities(Mage::getStoreConfig('webpos/receipt/footer_text',$storeId));
        $settings['font_type'] = Mage::getStoreConfig('webpos/receipt/font_type',$storeId);
        $settings['webpos_logo'] = Mage::getStoreConfig('webpos/general/webpos_logo',$storeId);
        return $settings;
    }

    public function getBarcodeImgSource() {
        $type = "code128";
        $orderId = $this->getOrder()->getIncrementId();
        $barcodeOptions = array('text' => $orderId,
            'fontSize' => "14",
            'withQuietZones' => true
        );
        $rendererOptions = array();
        $imageResource = Zend_Barcode::factory(
                        $type, 'image', $barcodeOptions, $rendererOptions
        );
        return $imageResource;
    }

}

?>