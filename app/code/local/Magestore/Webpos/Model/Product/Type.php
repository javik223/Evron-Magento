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
 * SimiPOS Product Type Model
 * 
 * @category    Magestore
 * @package     Magestore_SimiPOS
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Abstract {

    public function prepareForCart(Varien_Object $buyRequest, $product = null) {
        if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
            return parent::prepareForCart($buyRequest, $product);
        }
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $result = parent::prepareForCart($buyRequest, $product);
        if (is_string($result)) {
            return $result;
        }
        reset($result);
        $product = current($result);
        $result = $this->_prepareWebPOSProduct($buyRequest, $product);
        return $result;
    }

    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode) {
        if (version_compare(Mage::getVersion(), '1.5.0', '<')) {
            return parent::_prepareProduct($buyRequest, $product, $processMode);
        }
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        if (is_string($result)) {
            return $result;
        }
        reset($result);
        $product = current($result);
        $result = $this->_prepareWebPOSProduct($buyRequest, $product);
        return $result;
    }

    protected function _prepareWebPOSProduct(Varien_Object $buyRequest, $product) {
        if ($name = $buyRequest->getData('name')) {
            $product->addCustomOption('name', $name);
            $product->setName($name);
        }
		$product->addCustomOption('tax_class_id', $buyRequest->getData('tax_class_id'));
        $product->addCustomOption('price', $buyRequest->getData('price'));
        $product->addCustomOption('is_virtual', $buyRequest->getData('is_virtual'));
        return array($product);
    }

    public function isVirtual($product = null) {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if ($isVirtual = $product->getCustomOption('is_virtual')) {
            return (bool) $isVirtual->getValue();
        }
        return true;
    }

}
