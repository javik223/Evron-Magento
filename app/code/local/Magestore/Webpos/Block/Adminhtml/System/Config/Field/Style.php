<?php
class Magestore_Webpos_Block_Adminhtml_System_Config_Field_Style extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        
		$html .= '<div style="float:left;">';
		$html .= $element->getLabel();
		$fieldArrays = array();
		$fieldArrays[] = 'webpos_style_management_style';
		$html .= $this->_getFieldHtml($element);
		$html .='</div>';
		if(Mage::getStoreConfig('webpos/style_management/style')){
			$style = Mage::getStoreConfig('webpos/style_management/style');
		}else{
			$style = 'orange';
		}
		$html .='<br><div id="showreview" style="padding-top:15px;">
					<img width="1000px" src="'.Mage::getBlockSingleton('core/template')->getSkinUrl('images/webpos/style/').$style.'.png" />
				 </div>';
		$html .= '
					<style type="text/css">
						#webpos_style_management .collapseable{
							display:none;
						}
					</style>
					<script type="text/javascript">
						function showreview(style)
						{
							var show = "<img width=\"1000px\" src=\"'.Mage::getBlockSingleton('core/template')->getSkinUrl('images/webpos/style/').'";
							show +=style.value+".png\" />";
							$("showreview").innerHTML=show;
						}
					</script>
		
				 ';
		
        return $html;
    }
	
	protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array('show_in_default'=>1, 'show_in_website'=>1));
        }
        return $this->_dummyElement;
    }
	
	protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }
	
	protected function _showAllOption()
	{
		return array(
			'orange'=>Mage::helper('webpos')->__('Orange'),
			'green'=>Mage::helper('webpos')->__('Green'),
			'black'=>Mage::helper('webpos')->__('Black'),
			'blue'=>Mage::helper('webpos')->__('Blue'),
			'darkblue'=>Mage::helper('webpos')->__('Dark Blue'),
			'pink'=>Mage::helper('webpos')->__('Pink'),
			'red'=>Mage::helper('webpos')->__('Red'),
			'violet'=>Mage::helper('webpos')->__('Violet'),
		);
	}
	
	protected function _optionToHtml($option, $selected)
    {	
            $html = '<option value="'.$option["key"].'"';
            $html.= isset($option['value']) ? 'title="'.$option['value'].'"' : '';
            if ($option['key']==$selected){
                $html.= ' selected="selected"';
            }
            $html.= '>'.$option['value']. '</option>'."\n";
        return $html;
    }
	
	protected function _getFieldHtml($fieldset)
    {

        $helper = Mage::helper('webpos');
        $data = Mage::getStoreConfig('webpos/style_management/style');
		$e = $this->_getDummyElement();
		$html = '';
		
		$html .= '<select style="width: 280px;margin-left:30px;" onchange="showreview(this);" id="webpos_style_management_style" name="groups[style_management][fields][style][value]" class="select">';
		$allOptions = $this->_showAllOption();
		foreach($allOptions as $key=>$value){
			$option['value'] = $value;
			$option['key'] = $key;
			$selected=$data;
			$html.= $this->_optionToHtml($option, $selected);
		}
		$html.= '</select>';
	
		return $html;
    }
}