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
class Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public static function cmpAscDate($a, $b) {
        return $a->getDate() > $b->getDate();
    }

    public static function cmpDescDate($a, $b) {
        return $a->getDate() < $b->getDate();
    }

    public static function cmpAscQty($a, $b) {
        return $a->getQty() > $b->getQty();
    }

    public static function cmpDescQty($a, $b) {
        return $a->getQty() < $b->getQty();
    }

    public static function cmpAscGrandTotal($a, $b) {
        return $a->getGrandTotal() > $b->getGrandTotal();
    }

    public static function cmpDescGrandTotal($a, $b) {
        return $a->getGrandTotal() < $b->getGrandTotal();
    }

    public function getMainButtonsHtml()
    {
        return '';
    }
    
    public function __construct() {        
        parent::__construct();
        $this->setId('warehouseGrid');
        $this->setDefaultSort('warehouse_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        if (!$this->getFilterData())
            $this->setFilterData(new Varien_Object());
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $days = Mage::helper('inventorydashboard')->getDateRangeByDay(10);
        $from = $days['from'];
        $to = $days['to'];
        $timezoneLocal = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
            $from->setTimezone($timezoneLocal);
            $to->setTimezone($timezoneLocal);
            $dates = array();
            $datas = array();
            while($from->compare($to) < 0){
                $d = $from->toString('yyyy-MM-dd');
                $from->addDay(1);
                $dates[] = $d;
            }
            $categories = array();
            $series['total']['name'] = $this->__('Grand Total %s',Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol());
            $series['total']['data'] = '[';    
            $series['item']['name'] = $this->__('Qty');
            $series['item']['data'] = '[';    
            $i = 0;
            $resource = Mage::getSingleton('core/resource');        
            $readConnection = $resource->getConnection('core_read');
            $data = array();
            $collection = new Varien_Data_Collection();
            foreach($dates as $date){
                $start = $date.' 00:00:00';
                $end = $date.' 23:59:59';
                $results = '';
                $sql = 'SELECT `increment_id`,`grand_total`,`total_item_count` from '.$resource->getTableName('sales/order').' WHERE 
                                                    (`created_at` >= \''.$start.'\')
                                                and (`created_at` <= \''.$end.'\')
                                                and (`status` != "closed")
                                                and (`status` != "canceled")'; 
                $results = $readConnection->query($sql);
                $grandTotal = 0;
                $items = 0;
                if($results){                    
                    foreach($results as $result){   
                        $grandTotal += $result['grand_total'];
                        $items += $result['total_item_count'];
                    }
                }
                
                
                $data[$i]['grand_total'] = $grandTotal;
                $data[$i]['qty'] = $items;
                $data[$i]['date']= date('M d Y',  strtotime($date));
                 
                $item = new Varien_Object($data[$i]);

                $collection->addItem($item);
                $i++;        
            }
            
        $this->setCollection($collection);       
      
        return parent::_prepareCollection();
    }

    public function prepareExport() {
        $this->_prepareColumns();
        $this->_prepareCollection();
        return $this;
    }
    

    protected function _prepareColumns() {

        
        $this->addColumn('date', array(
            'header' => Mage::helper('inventoryreports')->__('Date'),
            'index' => 'date',
            'width' => '150px',
            'align' => 'left',
            'type' => 'date',
            'filter' => false
        ));
      
        $this->addColumn('qty', array(
            'header' => Mage::helper('inventoryreports')->__('Qty'),
            'align' => 'left',
            'index' => 'qty',
            'type' => 'number',
            'filter' => false
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('inventoryreports')->__("Grand Total"),
            'align' => 'left',
            'index' => 'grand_total',
            'type' => 'number',
            'filter' => false
        ));



//        $this->addExportType('*/*/exportSalesOrdersCsv', Mage::helper('adminhtml')->__('CSV'));
//        $this->addExportType('*/*/exportSalesOrdersXml', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }


    protected function _setCollectionOrder($column) {
        $filterData = $this->getFilterData();
        $collection = $this->getCollection();
        if ($collection) {
            switch ($column->getId()) {
                case 'date':
                    
                    $arr = array();
                    foreach ($collection as $item) {  
                        $arr[] = $item;
                    }
                    if ($column->getDir() == 'asc') {
                        $sorted = usort($arr, array('Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid', 'cmpAscDate'));
                    } else {
                        $sorted = usort($arr, array('Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid', 'cmpDescDate'));
                    }
                    $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
                    for ($i = 0; $i < count($arr); $i++) {
                        $temp->addItem($arr[$i]);
                    }
                    $this->setCollection($temp);
                    break;
                case 'qty':
                    
                    $arr = array();
                    foreach ($collection as $item) {   
                        $arr[] = $item;
                    }
                    if ($column->getDir() == 'asc') {
                        $sorted = usort($arr, array('Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid', 'cmpAscQty'));
                    } else {
                        $sorted = usort($arr, array('Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid', 'cmpDescQty'));
                    }
                    $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
                    for ($i = 0; $i < count($arr); $i++) {
                        $temp->addItem($arr[$i]);
                    }
                    $this->setCollection($temp);
                    break;
                case 'grand_total':
                    
                    $arr = array();
                    foreach ($collection as $item) {   
                        $arr[] = $item;
                    }
                    if ($column->getDir() == 'asc') {
                        $sorted = usort($arr, array('Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid', 'cmpAscGrandTotal'));
                    } else {
                        $sorted = usort($arr, array('Magestore_Inventoryreports_Block_Adminhtml_Sales_Orders_Grid', 'cmpDescGrandTotal'));
                    }
                    $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
                    for ($i = 0; $i < count($arr); $i++) {
                        $temp->addItem($arr[$i]);
                    }
                    $this->setCollection($temp);
                    break;
                default:
                    $filter = $column->getIndex();
                    if ($column->getFilterIndex())
                        $filter = $column->getFilterIndex();
                    if ($column->getDir() == 'asc') {
                        $collection->setOrder($filter, 'ASC');
                    } else {
                        $collection->setOrder($filter, 'DESC');
                    }
                    break;
            }
        }
    }

    
    public function getRowUrl($row) {
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/ordersgrid',array('top_filter'=>$this->getRequest()->getParam('top_filter')));
    }

}