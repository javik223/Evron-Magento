<?php

class Magestore_Inventoryreports_Block_Adminhtml_Header_Timeselect extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_Inventoryreports_Block_Inventoryreports
     */
    protected function _prepareLayout() {
        $this->setTemplate('inventoryreports/header/time_select.phtml');
        return parent::_prepareLayout();
    }

    public function getTimeSelectOptions() {
        $options = array();
        $options['last_7_days'] = $this->__('Last 7 days');
        $options['last_30_days'] = $this->__('Last 30 days');
        $options['this_week'] = $this->__('This week');
        $options['this_month'] = $this->__('This month');
        $options['this_quarter'] = $this->__('This quarter');
        $options['this_year'] = $this->__('This year');
        $options['range'] = $this->__('Range');
        return $options;
    }
    
    public function getTimeSupplyneedOptions() {
        $options = array();
        $options['next_7_days'] = $this->__('Next 7 days');
        $options['next_30_days'] = $this->__('Next 30 days');
        $options['this_week'] = $this->__('This week');
        $options['this_month'] = $this->__('This month');
        $options['this_quarter'] = $this->__('This quarter');
        $options['this_year'] = $this->__('This year');
        $options['range'] = $this->__('Range');
        return $options;
    }

}
