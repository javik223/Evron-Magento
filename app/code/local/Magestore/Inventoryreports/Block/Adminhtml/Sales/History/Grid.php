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
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Sales_History_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('saleshistoryGrid');
        $this->setDefaultSort('supplier_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        foreach ($requestData as $key => $value)
            if (!empty($value))
                $filterData->setData($key, $value);
        $dateFrom = $filterData->getData('date_from',null);
        $dateTo = $filterData->getData('date_to',null);  
        if(!$dateFrom || !$dateTo){
            $collection = false;
        }else{
            $dateFrom = $dateFrom.' 00:00:00';
            $dateTo = $dateTo.' 23:59:59';
            $dateFrom = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateFrom));
            $dateTo = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateTo));
            if($warehouseId = $this->getRequest()->getParam('warehouse_id')){
                $collection = Mage::getResourceModel('inventoryreports/product_collection')
                                        ->addAttributeToSelect('sku')                            
                                        ->addAttributeToSelect('name')
                                        ->addAttributeToSelect('status')
                                        ->addAttributeToSelect('price')
                                        ->addAttributeToSelect('attribute_set_id')
                                        ->addAttributeToSelect('type_id')
                                        ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
                $collection->getSelect()
                            ->join(array('warehouse_shipment' => $collection->getTable('inventoryplus/warehouse_shipment')), '`warehouse_shipment`.`product_id` = `e`.`entity_id` and `warehouse_shipment`.`warehouse_id` = \''. $warehouseId .'\'', 
                                        array('total_order' => 'sum(`warehouse_shipment`.`qty_shipped`)')
                                    )
                            ->join(array('order_shipment' => $collection->getTable('sales/shipment')), '`warehouse_shipment`.`shipment_id` = `order_shipment`.`entity_id` and `order_shipment`.`created_at` >= \''. $dateFrom .'\' and `order_shipment`.`created_at` <= \''. $dateTo.'\'', 
                                        array('')
                                    )
                            ->group('e.entity_id');                   
                $collection->setIsGroupCountSql(true);
            }else{
                $collection = Mage::getResourceModel('inventoryreports/product_collection')
                                        ->addAttributeToSelect('sku')                            
                                        ->addAttributeToSelect('name')
                                        ->addAttributeToSelect('status')
                                        ->addAttributeToSelect('price')
                                        ->addAttributeToSelect('attribute_set_id')
                                        ->addAttributeToSelect('type_id')
                                        ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
                $collection->getSelect()
                            ->join(array('order_item' => $collection->getTable('sales/order_item')), '`order_item`.`created_at` >= \''. $dateFrom .'\' and `order_item`.`created_at` <= \''. $dateTo.'\' and `e`.`entity_id` = `order_item`.`product_id`', 
                                        array('total_order' => 'sum(`order_item`.`qty_shipped`)')
                                    )
                            ->group('e.entity_id');            
                $collection->setIsGroupCountSql(true);        
            }
        }
        
//        $collection->setIsGroupCountSql(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
            $filterData = new Varien_Object();
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
            foreach ($requestData as $key => $value)
                if (!empty($value))
                    $filterData->setData($key, $value);
            $dateFrom = $filterData->getData('date_from', null);
            $dateTo = $filterData->getData('date_to', null);
            $period = $filterData->getData('period_type', null);
            
            $this->addColumn('entity_id', array(
                'header' => Mage::helper('catalog')->__('ID'),
                'align' => 'right',
                'sortable' => true,
                'width' => '60',
                'type' => 'number',
                'index' => 'entity_id'
            ));
            
            $this->addColumn('name', array(
                'header' => Mage::helper('inventoryreports')->__('Product Name'),
                'align' => 'left',
                'index' => 'name'
            ));
            $totalOrderFilter = '';
            if($this->getRequest()->getParam('warehouse_id')){                
                $totalOrderFilter = 'SUM(`warehouse_shipment`.`qty_shipped`)';
            }else{
                $totalOrderFilter = 'SUM(`order_item`.`qty_shipped`)';
            }
            $this->addColumn('total_order', array(
                'header' => Mage::helper('inventoryreports')->__('Total Ordered'),
                'align' => 'left',
                'type' => 'number',
                'index' => 'total_order',
                'filter_condition_callback' => array($this, '_filterTotalOrderedCallback'),
//                'filter_index' => 'SUM(order_item.qty_ordered)'
                'filter_index' => $totalOrderFilter
            ));
            $header = '';
            if($period == 1){
                $header = Mage::helper('inventoryreports')->__('Average Ordered(per day)');
            }elseif($period == 2){
                $header = Mage::helper('inventoryreports')->__('Average Ordered(per month)');
            }elseif($period == 3){
                $header = Mage::helper('inventoryreports')->__('Average Ordered(per year)');
            }
            $this->addColumn('average_order', array(
                'header' => $header,
                'align' => 'right',
                'type' => 'number',
                'index' => 'average_order',
                'renderer' => 'inventoryreports/adminhtml_sales_history_renderer_averageorder',
                'width' => '60',
                'filter_condition_callback' => array($this, '_filterAverageOrderCallback')
            ));
            
            $this->addColumn('total_purchase', array(
                'header' => Mage::helper('inventoryreports')->__('Total Purchased'),
                'align' => 'right',
                'type' => 'number',
                'index' => 'total_purchase',
                'width' => '60',
                'renderer' => 'inventoryreports/adminhtml_sales_history_renderer_purchased',
                'filter_condition_callback' => array($this, '_filterTotalPurchaseCallback')
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryreports')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventoryreports')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/historygrid',array('warehouse_id'=>$this->getRequest()->getParam('warehouse_id',0),'top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalOrderedCallback($collection, $column)
    {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        foreach ($requestData as $key => $value)
            if (!empty($value))
                $filterData->setData($key, $value);
        $dateFrom = $filterData->getData('date_from',null);
        $dateTo = $filterData->getData('date_to',null);  
        $dateFrom = $dateFrom.' 00:00:00';
        $dateTo = $dateTo.' 23:59:59';
        $dateFrom = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateFrom));
        $dateTo = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateTo));
        if($warehouseId = $this->getRequest()->getParam('warehouse_id')){
            $collectionClone = Mage::getResourceModel('inventoryreports/product_collection')
                                    ->addAttributeToSelect('sku')                            
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('status')
                                    ->addAttributeToSelect('price')
                                    ->addAttributeToSelect('attribute_set_id')
                                    ->addAttributeToSelect('type_id')
                                    ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
            $collectionClone->getSelect()
                        ->join(array('warehouse_shipment' => $collection->getTable('inventoryplus/warehouse_shipment')), '`warehouse_shipment`.`product_id` = `e`.`entity_id` and `warehouse_shipment`.`warehouse_id` = \''. $warehouseId .'\'', 
                                    array('total_order' => 'sum(`warehouse_shipment`.`qty_shipped`)')
                                )
                        ->join(array('order_shipment' => $collection->getTable('sales/shipment')), '`warehouse_shipment`.`shipment_id` = `order_shipment`.`entity_id` and `order_shipment`.`created_at` >= \''. $dateFrom .'\' and `order_shipment`.`created_at` <= \''. $dateTo.'\'', 
                                    array('')
                                )
                        ->group('e.entity_id');                   
        }else{
            $collectionClone = Mage::getResourceModel('inventoryreports/product_collection')
                                    ->addAttributeToSelect('sku')                            
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('status')
                                    ->addAttributeToSelect('price')
                                    ->addAttributeToSelect('attribute_set_id')
                                    ->addAttributeToSelect('type_id')
                                    ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
            $collectionClone->getSelect()
                        ->join(array('order_item' => $collection->getTable('sales/order_item')), '`order_item`.`created_at` >= \''. $dateFrom .'\' and `order_item`.`created_at` <= \''. $dateTo.'\' and `e`.`entity_id` = `order_item`.`product_id`', 
                                    array('total_order' => 'sum(`order_item`.`qty_shipped`)')
                                )
                        ->group('e.entity_id');            
        }        
        
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        $i=1;
        foreach ($collectionClone as $item) {            
            $totalOrder = $item->getData('total_order');                                    
            $pass = TRUE;            
            if(!$totalOrder){
                $pass = FALSE;
                continue;
            }
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($totalOrder) < floatval($filter['from'])) {
                    $pass = FALSE;
                    continue;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($totalOrder) > floatval($filter['to'])) {
                        $pass = FALSE;
                        continue;
                    }
                }
            }
            if ($pass) {                
                $arr[] = $item->getEntityId();
            }
        }            
        $collection->addFieldToFilter('entity_id',array('in'=>$arr));       
        
//        $filter = $column->getFilter()->getValue();
//        if($this->getRequest()->getParam('warehouse_id')){                
//            $totalOrderFilter = 'SUM(`warehouse_shipment`.`qty_shipped`)';
//        }else{
//            $totalOrderFilter = 'SUM(`order_item`.`qty_shipped`)';
//        }
//        if (isset($filter['from']) && $filter['from']) {
//            $collection->getSelect()->HAVING($totalOrderFilter.' >= ?', $filter['from']);
//        }
//        if (isset($filter['to']) && $filter['to']) {
//            $collection->getSelect()->HAVING($totalOrderFilter.' <= ?', floatval($filter['to']));
//        }                     
    }
    
    public function _filterAverageOrderCallback($collection, $column)
    {        
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        foreach ($requestData as $key => $value)
            if (!empty($value))
                $filterData->setData($key, $value);
        $dateFrom = $filterData->getData('date_from',null);
        $dateTo = $filterData->getData('date_to',null);  
        $period = $filterData->getData('period_type', null);
        $days = (strtotime($dateTo) - strtotime($dateFrom))/(60*60*24);
        $dateFrom = $dateFrom.' 00:00:00';
        $dateTo = $dateTo.' 23:59:59';
        $dateFrom = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateFrom));
        $dateTo = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateTo));              
        if($warehouseId = $this->getRequest()->getParam('warehouse_id')){
            $collectionClone = Mage::getResourceModel('inventoryreports/product_collection')
                                    ->addAttributeToSelect('sku')                            
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('status')
                                    ->addAttributeToSelect('price')
                                    ->addAttributeToSelect('attribute_set_id')
                                    ->addAttributeToSelect('type_id')
                                    ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
            $collectionClone->getSelect()
                        ->join(array('warehouse_shipment' => $collection->getTable('inventoryplus/warehouse_shipment')), '`warehouse_shipment`.`product_id` = `e`.`entity_id` and `warehouse_shipment`.`warehouse_id` = \''. $warehouseId .'\'', 
                                    array('total_order' => 'sum(`warehouse_shipment`.`qty_shipped`)')
                                )
                        ->join(array('order_shipment' => $collection->getTable('sales/shipment')), '`warehouse_shipment`.`shipment_id` = `order_shipment`.`entity_id` and `order_shipment`.`created_at` >= \''. $dateFrom .'\' and `order_shipment`.`created_at` <= \''. $dateTo.'\'', 
                                    array('')
                                )
                        ->group('e.entity_id');                               
        }else{
            $collectionClone = Mage::getResourceModel('inventoryreports/product_collection')
                                    ->addAttributeToSelect('sku')                            
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('status')
                                    ->addAttributeToSelect('price')
                                    ->addAttributeToSelect('attribute_set_id')
                                    ->addAttributeToSelect('type_id')
                                    ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
            $collectionClone->getSelect()
                        ->join(array('order_item' => $collection->getTable('sales/order_item')), '`order_item`.`created_at` >= \''. $dateFrom .'\' and `order_item`.`created_at` <= \''. $dateTo.'\' and `e`.`entity_id` = `order_item`.`product_id`', 
                                    array('total_order' => 'sum(`order_item`.`qty_shipped`)')
                                )
                        ->group('e.entity_id');            
            $collectionClone->setIsGroupCountSql(true);        
        }
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        $i=1;
        foreach ($collectionClone as $item) {            
            $totalOrder = $item->getData('total_order');  
            $order = 0;
            if($period == 1){
                $order = round(($totalOrder/$days),2);
            }elseif($period == 2){
                $order = round(($totalOrder/$days)*30,2);
            }elseif($period == 3){
                $order = round(($totalOrder/$days)*365,2);
            }
//            $order = round(($totalOrder/$days)*30,2);                      
            $pass = TRUE;            
            if(!$order){
                $pass = FALSE;
                continue;
            }
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($order) < floatval($filter['from'])) {
                    $pass = FALSE;
                    continue;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($order) > floatval($filter['to'])) {
                        $pass = FALSE;
                        continue;
                    }
                }
            }
            if ($pass) {                
                $arr[] = $item->getEntityId();
            }
        }            
        $collection->addFieldToFilter('entity_id',array('in'=>$arr));       
    }
    
    public function _filterTotalPurchaseCallback($collection, $column)
    {        
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        foreach ($requestData as $key => $value)
            if (!empty($value))
                $filterData->setData($key, $value);
        $dateFrom = $filterData->getData('date_from',null);
        $dateTo = $filterData->getData('date_to',null);  
        $days = (strtotime($dateTo) - strtotime($dateFrom))/(60*60*24);
        $dateFrom = $dateFrom.' 00:00:00';
        $dateTo = $dateTo.' 23:59:59';
        $dateFrom = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateFrom));
        $dateTo = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', strtotime($dateTo));
        $collectionClone = Mage::getResourceModel('inventoryreports/product_collection')
                                        ->addAttributeToSelect('sku')                            
                                        ->addAttributeToSelect('name')
                                        ->addAttributeToSelect('status')
                                        ->addAttributeToSelect('price')
                                        ->addAttributeToSelect('attribute_set_id')
                                        ->addAttributeToSelect('type_id')
                                        ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        $collectionClone->getSelect()
                    ->join(array('order_item' => $collection->getTable('sales/order_item')), '`order_item`.`created_at` >= \''. $dateFrom .'\' and `order_item`.`created_at` <= \''. $dateTo.'\' and `e`.`entity_id` = `order_item`.`product_id`', 
                                array('total_order' => 'sum(`order_item`.`qty_shipped`)')
                            )
                    ->group('e.entity_id');            
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        $i=1;
        foreach ($collectionClone as $item) {            
            $productId = $item->getId();
            $resource = Mage::getSingleton('core/resource');        
            $readConnection = $resource->getConnection('core_read');
            $results = '';        
            $purchaseOrderIds = array();        
            $sql = 'SELECT `purchase_order_id` FROM '.$resource->getTableName('inventorypurchasing/purchaseorder').' where (`purchase_on` >= \''. $dateFrom .'\') and (`purchase_on` <= \''. $dateTo .'\')';         

            $results = $readConnection->query($sql);            
            if($results){
                foreach($results as $result){            
                    $purchaseOrderIds[] = $result['purchase_order_id'];
                }
            }
            if($warehouseId = $this->getRequest()->getParam('warehouse_id')){
                $products = Mage::getModel('inventorypurchasing/purchaseorder_productwarehouse')
                                            ->getCollection()
                                            ->addFieldToFilter('purchase_order_id',array('in'=>$purchaseOrderIds))
                                            ->addFieldToFilter('warehouse_id',$warehouseId)
                                            ->addFieldToFilter('product_id',$productId);
            }else{
                $products = Mage::getModel('inventorypurchasing/purchaseorder_product')
                                            ->getCollection()
                                            ->addFieldToFilter('purchase_order_id',array('in'=>$purchaseOrderIds))
                                            ->addFieldToFilter('product_id',$productId);
            }
            $qtyPurchased = 0;
            if($products->getSize() > 0){
                foreach($products as $product){
                    $qtyPurchased += $product->getData('qty_recieved') - $product->getData('qty_returned');
                }
            }
            
            $pass = TRUE;            
            if(!$qtyPurchased){
                $pass = FALSE;
                continue;
            }
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($qtyPurchased) < floatval($filter['from'])) {
                    $pass = FALSE;
                    continue;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($qtyPurchased) > floatval($filter['to'])) {
                        $pass = FALSE;
                        continue;
                    }
                }
            }
            if ($pass) {                
                $arr[] = $item->getEntityId();
            }
        }            
        $collection->addFieldToFilter('entity_id',array('in'=>$arr));       
    }        
    
//    protected function _setCollectionOrder($column)
//    {
//        $collection = $this->getCollection();
//        if ($collection) {
//            $columnIndex = $column->getFilterIndex() ?
//                $column->getFilterIndex() : $column->getIndex();
//            $collection->getSelect()->order($columnIndex." ".strtoupper($column->getDir()));
//        }
//        
//        return $this;
//    }
}