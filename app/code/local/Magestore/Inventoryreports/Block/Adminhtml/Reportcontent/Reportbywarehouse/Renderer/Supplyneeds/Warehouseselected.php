<?php
    class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Renderer_Supplyneeds_Warehouseselected
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
        public function render(Varien_Object $row) 
        {
            $product_id = $row->getEntityId();
            if($product_id){
                $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
                if(empty($requestData)){$requestData = Mage::Helper('inventoryreports')->getDefaultOptionsWarehouse();}
                $warehouse = $requestData['warehouse_select'];
                $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);
                $datefrom = $gettime['date_from'];
                $dateto = $gettime['date_to'];
                $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse);
                $supplyneeds = array();
                $total_supplyneeds = 0;
                $method = Mage::getStoreConfig('inventory/supplyneed/supplyneeds_method');             
                if ($datefrom && $dateto && $method == 2 && (strtotime($datefrom) <= strtotime($dateto))) {            
                    $max_needs = Mage::helper('inventorysupplyneeds')->calMaxAverage($product_id, $datefrom, $dateto, $warehouse);
                } elseif ($datefrom && $dateto && $method == 1 && strtotime($datefrom) <= strtotime($dateto)) {
                    $max_needs = Mage::helper('inventorysupplyneeds')->calMaxExponential($product_id, $datefrom, $dateto, $warehouse);
                } else {
                    $max_needs = 0;
                }
                if($max_needs > 0){
                    $supplyneeds[$product_id] = $max_needs;
                    $total_supplyneeds += $max_needs;
                }
            }
            if($total_supplyneeds == 0){
                return '0';
            }
            else{return $total_supplyneeds;}
        }
    }
?>
