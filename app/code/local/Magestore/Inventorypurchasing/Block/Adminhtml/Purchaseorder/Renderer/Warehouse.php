<?php

class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Renderer_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $product_id = $row->getId();
        $warehouseIds = $row->getWarehouseId();
        $warehouseIds = explode(',', $warehouseIds);
        $count = 0;
        $contentCSV = '';
        $content = '';
		
        foreach($warehouseIds as $warehouseId){
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/inp_warehouse/edit',array('id'=>$warehouseId));
            $name = Mage::getModel('inventoryplus/warehouse')->load($warehouseId)->getWarehouseName();
            $content .= "<a href=".$url.">$name<a/>"."<br/>";
            if($count != 0) $contentCSV .= ', ';
            $contentCSV .= $name;
            $count++;
        }
        if(in_array(Mage::app()->getRequest()->getActionName(),array('exportCsv','exportXml')))
            return $contentCSV;
		if($count >= 5){
			$scroll = '<div style = "overflow-y : scroll; height : 110px">'.$content.'</div>';
			return $scroll;
		} else
        return '<label>'.$content.'</label>';
        
    }
    
    public function renderExport(Varien_Object $row){
        $warehouseIds = $row->getWarehouseId();
        $warehouseIds = explode(',', $warehouseIds); 
        $names = array();
        foreach($warehouseIds as $warehouseId){
            $names[] = Mage::getModel('inventoryplus/warehouse')->load($warehouseId)->getWarehouseName();
        }
        return implode(', ', $names);
    }

}

?>
