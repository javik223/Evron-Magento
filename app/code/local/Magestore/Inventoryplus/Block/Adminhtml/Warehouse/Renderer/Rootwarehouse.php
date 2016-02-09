<?php
    class Magestore_Inventoryplus_Block_Adminhtml_Warehouse_Renderer_Rootwarehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $html = '';
        $isRoot = $row->getIsRoot();
        
        if($isRoot){
            $html .= '<div style="text-transform: uppercase;font-size:10px;background-color:#3CB861;color:#fff;width:100%;height:100%"> '.$this->__('yes').' </div>';
        } else {
            $html .= '<div style="text-transform: uppercase;font-size:10px"> '.$this->__('no').' </div>';
        }
        return $html;
    }
    public function renderExport(Varien_Object $row)
    {
        $isRoot = $row->getIsRoot();        
        if($isRoot){
            $html = $this->__('yes');
        } else {
            $html = $this->__('no');
        }
        return $html;
    }
}
?>
