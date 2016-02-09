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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorysupplyneeds Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Draftpo_Renderer_Supplierdropdown extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Get suppliers dropdown
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $suppliers = $this->getListSupplier($row);
        $html = '<select name=\'supplier_id[' . $row->getProductId() . ']\' '
                . 'id=\'supplier_id[' . $row->getProductId() . ']\' '
                . 'style="width:250px;" onclick=\'return false;\''
                . ($this->_canEdit() ? '' : ' disabled ')
                . 'class="po-update">';
        //$html = '<select name=\'supplier_list\' style="width:250px;" onclick=\'return false;\'>';
        if (count($suppliers)) {
            foreach ($suppliers as $data) {
                $html .= '<option value=\'' . $data['id'] . '\''
                        . ($data['id'] == $row->getSupplierId() ? 'selected="selected"' : '')
                        . ' >'
                        . $data['name']
                        . ' (' . $this->helper('core')->currency($data['cost']) . ' | '
                        . $data['tax'] . '% | '
                        . $data['discount'] . '% | '
                        . $this->helper('core')->currency($data['final_cost'])
                        . ')</option>';
            }
        }
        $html .= '</select><br/><br/>';
        return $html;
    }
    
    /**
     * Check edit permission
     * 
     * @return boolean
     */
    protected function _canEdit(){
        return true;
    }        

    /**
     * Get sorted supplier list
     * 
     * @return array
     */
    public function getListSupplier($row) {
        $suppliers = explode(';', $row->getData('supplier_list'));
        $list = array();
        if (count($suppliers)) {
            foreach ($suppliers as $supplierData) {
                $supplier = explode(',,', $supplierData);
                $supplier['id'] = $supplier[0];
                $supplier['name'] = $supplier[1];
                $supplier['cost'] = (float) $supplier[2];
                $supplier['tax'] = (float) $supplier[3];
                $supplier['discount'] = (float) $supplier[4];
                $supplier['final_cost'] = $supplier['cost'] * (100 + $supplier['tax'] - $supplier['discount']) / 100;
                $list[] = $supplier;
            }
        }
        usort($list, array($this, 'compareSupplierFinalCost'));
        return $list;
    }
    
    /**
     * Compare two suppliers
     * 
     * @param array $supplierA
     * @param array $supplierB
     * @return int
     */
    public function compareSupplierFinalCost($supplierA, $supplierB) {
        return $this->compareSupplier('final_cost', $supplierA, $supplierB);
    }
    
    /**
     * Compare two suppliers
     * 
     * @param string $field
     * @param array $supplierA
     * @param array $supplierB
     * @return int
     */
    public function compareSupplier($field, $supplierA, $supplierB) {
        if ($supplierA[$field] == $supplierB[$field])
            return 0;
        if ($supplierA[$field] < $supplierB[$field])
            return -1;
        return 1;
    }

}
