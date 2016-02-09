<?php
class Magestore_Inventoryreports_Block_Adminhtml_Header_Others extends Magestore_Inventoryreports_Block_Adminhtml_Header
{
    /**
     * prepare block's layout
     *
     * @return Magestore_Inventoryreports_Block_Inventoryreports
     */
    public function _prepareLayout()
    {
        $this->setTemplate('inventoryreports/header/others.phtml');
        return parent::_prepareLayout();
    }
    
    public function getAllShippingMethods($isMultiSelect = false)
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();

        foreach($methods as $_code => $_method)
        {
            if(!$_title = Mage::getStoreConfig("carriers/$_code/title"))
                $_title = $_code;

            $options[] = array('value' => $_code, 'label' => $_title . " ($_code)");
        }

        if($isMultiSelect)
        {
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }
    
    /**
     * Get all order statuses
     * 
     * @return array
     */
    public function getOrderStatuses() {
        return Mage::getSingleton('sales/order_config')->getStatuses();
    }
    
    /**
     * Get submited order status
     * 
     * @return array
     */
    public function getOrderStatus() {
        $requestData = $this->getRequestData();
        
        if(!isset($requestData['order_status']))
            return array();
        
        if(!is_array($requestData['order_status']))
            return explode(',', $requestData['order_status']);
        
        return $requestData['order_status'];
    }
}   
