<?php

class Magestore_Inventorybarcode_Model_Search_Barcode extends Varien_Object {

    /**
     * Load search results
     *
     * @return Magestore_Inventorybarcode_Model_Search_Barcode
     */
    public function load() {
        $arr = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getModel('inventorybarcode/barcode')->getCollection()
                ->addFieldToFilter('barcode', array('like' => $this->getQuery() . '%'))
                ->setPageSize(10)
                ->load();

        if (!$collection->getSize()) {
            $collection = Mage::getModel('inventorybarcode/barcode')->getCollection()
                    ->addFieldToFilter('product_name', array('like' => $this->getQuery() . '%'))
                    ->setPageSize(10)
                    ->load();
        }

        if (!$collection->getSize()) {
            $collection = Mage::getModel('inventorybarcode/barcode')->getCollection()
                    ->addFieldToFilter('product_sku', array('like' => $this->getQuery() . '%'))
                    ->setPageSize(10)
                    ->load();
        }


        foreach ($collection->getItems() as $barcode) {
            $status = Mage::helper('inventorybarcode')->__('Enable');
            if(!$barcode->getBarcodeStatus()){
                $status = Mage::helper('inventorybarcode')->__('Disable');
            }
            $arr[] = array(
                'id' => 'barcode/1/' . $barcode->getId(),
                'type' => Mage::helper('inventorybarcode')->__('Barcode'),
                'name' => $barcode->getBarcode(),
                'product_name' => $barcode->getProductName(),
                'barcode_id' => $barcode->getId(),
                'warehouse_name' => $barcode->getWarehouseWarehouseName(),
                'status' => $status
            );
        }

        $this->setResults($arr);

        return $this;
    }

}
