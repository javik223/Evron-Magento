<?php

class Magestore_Inventoryshipment_Block_Adminhtml_Inventoryshipment_Renderer_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $inventoryShipments = Mage::getModel('inventoryplus/warehouse_shipment')
            ->getCollection()
            ->addFieldToFilter('order_id', $row->getId())
            ->addFieldToFilter('qty_shipped', array('gt' => 0));
        $inventoryShipments->getSelect()->group('warehouse_id');
        $html = '';
        $htmlExport = '';
        $whs = Mage::helper('inventoryshipment')->getAllWarehouseName();
        if ($inventoryShipments->getSize() > 0) {
            foreach ($inventoryShipments as $inventoryShipment) {
                if($inventoryShipment->getWarehouseId() != 0){
                $html .= '<a href="' . $this->getUrl('adminhtml/inp_warehouse/edit', array('id' => $inventoryShipment->getWarehouseId())) . '" >' . $whs[$inventoryShipment->getWarehouseId()] . '</a><br/>';
                $htmlExport .= $whs[$inventoryShipment->getWarehouseId()].',';
                }
            }
        } else {
			if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorydropship')) {
				$dropship = Mage::getModel('inventorydropship/inventorydropship')
						->getCollection()
						->addFieldToFilter('order_id',$row->getId())
						->addFieldToFilter('status',array('neq' => 5))
						->getFirstItem();
				if($dropship->getId()){
					$html = 'Use Dropship';
				} else {
					$html .= 'None';
				}
			}else
				$html .= 'None';
        }
        $htmlExport = rtrim($htmlExport, ',');
        if(in_array(Mage::app()->getRequest()->getActionName(),array('exportCsv','exportExcel')))
            return $htmlExport;
        return $html;
    }

}