<?php

class NanoWebG_ElasticSearch_Block_Adminhtml_System_Config_Fieldset_Account
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    

   protected $_template = 'nanowebg/elasticsearch/system/config/fieldset/account.phtml';

   public function __construct() {

   }

    public function render(Varien_Data_Form_Element_Abstract $element){
        return $this->toHtml();
    }



    public function getAccountUrl(){

         $str = 'https://qbox.io/magento';

           return $str;
    }
  
}