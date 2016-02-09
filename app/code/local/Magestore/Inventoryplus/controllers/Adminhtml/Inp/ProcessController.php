<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */

class Magestore_Inventoryplus_Adminhtml_Inp_ProcessController extends Mage_Adminhtml_Controller_Action {

    /**
     * Run process in popup
     * 
     */
    public function runAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Do steps in process
     * 
     */
    public function doProcessAction(){
        $type = $this->getDataType();
        $count = Mage::getSingleton('adminhtml/session')->getData('IMProcess_count'.$this->getTypeKey($type));
        $count += 174;
        Mage::getSingleton('adminhtml/session')->setData('IMProcess_count'.$this->getTypeKey($type), $count);
        $total = 1000;
        $response = array('count'=> $count, 
                    'remain' => $total - $count,
                    'msgs'=> array('process' => $this->helper()->__('Processed %s/%s records.', $count, $total),
                             'finish' => $this->helper()->__('Finished %s process.', $type))
                );
        return $this->getResponse()->setBody(json_encode($response));        
    }
    
    /**
     * Get process data list
     * 
     */
    public function processDataListAction(){
        $list = array();
        $list = array('Product Adjusment', 'Scan Sales Order', 'Scan Shipment');
        $this->_resetDataCount($list);
        return $this->getResponse()->setBody(json_encode(array('list'=>$list)));
    }
    
    /**
     * Get total data items
     * 
     */
    public function countDataAction(){
        $type = $this->getDataType();
        $total = 1000;
        $response = array('total'=> $total, 
                    'msgs'=> array('start' => $this->helper()->__('Starting %s.', $type),
                             'total' => $this->helper()->__('Found %s record(s).', $total),
                             'finish' => $this->helper()->__('Finished %s process.', $type))
                );
        return $this->getResponse()->setBody(json_encode($response));
    }
    
    public function helper(){
        return Mage::helper('inventoryplus');
    }
    
    public function getDataType(){
        return $this->getRequest()->getPost('type');
    }
    
    public function getTypeKey($type){
        return str_replace(' ', '_', $type);
    }
    
    protected function _resetDataCount($types){
        foreach($types as $type){
           Mage::getSingleton('adminhtml/session')->unsetData('IMProcess_count'.$this->getTypeKey($type));
        }
    }
    
}