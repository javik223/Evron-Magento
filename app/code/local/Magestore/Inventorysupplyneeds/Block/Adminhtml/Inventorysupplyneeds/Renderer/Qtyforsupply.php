<?php

class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Renderer_Qtyforsupply extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $product_id = $row->getProductId();
        $warehouse = $datefrom = $dateto = '';
        if ($requestData && isset($requestData['warehouse_select']))
            $warehouse = $requestData['warehouse_select'];
        if ($requestData && isset($requestData['date_from']))
            $datefrom = $requestData['date_from'];
        if (!$datefrom) {
            $now = now();            
            $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if ($requestData && isset($requestData['date_to']))
            $dateto = $requestData['date_to'];
        if (!$dateto) {
            $now = now();            
            $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if($datefrom)
            $datefrom = $datefrom . ' 00:00:00';
        if($dateto)
            $dateto = $dateto . ' 23:59:59';
        $method = Mage::getStoreConfig('inventory/supplyneed/supplyneeds_method');        
        $min_needs = Mage::helper('inventorysupplyneeds')->calMin($product_id, $warehouse);        
        if ($datefrom && $dateto && $method == 2 && (strtotime($datefrom) <= strtotime($dateto))) {            
//            $max_needs = ceil($row->getTotalOrder() / 10) + $min_needs;
            $max_needs = Mage::helper('inventorysupplyneeds')->calMaxAverage($product_id, $datefrom, $dateto, $warehouse);
        } elseif ($datefrom && $dateto && $method == 1 && strtotime($datefrom) <= strtotime($dateto)) {
            $max_needs = Mage::helper('inventorysupplyneeds')->calMaxExponential($product_id, $datefrom, $dateto, $warehouse);
        } else {
            $min_needs = 0;
            $max_needs = 0;
        }
        if (!$dateto || strtotime($datefrom) > strtotime($dateto)) {
            $min_needs = 0;
            $max_needs = 0;
        }
        if ($min_needs < 0) {
            $min_needs = 0;
        }
        if ($max_needs < 0) {
            $min_needs = 0;
            $max_needs = 0;
        }
        $url = $this->getUrl('adminhtml/insu_inventorysupplyneeds/chart'); //.'product_id/'.$product_id;
        return '<p style="text-align:center"><label name="maxNeeds" id="max_need_' . $product_id . '">' . $max_needs . '</label></p>
                <p style="text-align:center"><a name="url" href="javascript:void(0)" onclick="drawChart(' . $product_id . ')">Sales History</a></p>
                <script type="text/javascript">
                    function drawChart(product_id){
                        var url = "' . $url . 'product_id/"+product_id;
                        TINY.box.show(url,1, 600, 600, 1);
                    }
                </script>
                ';
    }

}