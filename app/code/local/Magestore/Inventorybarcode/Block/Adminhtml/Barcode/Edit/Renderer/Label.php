<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcode_Edit_Renderer_Label extends Varien_Data_Form_Element_Abstract {

    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
	$values = explode(',', $this->getEscapedValue());
        
        $html = $this->getBold() ? '<strong>' : ''; 
      
        
        foreach($values as $id => $value){            
                $html .= $value . '<br/>';           
        }      
        
        $html.= $this->getBold() ? '</strong>' : '';
        $html.= $this->getAfterElementHtml();
        return $html;
    }

}


