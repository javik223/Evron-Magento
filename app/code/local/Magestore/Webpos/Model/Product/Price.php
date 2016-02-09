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
 * @package     Magestore_SimiPOS
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * SimiPOS Product Price Model
 * 
 * @category    Magestore
 * @package     Magestore_SimiPOS
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Product_Price extends Mage_Catalog_Model_Product_Type_Price
{
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($price = $product->getCustomOption('price')) {
            $store = Mage::app()->getStore($product->getStoreId());
            $finalPrice = $price->getValue() / $store->convertPrice(1);
        }
        return parent::_applyOptionsPrice($product, $qty, $finalPrice);
    }
}
