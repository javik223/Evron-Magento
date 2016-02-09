<?php

class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyproduct_Renderer_Supplier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $block = new Magestore_Inventoryreports_Block_Adminhtml_Supplier_Product_Grid;        
        $filter   = $block->getParam($block->getVarNameFilter(), null);
        $condorder = '';
        if($filter){
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach($data as $value=>$key){
                if($value == 'supplier_id'){
                    $condorder = $key;
                }                
            }
        }
        
        $product_id = $row->getId();
        $resource = Mage::getSingleton('core/resource');        
        $readConnection = $resource->getConnection('core_read');
        $results = '';
        $supplierIds = array();
        if($condorder){
            $supplierIds[] = $condorder;
        }else{
            $sql = 'SELECT distinct(`supplier_id`) FROM '.$resource->getTableName('inventorypurchasing/supplier_product').' where `product_id` = '.$product_id;     
            $results = $readConnection->query($sql);
            if($results){
                foreach($results as $result){            
                    $supplierIds[] = $result['supplier_id'];
                }
            }
        }
        $suppliers = Mage::getModel('inventorypurchasing/supplier')
                            ->getCollection()
                            ->addFieldToFilter('supplier_id',array('in'=>$supplierIds));
        $content = '';
        $check = 0;
        foreach($suppliers as $supplier){
            $supplierId = $supplier->getId();
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/inpu_supplier/edit',array('id'=>$supplierId,'inventory'=>true));
            $name = $supplier->getSupplierName();
            if(in_array(Mage::app()->getRequest()->getActionName(),array('exportCsv','exportXml','exportCsvProductInfo','exportXmlProductInfo'))){
                if($check)
                    $content.=', '.$name;
                else
                    $content.=$name;
            }else
                $content .= "<a href=\"#\" onclick=\"showTimeDelivery(".$supplier->getId().",".$product_id.");return false;\" title=\"".Mage::helper('inventoryreports')->__('Report Time Inventory by Supplier')."\">".$name."<a/>"."<br/>";
            $check++;
        }
        return $content;
        
        $content = '';
        $check = 0;
        $supplierIds = array();
        
        foreach($supplier_products as $supplier_product){
            $supplier_id = $supplier_product->getSupplierId();
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/inpu_supplier/edit',array('id'=>$supplier_id,'inventory'=>true));
            $supplier = Mage::getModel('inventorypurchasing/supplier')
                            ->getCollection()
                            ->addFieldToFilter('supplier_id',$supplier_id)
                            ->getFirstItem();
            $name = $supplier->getSupplierName();
            if(in_array(Mage::app()->getRequest()->getActionName(),array('exportCsv','exportXml','exportCsvProductInfo','exportXmlProductInfo')))
            {
                if($check)
                $content.=', '.$name;
                else
                $content.=$name;
            }
            else
                $content .= "<a href=".$url.">".$name."<a/>"."<br/>";
            $check++;
        }
        return $content;
    }

}

?>
