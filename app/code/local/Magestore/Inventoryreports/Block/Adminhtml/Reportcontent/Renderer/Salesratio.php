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
 * @package     Magestore_Inventoryplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Renderer_Salesratio
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected $_chartBlock = null;
    
    /**
     * Get total data
     * 
     * @return int|float
     */
    public function getTotalData() {
        if(! $chartBlock = $this->getLayout()->createBlock($this->_chartBlock)){
            return 0;
        }
        return $chartBlock->getTotalData();
    }
}
