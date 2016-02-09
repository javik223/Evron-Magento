<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Sendstock_Edit_Tabs extends
Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('sendstock_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventorywarehouse')->__('Stock Sending Information'));
    }

    protected function _beforeToHtml() {
        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        if (!$this->getRequest()->getParam('id')) {
            if (!$source && !$target)
                $this->addTab('form_section', array(
                    'label' => Mage::helper('inventorywarehouse')->__('General Information'),
                    'title' => Mage::helper('inventorywarehouse')->__('General Information'),
                    'content' => $this->getLayout()
                        ->createBlock('inventorywarehouse/adminhtml_sendstock_edit_tab_form')
                        ->toHtml(),
                ));
            if ($source && $target) {
                $this->addTab('products_section', array(
                    'label' => Mage::helper('inventorywarehouse')->__('Send Stock'),
                    'title' => Mage::helper('inventorywarehouse')->__('Send Stock'),
                    'url' => $this->getUrl('*/*/products', array('_current' => true)),
                    'class' => 'ajax',
                ));
            }
        } else {
//            $this->addTab('form_section', array(
//                'label' => Mage::helper('inventorywarehouse')->__('General Information'),
//                'title' => Mage::helper('inventorywarehouse')->__('General Information'),
//                'content' => $this->getLayout()
//                    ->createBlock('inventorywarehouse/adminhtml_sendstock_edit_tab_form')
//                    ->toHtml(),
//            ));
            $this->addTab('products_section', array(
                'label' => Mage::helper('inventorywarehouse')->__('Send Stock'),
                'title' => Mage::helper('inventorywarehouse')->__('Send Stock'),
                'url' => $this->getUrl('*/*/products', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
                'class' => 'ajax',
            ));
        }
        return parent::_beforeToHtml();
    }
}
?>
