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
 * Inventorysupplyneeds Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const SUPPLIER_TYPE_LOWEST_COST = 1;
    const SUPPLIER_TYPE_LAST_PURCHASE = 2;
    
    /**
     *
     * @var array
     */
    protected $_forecastFilterSelect;
    

    public function setTopFilter($filter) {
        $this->_forecastFilterSelect = Mage::helper('adminhtml')->prepareFilterString($filter);
    }

    public function getForecastFilterSelect() {
        return $this->_forecastFilterSelect;
    }

    public function getForecastTo() {
        if($this->_getDraftPO()){
            return Mage::getModel('core/date')->date('d-m-Y', strtotime($this->_getDraftPO()->getForecastTo()));
        }
        $data = $this->_forecastFilterSelect;
        if (isset($data['forecast_date_to']) && $data['forecast_date_to'])
            return $data['forecast_date_to'];
        return Mage::getModel('core/date')->date('d-m-Y', '+7 days');
    }

    /**
     * Check edit mode
     * 
     * @return boolean
     */
    public function isEditModeDraftPO() {
        if (Mage::app()->getRequest()->getParam('id')) {
            return true;
        }
        return false;
    }

    public function getWarehouseSelected() {
        if($this->_getDraftPO()){
            return json_decode($this->_getDraftPO()->getWarehouses(), true);
        }
        $data = $this->_forecastFilterSelect;
        if (isset($data['warehouse_select']) && $data['warehouse_select'])
            return explode(',', $data['warehouse_select'][0]);
        $warehousesEnable = Mage::helper('inventoryplus/warehouse')->getAllWarehouseNameEnable();
        return array_keys($warehousesEnable);
    }

    public function getsupplierSelected() {
        if($this->_getDraftPO()){
            return json_decode($this->_getDraftPO()->getSuppliers(), true);
        }        
        $data = $this->_forecastFilterSelect;
        if (isset($data['supplier_select']) && $data['supplier_select'])
            return explode(',', $data['supplier_select'][0]);
        $suppliersEnable = Mage::getModel('inventorypurchasing/supplier')->getCollection();  //supplyneed plugin depends on purchasing
        $suppliersEnable->addFieldToFilter('supplier_status', 1);
        $suppliers = array();
        foreach ($suppliersEnable as $supplier) {
            $suppliers[] = $supplier->getId();
        }
        return $suppliers;
    }

    public function getHistorySelected() {
        if($this->_getDraftPO()){
             return $this->_getDraftPO()->getSalesDateRangeType();
        }            
        $data = $this->_forecastFilterSelect;
        if (isset($data['history_select']))
            return $data['history_select'];
        return '30_days';
    }

    public function getHistoryFrom() {
        if($this->_getDraftPO()){
             return Mage::getModel('core/date')->date('d-m-Y', strtotime($this->_getDraftPO()->getSalesFrom()));
        }
        $data = $this->_forecastFilterSelect;
        if (isset($data['baseon_date_from']))
            return $data['baseon_date_from'];
        return Mage::getModel('core/date')->date('d-m-Y', '-7 days');
    }

    public function getHistoryTo() {
        if($this->_getDraftPO()){
             return Mage::getModel('core/date')->date('d-m-Y', strtotime($this->_getDraftPO()->getSalesTo()));
        }        
        $data = $this->_forecastFilterSelect;
        if (isset($data['baseon_date_to']))
            return $data['baseon_date_to'];
        return Mage::getModel('core/date')->date('d-m-Y');
    }

    public function getSalesFromTo() {
        if($this->_getDraftPO()){
             return $this->_getDraftPO()->getSalesDateRange();
        }            
        $data = $this->getHistorySelected();
        $return = array();
        switch ($data) {
            case '7_days':
                $return['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00', '-7 days');
                $return['to'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $return['count'] = 7;
                break;
            case '30_days':
                $return['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00', '-30 days');
                $return['to'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $return['count'] = 30;
                break;
            case '3_months':
                $return['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00', '-3 months');
                $return['to'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $return['count'] = floor((strtotime($return['to']) - strtotime($return['from'])) / (60 * 60 * 24));
                break;
            case 'range':
                $from = $this->getHistoryFrom();
                $to = $this->getHistoryTo();
                $return['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00', $from);
                $return['to'] = Mage::getModel('core/date')->date('Y-m-d 23:59:59', $to);
                $return['count'] = floor((strtotime($return['to']) - strtotime($return['from'])) / (60 * 60 * 24));
                break;
            default :
                $return['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00', '-30 days');
                $return['to'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $return['count'] = 30;
        }
        return $return;
    }

    public function getNumberDaysForecast() {
        if($this->_getDraftPO()){
             return $this->_getDraftPO()->getNumberDaysForecast();
        }             
        $return['from'] = Mage::getModel('core/date')->date('Y-m-d 00:00:00');
        $return['to'] = Mage::getModel('core/date')->date('Y-m-d 23:59:59', $this->getForecastTo());
        $return = floor((strtotime($return['to']) - strtotime($return['from'])) / (60 * 60 * 24));
        return $return;
    }

    public function getRatePurchaseMore() {
        if($this->_getDraftPO()){
             return $this->_getDraftPO()->getPurchaseRate();
        }            
        $data = $this->_forecastFilterSelect;
        if (isset($data['purchase_more_rate']))
            return $data['purchase_more_rate'];
        return '100';
    }

    public function getWarehouseSales($productId) {//$from,$to){
        $defaultWarehouse = 1; // hard fix for Default warehouse(although could be deleted)
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection();
        $return = array();
        foreach ($warehouseCollection as $warehouse) {
            $warehouseId = $warehouse->getId();
            $collection = Mage::getModel('sales/order_item')->getCollection();
            $collection->getSelect()
                    ->joinLeft(
                            array('warehouse_order' => $collection->getTable('inventoryplus/warehouse_order')), "main_table.item_id=warehouse_order.item_id", array('warehouse_ordered' => new Zend_Db_Expr("IFNULL(warehouse_id, {$defaultWarehouse})")));
            $collection->getSelect()->where("IFNULL(warehouse_id, {$defaultWarehouse}) = {$warehouseId} AND main_table.product_id={$productId}");
            $collection->getSelect()->columns(array('total_ordered' => new Zend_Db_Expr("SUM(qty_ordered)")));
            $return[$warehouseId] = $collection->getFirstItem()->getTotalOrdered();
        }
        return $return;
    }

    public function getWarehouseSalesRatio($productId) {
        $warehouseSales = $this->getWarehouseSales($productId);
        $total = array_sum($warehouseSales);
        $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection();
        $return = array();
        foreach ($warehouseCollection as $warehouse) {
            $warehouseId = $warehouse->getId();
            $return[$warehouseId] = $warehouseSales[$warehouseId] / $total;
        }
        return $return;
    }

    public function addPOFromSupplyneedData($warehouseIds, $warehouseNames, $supplierId, $supplierName, $currency, $change_rate) {
        $data['purchase_on'] = now();
        $data['bill_name'] = $this->__('Supply Need');
        ;
        $data['warehouse_id'] = $warehouseIds;
        $data['warehouse_name'] = $warehouseNames;
        $data['supplier_id'] = $supplierId;
        $data['supplier_name'] = $supplierName;
        $data['total_products'] = 0;
        $data['total_amount'] = 0;
        $data['comments'] = 0;
        $data['currency'] = $currency;
        $data['tax_rate'] = 0;
        $data['shipping_cost'] = 0;
        $data['delivery_process'] = 0;
        $data['status'] = 5;
        $data['paid'] = 0;
        $data['total_products_recieved'] = 0;
        $data['created_by'] = 'supplyneeds';
        $data['order_placed'] = '';
        $data['started_date'] = '';
        $data['canceled_date'] = '';
        $data['expected_date'] = '';
        $data['payment_date'] = '';
        $data['ship_via'] = '';
        $data['payment_term'] = '';
        $data['change_rate'] = $change_rate;
        $data['total_product_refunded'] = 0;
        return $data;
    }

    /**
     * Prepare data to create new draft purchase order
     * 
     * @return array
     */
    public function prepareDataForDraftPO() {
        $data = $this->_prepareGeneralDrafPOData();
        $request = Mage::app()->getRequest();
        $productData = array();
        $purchaseProducts = array();
        $purchaseProductString = $request->getParam('purchase_products') ?
                $request->getParam('purchase_products') : $request->getPost('purchase_products');
        parse_str(urldecode($purchaseProductString), $purchaseProducts);
        if (count($purchaseProducts)) {
            foreach ($purchaseProducts as $pId => $enCoded) {
                $productData[$pId]['purchase_more'] = (int) str_replace('purchase_more=', '', base64_decode($enCoded));
                if ($productData[$pId]['purchase_more'] <= 0) {
                    unset($productData[$pId]);
                }
            }
        }
        if(!count($productData)) {
            return $data;
        }
        $this->chooseLastPurchasedSupplier($productData);
        $this->calculateWarehouseQty($productData);
        $data['product_data'] = $productData;
        return $data;
    }
    
    /**
     * Prepare general data of new draft purchase order
     * 
     * @return array
     */
    protected function _prepareGeneralDrafPOData(){
        $data = array();
        $request = Mage::app()->getRequest();
        $data['currency'] = $request->getParam('currency') ? $request->getParam('currency') : $request->getPost('currency');
        $data['change_rate'] = $request->getParam('change_rate') ? $request->getParam('change_rate') : $request->getPost('change_rate');
        $dateRange = $this->getSalesFromTo();
        $data['sales_from'] = $dateRange['from'];
        $data['sales_to'] = $dateRange['to'];
        $data['daterange_type'] = $this->getHistorySelected();
        $data['forecast_to'] = $this->getForecastTo();
        $data['warehouses'] = json_encode($this->getWarehouseSelected());
        $data['suppliers'] = json_encode($this->getsupplierSelected());
        $data['purchase_rate'] = $this->getRatePurchaseMore() / 100;
        return $data;
    }
    /**
     * 
     * @param array $productData
     * @return array
     */
    public function calculateWarehouseQty(&$productData) {
        $gridExport = Mage::getBlockSingleton('inventorysupplyneeds/adminhtml_inventorysupplyneeds_gridexport');
        $collection = $gridExport->getCollectionData(array_keys($productData));
        $warehouses = $this->getWarehouseSelected();
        $primaryWarehouse = Mage::helper('inventoryplus/warehouse')->getPrimaryWarehouse();
        if (count($collection)) {
            foreach ($collection as $item) {
                $warehouseQty = array();
                $productId = $item->getProductId() ? : Mage::getModel('catalog/product')->getIdBySku($item->getSku());
                $purchaseQty = $productData[$productId]['purchase_more'];
                foreach ($warehouses as $warehouseId) {
                    $warehouseQty[$warehouseId] = ceil($item->getData('supplyneeds_' . $warehouseId) - $item->getData('in_purchasing_' . $warehouseId));
                    $purchaseQty -= $warehouseQty[$warehouseId];
                }
                //add remaining qty into default warehouse
                if ($purchaseQty > 0) {
                    $defaultWarehouseId = $this->_selectDefaultWarehouse($primaryWarehouse->getId, array_keys($warehouseQty));
                    $warehouseQty[$defaultWarehouseId] += $purchaseQty;
                    $warehouseQty[$defaultWarehouseId] = ($warehouseQty[$defaultWarehouseId] < 0) ? 0 : $warehouseQty[$defaultWarehouseId];
                }
                $productData[$productId]['warehouse_purchase'] = json_encode($warehouseQty);
                //update total purchase qty for each product
                $productData[$productId]['purchase_more'] = array_sum($warehouseQty);
            }
        }
        foreach($productData as &$productItem){
            if(!isset($productItem['warehouse_purchase'])){
                $defaultWarehouseId = $this->_selectDefaultWarehouse($primaryWarehouse->getId, $warehouses);
                $productItem['warehouse_purchase'] = json_encode(array($defaultWarehouseId=>$productItem['purchase_more']));
            }
        }
        return $productData;
    }
    
    /**
     * Add warehouse statistics (available qty, in purchasing, supply needs,...) to product collection
     * 
     * @param collection $collection
     * @return collection 
     */
    public function addWarehouseStaticToCollection($collection){
        $productIds = array();
        if(count($collection)){
            foreach($collection as $item){
                $productIds[] = $item->getProductId();
            }            
        }
        if(!count($productIds))
            return;
        //get warehouse statistics
        $gridExport = Mage::getBlockSingleton('inventorysupplyneeds/adminhtml_inventorysupplyneeds_gridexport');
        $exportCollection = $gridExport->getCollectionData($productIds); 
        $warehouseStatics = array();
        $attachFields = array('available_qty', 'total_qty_ordered', 'avg_qty_ordered', 'in_purchasing', 'supplyneeds');
        foreach($exportCollection as $exportItem){
            $productId = $exportItem->getProductId() ? : Mage::getModel('catalog/product')->getIdBySku($exportItem->getSku());
            foreach($exportItem->getData() as $field=>$value){
                foreach($attachFields as $attachField){
                    if(strpos($field, $attachField.'_') > -1){
                        $warehouseStatics[$productId][$field] = $value;
                    }
                }
            }
        }
        //add statistics to collection
        foreach($collection as $item){
            if(isset($warehouseStatics[$item->getProductId()])){
                $item->addData($warehouseStatics[$item->getProductId()]);
            }
        }
        return $collection;
    }

    /**
     * 
     * @param int $primaryId
     * @param array $warehouseQtys
     * @return int
     */
    protected function _selectDefaultWarehouse($primaryId, $warehouseIds) {
        $defaultWarehouseId = null;
        if (isset($warehouseQty[$primaryId])) {
            $defaultWarehouseId = $primaryId;
        } else {
            foreach ($warehouseIds as $warehouseId) {
                $defaultWarehouseId = $warehouseId;
                break;
            }
        }
        return $defaultWarehouseId;
    }

    /**
     * Choose default suppliers for products
     * 
     * @param array $productData
     * @return array
     */
    public function chooseDefaultSupplier(&$productData) {
        if (!count($productData)) {
            return $productData;
        }
        $supplierProducts = Mage::getResourceModel('inventorypurchasing/supplier_product_collection')
                ->addFieldToFilter('product_id', array('in' => array_keys($productData)));
        if (count($supplierProducts)) {
            $supplierList = array();
            //prepare supplier list
            foreach ($supplierProducts as $supplierProduct) {
                $supplyData = $supplierProduct->getData();
                $supplyData['final_cost'] = $supplierProduct->getCost() * (100 + $supplierProduct->getTax() - $supplierProduct->getDiscount()) / 100;
                $supplierList[$supplierProduct->getProductId()][] = $supplyData;
            }
            //choose supplier for each products
            foreach ($productData as $productId => $data) {
                if(isset($productData[$productId]['supplier_id']) && $productData[$productId]['supplier_id'] )
                    continue;
                $suppliers = isset($supplierList[$productId]) ? $supplierList[$productId] : array();
                if (count($suppliers)) {
                    //sort by final_cost asc
                    usort($suppliers, array($this, 'compareSupplierFinalCost'));
                    //get first supplier
                    $productData[$productId]['supplier_id'] = $suppliers[0]['supplier_id'];
                }
            }
        }
        return $productData;
    }
    
    /**
     * Choose last purchased supplier for products
     * 
     * @param array $productData
     * @return array
     */
    public function chooseLastPurchasedSupplier(&$productData){
        if (!count($productData)) {
            return $productData;
        }
        //get last purchased suppliers
        $poProducts = Mage::getResourceModel('inventorypurchasing/purchaseorder_product_collection')
                            ->addFieldToFilter('product_id', array('in'=>array_keys($productData)))
                            ->setOrder('purchase_order_product_id', 'DESC');
        $poProducts->getSelect()->join(array('po' => $poProducts->getTable('inventorypurchasing/purchaseorder')),
                                        'main_table.purchase_order_id = po.purchase_order_id',
                                        array('supplier_id'));
        $poProducts->getSelect()->group('main_table.product_id');
        $poProducts->getSelect()->columns(array(
            'list_supplier' => new Zend_Db_Expr('GROUP_CONCAT(`supplier_id` SEPARATOR ",")'),
        ));
        if(count($poProducts)){
            foreach($poProducts as $poProduct){
                $suplierIds = explode(',', $poProduct->getData('list_supplier'));
                if(count($suplierIds)){
                    $productData[$poProduct->getProductId()]['supplier_id'] = reset($suplierIds);
                }
            }
        }
        //check if need to choose default supplier
        $needChooseDefault = false;
        foreach($productData as $productItem){
            if(!isset($productItem['supplier_id']) || !$productItem['supplier_id']){
                $needChooseDefault = true;
                break;
            }
        }
        if($needChooseDefault && 0){
            $this->chooseDefaultSupplier($productData);
        }
        return $productData;
    }
    
    /**
     * Mass change suppliers of draft purchase order
     * 
     * @param string|int $id
     * @param string|int $type
     * @return \Magestore_Inventorysupplyneeds_Model_Draftpo
     */
    public function massChangeSupplier($id, $type) {
        $draftPO = Mage::getModel('inventorysupplyneeds/draftpo')->load($id);
        $products = $draftPO->getProductCollection();
        $productData = array();
        foreach($products as $product){
            $productData[$product->getProductId()] = $product->getData();
        }
        switch($type){
            case self::SUPPLIER_TYPE_LOWEST_COST:
                $this->chooseDefaultSupplier($productData);
                break;
            case self::SUPPLIER_TYPE_LAST_PURCHASE:
                $this->chooseLastPurchasedSupplier($productData);
                    break;
        }
        $draftPO->setProductData($productData)
                ->setIsUpdateMode(true)
                ->save();
        return $draftPO;
    }

    /**
     * Compare two suppliers
     * 
     * @param array $supplierA
     * @param array $supplierB
     * @return int
     */
    public function compareSupplierFinalCost($supplierA, $supplierB) {
        return $this->compareSupplier('final_cost', $supplierA, $supplierB);
    }

    /**
     * Compare two suppliers
     * 
     * @param string $field
     * @param array $supplierA
     * @param array $supplierB
     * @return int
     */
    public function compareSupplier($field, $supplierA, $supplierB) {
        if ($supplierA[$field] == $supplierB[$field])
            return 0;
        if ($supplierA[$field] < $supplierB[$field])
            return -1;
        return 1;
    }

    /**
     * Prepare data to save to draft purchase order
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public function prepareUpdateData($field, $value) {
        $explodedField = explode(';', str_replace('[', ';', str_replace(']', ';', $field)));
        $attribute = $explodedField[0];
        $productId = $explodedField[1];
        $warehouseId = isset($explodedField[3]) ? $explodedField[3] : null;
        $updateData = array(
            'attribute' => $attribute,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'value' => $value,
        );
        return $updateData;
    }

    /**
     * 
     * @return \Magestore_Inventorysupplyneeds_Model_Draftpo
     */
    public function getDraftPO() {
        return Mage::getResourceModel('inventorysupplyneeds/draftpo_collection')
                        ->setOrder('draft_po_id', 'DESC')
                        ->getFirstItem();
    }
    
    /**
     * 
     * @return \Magestore_Inventorysupplyneeds_Model_Draftpo
     */
    protected function _getDraftPO(){
        return Mage::registry('draftpo');
    }

}
