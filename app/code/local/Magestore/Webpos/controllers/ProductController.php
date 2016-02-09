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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_ProductController extends Magestore_Webpos_Controller_Action {

    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function isVirtual() {
        return $this->getOnepage()->getQuote()->isVirtual();
    }

    public function _getWebposSession() {
        return Mage::getSingleton('webpos/session');
    }

    public function reloadProductListAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $cache = Mage::app()->getCache();
        $session = Mage::getModel('core/session');
        $page = 1;
        $result = array();
        $cacheId = '';
        $type = $this->getRequest()->getParam('type');
        $categoryId = $this->getRequest()->getParam('categoryId');
        $pos_cpage = $session->getData('pos_cpage');
        $keyword = $this->getRequest()->getParam('keyword');
        $keyword = strtolower($keyword);
        if (isset($type) && $type == 'next' && isset($pos_cpage)) {
            $page = $pos_cpage + 1;
        } elseif (isset($type) && $type == 'back' && isset($pos_cpage)) {
            $page = $pos_cpage - 1;
        }
        if (isset($page) && $page < 0)
            $page = 1;
        $session->setData('pos_cpage', $page);
        if (isset($categoryId)) {
            $session->setData('pos_ccategory', $categoryId);
            $cacheId = 'webpos_product_list_' . $categoryId . '_' . $page;
        }
        if (!empty($keyword)) {
            $keyword = preg_replace('/\s+/', '', $keyword);
            $cacheId = 'webpos_product_list_' . $keyword . '_' . $page;
        }

        $session->setData('pos_cpage', $page);
        if ($type == 'recent') {
            $session->setData('special_category', 'recent');
            $cacheId = 'webpos_product_list_recent_' . $page;
        } elseif ($type == 'popular') {
            $session->setData('special_category', 'popular');
            $cacheId = 'webpos_product_list_popular_' . $page;
        } elseif ($type != 'next' && $type != 'back') {
            $session->setData('special_category', null);
        }
        /*
          $listProduct = $cache->load($cacheId);
          if(!$listProduct){
          $listProduct = $this->getLayout()->createBlock('webpos/listproduct')
          ->setTemplate('webpos/webpos/productlist.phtml')
          ->toHtml();
          $cache->save($listProduct, $cacheId, array("webpos_product_list"), 3600);
          }
         */
        //$result['productlist'] = $listProduct;
        /**/

        $result['productlist'] = $this->getLayout()->createBlock('webpos/listproduct')
                ->setCurrentCurrencyCode($this->getRequest()->getParam('current_currency'))
                ->setTemplate('webpos/webpos/productlist.phtml')
                ->toHtml();
        /**/
        $curPage = Mage::getModel('core/session')->getData('pos_cpage');
        $result['numberProduct'] = $numberProduct = Mage::getModel('core/session')->getData('numberProduct');
        $result['strNumberProduct'] = $this->__("Totals: " . $numberProduct . ' products');
        $result['curPage'] = $curPage;
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function savecartAction() {
        $helper = Mage::helper('webpos');
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        if ($this->getRequest()->getParam('needtoUpdate') == 'custom-sale') {
            Mage::getSingleton('checkout/cart')->save();
            $helper->getDefaultCustomer();
            $this->loadLayout(false);
            $this->renderLayout();
            return;
        }
        $result = array();
        try {
            $helper->getDefaultCustomer();
            $webposNewCustomOption = $this->getRequest()->getParam('webposNewCustomOption');
            $storecreditData = $this->getRequest()->getParam('storecreditData');
            $productsInfoStr = $this->getRequest()->getParam('productInfo');
            $options = $this->getRequest()->getParam('options');
            $bundle_option_data = $this->getRequest()->getParam('bundle_option');
            $bundle_option_qty_data = $this->getRequest()->getParam('bundle_option_qty');
            $customOptions = Zend_Json::decode($options);
            $bundle_option = Zend_Json::decode($bundle_option_data);
            $bundle_option_qty = Zend_Json::decode($bundle_option_qty_data);
            $storecreditAmount = Zend_Json::decode($storecreditData);
            $webposNewCustomOption = Zend_Json::decode($webposNewCustomOption);
            $customPrice = $this->getRequest()->getParam('customPrice');
            $cart = Mage::getSingleton('checkout/cart');
            $session = $this->getSession();
            $quote = $cart->getQuote();
            $items = $quote->getAllItems();
            $errorMessage = '';
            $requestInfos = array();
            if (isset($productsInfoStr)) {
                $productsInfo = explode(',', $productsInfoStr);
                if (count($productsInfo) > 0) {
                    foreach ($productsInfo as $productInfo) {
                        $productData = array('id' => '', 'isnew' => false, 'params' => array('qty' => 0, 'super_attribute' => array()));
                        $data = explode('$s$', $productInfo);
                        if (count($data) > 1) {
                            $productId_itemId = (int) $data[0];

                            /* 20150904 - Decimal Qty */
                            $qty = (float) $data[1];
                            /* 20150904 - Decimal Qty */
                            $item = $quote->getItemById($productId_itemId);
                            if (empty($item))
                                $productData['isnew'] = true;
                            else
                                $productData['params']['product'] = $item->getProduct()->getId();
                            $productData['id'] = $productId_itemId;
                            $productData['params']['qty'] = $qty;

                            if (count($storecreditAmount) > 0) {
                                foreach ($storecreditAmount as $prdid => $amount) {
                                    if ($prdid != $productId_itemId)
                                        continue;
                                    $productData['params']['amount'] = $amount;
                                    $productData['params']['credit_price_amount'] = $amount;
                                }
                            }
                            $hasco = false;
                            if (isset($data[2])) {
                                $index = '';
                                if (strpos($data[2], '_index_') >= 0) {
                                    $options_and_custom = explode('_index_', $data[2]);
                                    $options = explode('|', $options_and_custom[0]);
                                    $hasco = true;
                                    $index = $options_and_custom[1];
                                } else {
                                    $options = explode('|', $data[2]);
                                }

                                if (count($options) > 0) {
                                    foreach ($options as $option) {
                                        $optionData = explode('-', $option);
                                        $optionId = $optionData[0];
                                        $optionValue = (int) $optionData[1];
                                        $productData['params']['super_attribute'][$optionId] = $optionValue;
                                    }
                                }
                                if ($hasco == true) {
                                    if (count($webposNewCustomOption) > 0) {
                                        foreach ($webposNewCustomOption as $prdid => $customOptions) {

                                            foreach ($customOptions as $key => $customOption) {
                                                if (count($customOption) > 0 && (int) $prdid == $productId_itemId && $key == $index) {
                                                    foreach ($customOption as $optionId => $optionValue) {
                                                        $filePath = Mage::getBaseDir('media') . DS . 'webpos' . DS . 'custom_options' . DS . $optionValue;
                                                        $subPath = DS . 'media' . DS . 'webpos' . DS . 'custom_options' . DS . $optionValue;
                                                        if (is_file($filePath)) {
                                                            $filesize = filesize($filePath);
                                                            $fileData = getimagesize($filePath);
                                                            $width = (isset($fileData[0]) && $fileData[0] >= 0) ? $fileData[0] : 0;
                                                            $height = (isset($fileData[1]) && $fileData[1] >= 0) ? $fileData[1] : 0;
                                                            $productData['params']['options'][$optionId]['type'] = 'application/octet-stream';
                                                            $productData['params']['options'][$optionId]['width'] = $width;
                                                            $productData['params']['options'][$optionId]['height'] = $height;
                                                            $productData['params']['options'][$optionId]['size'] = $filesize;
                                                            $productData['params']['options'][$optionId]['title'] = $optionValue;
                                                            $productData['params']['options'][$optionId]['quote_path'] = $subPath;
                                                            $productData['params']['options'][$optionId]['order_path'] = $subPath;
                                                            $productData['params']['options'][$optionId]['fullpath'] = $filePath;
                                                            $productData['params']['options'][$optionId]['secret_key'] = substr(md5(file_get_contents($filePath)), 0, 20);
                                                        } else {
                                                            $productData['params']['options'][$optionId] = $optionValue;
                                                        }
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($hasco == true) {
                                
                            } else
                            if (count($customOptions) > 0)
                                foreach ($customOptions as $prdid => $customOption) {
                                    if (count($customOption) > 0 && (int) $prdid == $productId_itemId) {
                                        foreach ($customOption as $optionId => $optionValue) {
                                            $filePath = Mage::getBaseDir('media') . DS . 'webpos' . DS . 'custom_options' . DS . $optionValue;
                                            $subPath = DS . 'media' . DS . 'webpos' . DS . 'custom_options' . DS . $optionValue;
                                            if (is_file($filePath)) {
                                                $filesize = filesize($filePath);
                                                $fileData = getimagesize($filePath);
                                                $width = (isset($fileData[0]) && $fileData[0] >= 0) ? $fileData[0] : 0;
                                                $height = (isset($fileData[1]) && $fileData[1] >= 0) ? $fileData[1] : 0;
                                                $productData['params']['options'][$optionId]['type'] = 'application/octet-stream';
                                                $productData['params']['options'][$optionId]['width'] = $width;
                                                $productData['params']['options'][$optionId]['height'] = $height;
                                                $productData['params']['options'][$optionId]['size'] = $filesize;
                                                $productData['params']['options'][$optionId]['title'] = $optionValue;
                                                $productData['params']['options'][$optionId]['quote_path'] = $subPath;
                                                $productData['params']['options'][$optionId]['order_path'] = $subPath;
                                                $productData['params']['options'][$optionId]['fullpath'] = $filePath;
                                                $productData['params']['options'][$optionId]['secret_key'] = substr(md5(file_get_contents($filePath)), 0, 20);
                                            } else {
                                                $productData['params']['options'][$optionId] = $optionValue;
                                            }
                                        }
                                        break;
                                    }
                                }
                            if (count($bundle_option) > 0)
                                foreach ($bundle_option as $prdid => $bundleOption) {
                                    if (count($bundleOption) > 0 && (int) $prdid == $productId_itemId) {
                                        foreach ($bundleOption as $optionId => $optionValue) {
                                            $productData['params']['bundle_option'][$optionId] = $optionValue;
                                        }
                                        break;
                                    }
                                }
                            if (count($bundle_option_qty) > 0)
                                foreach ($bundle_option_qty as $prdid => $bundleQty) {
                                    if (count($bundleQty) > 0 && (int) $prdid == $productId_itemId) {
                                        foreach ($bundleQty as $optionId => $qty) {
                                            $productData['params']['bundle_option_qty'][$optionId] = $qty;
                                        }
                                        break;
                                    }
                                }
                            $requestInfos[] = $productData;
                        }
                    }
                }
            }
            /*
              zend_debug::dump($requestInfos);
              die();
             */
            $updateItemsData = array();
            $newItemsData = array();
            foreach ($requestInfos as $requestInfo) {
                if ($requestInfo['isnew'] == true)
                    $newItemsData[] = array('id' => $requestInfo['id'], 'params' => $requestInfo['params']);
                else {
                    $updateItemsData[$requestInfo['id']] = $requestInfo['params'];
                    if (isset($requestInfo['params']['options'])) {
                        $item = Mage::getSingleton('checkout/cart')->getQuote()->getItemById($requestInfo['id']);
                        $itemOptions = $item->getOptions();
                        foreach ($itemOptions as $option) {
                            if ($option->getCode() == 'info_buyRequest') {
                                $unserialized = unserialize($option->getValue());
                                $unserialized['options'] = $requestInfo['params']['options'];
                                $option->setValue(serialize($unserialized));
                            }
                        }
                        $item->setOptions($itemOptions)->save();
                    }
                }
            }
            /*
              zend_debug::dump($updateItemsData);
              zend_debug::dump($newItemsData);
              die();
             */
            if (count($newItemsData) > 0) {
                foreach ($newItemsData as $data) {
                    $prd_name = '';
                    try {
                        $product = Mage::getModel('catalog/product')->load($data['id']);
                        $prd_name = $product->getName();
                        if ($product->getTypeId() == 'downloadable') {
                            $links = Mage::getModel('downloadable/product_type')->getLinks($product);
                            foreach ($links as $link)
                                $linkId = $link->getLinkId();
                            $data['params']['links'] = array($linkId);
                        }
                        if ($data['params']['qty'] > 0) {
                            if (Mage::helper('webpos')->isInventoryWebPOS11Active()) { //Magnus
                                $mg_request = new Varien_Object();
                                $mg_request->setData($data['params']);
                                $cfproduct = $product;
                                if ($cfproduct->getTypeId() != 'configurable' && $cfproduct->getTypeId() != 'grouped' && $cfproduct->getTypeId() != 'bundle') {
                                    $productId = $data['id'];
                                } else {
                                    $cartCandidates = $cfproduct->getTypeInstance(true)
                                            ->prepareForCartAdvanced($mg_request, $cfproduct, null);
                                    foreach ($cartCandidates as $candidate) {
                                        if ($candidate->getParentProductId()) {
                                            $productId = $candidate->getEntityId();
                                        }
                                    }
                                }
                                $warehouseId = Mage::getSingleton('core/session')->getCurrentWarehouseId();
                                $wpModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                                        ->addFieldToFilter('product_id', $productId)
                                        ->addFieldToFilter('warehouse_id', $warehouseId)
                                        ->getFirstItem();
                                $wpQty = $wpModel->getAvailableQty();
                                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                                $backorders = $inventory->getBackorders();
                                if ($data['params']['qty'] > $wpQty && !$backorders) {
                                    $return['errorMessage'] .= $this->__('Can not add items to cart. Because you added %d item(s) %s to cart but it only has %d item(s) in this warehouse.', $data['params']['qty'], $prd_name, $wpQty);
                                    $this->getResponse()->setBody(Zend_Json::encode($return));
                                    return;
                                }
                            }
                            $data['product'] = $data['id'];
                            $request = new Varien_Object();
                            $request->setData($data['params']);
                            $cart->addProduct($product, $request);
                            try {
                                $productId = $data['id'];
                                $productType = $product->getTypeId();
                                $userId = Mage::helper('webpos/permission')->getCurrentUser();
                                $findToAddPopular = Mage::getModel('webpos/products')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', $productId)
                                        ->addFieldToFilter('user_id', 0)
                                        ->getFirstItem()
                                ;
                                if ($findToAddPopular->getWebposProductId()) {
                                    $oldPopularity = $findToAddPopular->getPopularity();
                                    $newPopularity = $oldPopularity + 1;
                                    $findToAddPopular->setPopularity($newPopularity);
                                    $findToAddPopular->save();
                                } else {
                                    $newRecord = Mage::getModel('webpos/products');
                                    $newRecord->setProductId($productId);
                                    $newRecord->setType($productType);
                                    $newRecord->setPopularity(1);
                                    $newRecord->save();
                                }
                                $findToAddRecent = Mage::getModel('webpos/products')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', $productId)
                                        ->addFieldToFilter('user_id', $userId)
                                        ->getFirstItem()
                                ;
                                if ($findToAddRecent->getWebposProductId()) {
                                    $findToAddRecent->setLastAdded(now());
                                    $findToAddRecent->save();
                                } else {
                                    $newRecord = Mage::getModel('webpos/products');
                                    $newRecord->setProductId($productId);
                                    $newRecord->setType($productType);
                                    $newRecord->setUserId($userId);
                                    $newRecord->setLastAdded(now());
                                    $newRecord->save();
                                }
                            } catch (Exception $e) {
                                $errorMessage .= 'Error: ' . $e->getMessage() . ' <br />';
                            }
                        }
                        if (isset($data['params']['related_product'])) {
                            $related = $data['params']['related_product'];
                            if (!empty($related)) {
                                $cart->addProductsByIds(explode(',', $related));
                            }
                        }
                    } catch (Exception $e) {
                        $errorMessage .= $prd_name . ' - ' . $e->getMessage() . ' <br />';
                    }
                }
            }
            try {
                if (count($updateItemsData) > 0) {
                    foreach ($updateItemsData as $itemId => $params) {
                        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) { /* Magnus */
                            /* $item = Mage::getSingleton('checkout/cart')->getQuote()->getItemById($itemId);
                              $oldQty = $item->getQty(); */
                            $product = Mage::getModel('catalog/product')->load($params['product']);
                            $prd_name = $product->getName();
                            $mg_request = new Varien_Object();
                            $mg_request->setData($params);
                            $cfproduct = $product;
                            if ($cfproduct->getTypeId() != 'configurable' && $cfproduct->getTypeId() != 'grouped' && $cfproduct->getTypeId() != 'bundle') {
                                $productId = $params['product'];
                            } else {
                                $cartCandidates = $cfproduct->getTypeInstance(true)
                                        ->prepareForCartAdvanced($mg_request, $cfproduct, null);
                                foreach ($cartCandidates as $candidate) {
                                    if ($candidate->getParentProductId()) {
                                        $productId = $candidate->getEntityId();
                                    }
                                }
                            }
                            $newQty = $params['qty'];
                            $warehouseId = Mage::getSingleton('core/session')->getCurrentWarehouseId();
                            $wpModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->addFieldToFilter('warehouse_id', $warehouseId)
                                    ->getFirstItem();
                            $wpQty = $wpModel->getAvailableQty();

                            if ($newQty > $wpQty) {
                                $return['errorMessage'] .= $this->__('Can not add items to cart. Because you added %d item(s) %s to cart but it only has %d item(s) in this warehouse.', $newQty, $prd_name, $wpQty);
                                $this->getResponse()->setBody(Zend_Json::encode($return));
                                return;
                            }
                        }
                        $cart->updateItem($itemId, $params);
                    }
                }
            } catch (Exception $e) {
                $errorMessage .= 'Update items failed: ' . $e->getMessage() . ' <br />';
            }
            $addResult = $cart->save();
            $session->setCartWasUpdated(true);
        } catch (Exception $e) {
            $errorMessage .= $e->getMessage() . ' <br />';
        }
        if ($errorMessage != '') {
            $result['errorMessage'] = $errorMessage;
        }

        if (isset($customPrice) && $customPrice != '') {

            $customPriceArray = explode(',', $customPrice);
            foreach ($customPriceArray as $customPrice) {
                $customPriceDetail = explode('|', $customPrice);
                if (count($customPriceDetail) > 0) {
                    $productId = $customPriceDetail[0];
                    $custom_price = $customPriceDetail[1];
                    foreach ($session->getQuote()->getAllItems() as $item) {
                        if ($item->getProduct()->getId() == $productId) {

                            $item->setCustomPrice($custom_price);
                            $item->setOriginalCustomPrice($custom_price);
                            $item->getProduct()->setIsSuperMode(true);
                            $item->save();
                            $result['customItem'][$productId] = $item->getId();
                        }
                    }
                }
            }

            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $quote->save();
            $session->setCartWasUpdated(true);
        }

        $helperPayment = Mage::helper('webpos/payment');
        $shippingAddress = $quote->getShippingAddress();
        if (isset($shippingAddress)) {
            $shippingMethod = $shippingAddress->getShippingMethod();
            if ($shippingMethod == '' && $helperPayment->isWebposShippingEnabled()) {
                $shippingAddress->setShippingMethod('webpos_shipping_free');
                $quote->setTotalsCollectedFlag(false)->collectTotals();
                $this->getOnepage()->saveShippingMethod('webpos_shipping_free');
            }
        }
        $payment_method = $quote->getPayment()->getMethod();
        if ((empty($payment_method) || $payment_method == '') && $helperPayment->isCashPaymentEnabled()) {
            $payment_method = 'cashforpos';
            $payment['method'] = $payment_method;
            Mage::helper('webpos')->savePaymentMethod($payment);
        }

        $grandTotal = $quote->getGrandTotal();
        $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
        $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
        $result['grand_total'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
        $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
        $result['number_item'] = Mage::helper('checkout/cart')->getSummaryCount();
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();
        $result['pos_items'] = $this->getLayout()->createBlock('webpos/cart_items')
                ->setTemplate('webpos/webpos/cart/items.phtml')
                ->toHtml();

        $hasError = Mage::getBlockSingleton('checkout/cart')->hasError();
        if ($hasError == true) {
            $quote = Mage::getBlockSingleton('checkout/cart')->getQuote();
            $items = $quote->getAllVisibleItems();
            if (count($items) > 0)
                foreach ($items as $item) {
                    $errorInfo = $item->getErrorInfos();
                    if (count($errorInfo) > 0 && $item->getProduct()->getSku() != 'webpos-customsale') {
                        $result['hasError'] = true;
                    }
                }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Adam 09/07/2015
     * @return type
     */
    public function productsearchAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $cache = Mage::app()->getCache();
        $session = Mage::getModel('core/session');
        $page = 1;
        $result = array();
        $cacheId = '';
        $type = $this->getRequest()->getParam('type');
        $categoryId = $this->getRequest()->getParam('categoryId');
        $pos_cpage = $session->getData('pos_cpage');
        $keyword = $this->getRequest()->getParam('keyword');
        $keyword = strtolower($keyword);
        if (isset($type) && $type == 'next' && isset($pos_cpage)) {
            $page = $pos_cpage + 1;
        } elseif (isset($type) && $type == 'back' && isset($pos_cpage)) {
            $page = $pos_cpage - 1;
        }
        if (isset($page) && $page < 0)
            $page = 1;
        $session->setData('pos_cpage', $page);
        if (isset($categoryId)) {
            $session->setData('pos_ccategory', $categoryId);
            $cacheId = 'webpos_product_list_' . $categoryId . '_' . $page;
        }
        if (!empty($keyword)) {
            $keyword = preg_replace('/\s+/', '', $keyword);
            $cacheId = 'webpos_product_list_' . $keyword . '_' . $page;
        }

        $session->setData('pos_cpage', $page);
        if ($type == 'recent') {
            $session->setData('special_category', 'recent');
            $cacheId = 'webpos_product_list_recent_' . $page;
        } elseif ($type == 'popular') {
            $session->setData('special_category', 'popular');
            $cacheId = 'webpos_product_list_popular_' . $page;
        } elseif ($type != 'next' && $type != 'back') {
            $session->setData('special_category', null);
        }
        /*
          $listProduct = $cache->load($cacheId);
          if(!$listProduct){
          $listProduct = $this->getLayout()->createBlock('webpos/listproduct')
          ->setTemplate('webpos/webpos/productlist.phtml')
          ->toHtml();
          $cache->save($listProduct, $cacheId, array("webpos_product_list"), 3600);
          }
         */
        //$result['productlist'] = $listProduct;
        /**/

        $result['productlist'] = $this->getLayout()->createBlock('webpos/listproduct')
                ->setCurrentCurrencyCode($this->getRequest()->getParam('current_currency'))
                ->setTemplate('webpos/webpos/productlist.phtml')
                ->toHtml();
        /**/
        $curPage = Mage::getModel('core/session')->getData('pos_cpage');
        $result['numberProduct'] = $numberProduct = Mage::getModel('core/session')->getData('numberProduct');
        $result['strNumberProduct'] = $this->__("Totals: " . $numberProduct . ' products');
        $result['curPage'] = $curPage;
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function deleteProductAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $result = array();
        try {
            $itemid = (int) $this->getRequest()->getParam('itemid');
            $cart = Mage::getSingleton('checkout/cart');
            $cart->removeItem($itemid);
            $cart->save();
            $quote = $cart->getQuote();
            $grandTotal = $quote->getGrandTotal();
            $result['success'] = true;
//            $downgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50);
//            $upgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50 + 50);
            $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
            $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
            $result['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
            $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
            $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
            $result['number_item'] = Mage::helper('checkout/cart')->getSummaryCount();
        } catch (Exception $e) {
            $result['errorMessage'] = $this->__($e->getMessage());
            Mage::logException($e);
        }
        /*
          $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
          ->setTemplate('webpos/webpos/shipping_method.phtml')
          ->toHtml();
          $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
          ->setTemplate('webpos/webpos/payment_method.phtml')
          ->toHtml();
          $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
          ->setTemplate('webpos/webpos/review/totals.phtml')
          ->toHtml();
          $result['pos_items'] = $this->getLayout()->createBlock('webpos/cart_items')
          ->setTemplate('webpos/webpos/cart/items.phtml')
          ->toHtml();
         */
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function deleteItemAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $id = (int) $this->getRequest()->getParam('itemId');
        if ($id) {
            try {
                Mage::getSingleton('checkout/cart')->removeItem($id)
                        ->save();
                $result['success'] = true;
                $result['subtotal_html'] = Mage::getBlockSingleton('webpos/cart_totals')->setTemplate('webpos/webpos/review/totals.phtml')->toHtml();
                $result['grand_total'] = Mage::app()->getStore()->formatPrice(Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal());
                $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                        ->setTemplate('webpos/webpos/shipping_method.phtml')
                        ->toHtml();
                $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                        ->setTemplate('webpos/webpos/payment_method.phtml')
                        ->toHtml();
            } catch (Exception $e) {
                $result['error'] = $this->__($e->getMessage());
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /*
      vietdq
     */

    public function editPriceAction() {
        $productId = $this->getRequest()->getParam('productId');
        $number = $this->getRequest()->getParam('customPrice');
        $changeType = $this->getRequest()->getParam('changeType');
        $itemId = $this->getRequest()->getParam('itemId');
        $priceEdit = $this->getRequest()->getParam('priceEdit');
        $editPriceType = $this->getRequest()->getParam('editPriceType');
        $qty = $this->getRequest()->getParam('qty');
        $hasChange = $this->getRequest()->getParam('hasChange');
        $resetPrice = $this->getRequest()->getParam('resetPrice');
        $session = Mage::getSingleton('checkout/session');


        $result = array();
        $userId = Mage::helper('webpos/permission')->getCurrentUser();
        $isCreateOrder = Mage::helper('webpos/permission')->isCreateOrder($userId);
        if (!$isCreateOrder) {
            $result = array();
            $result['message'] = $this->__('You do not have permission!');
            $this->getResponse()->setBody(json_encode($result));
            return $this;
        }
        foreach ($session->getQuote()->getAllItems() as $item) {
            if ($item->getId() == $itemId) {
                if ($qty == 0) {
                    $cart = Mage::getSingleton('checkout/cart');
                    $cart->removeItem($itemId);
                    $cart->save();
                }
                $itemPrice = $item->getProduct()->getFinalPrice();
                if ($item->getProduct()->getSku() == 'webpos-customsale') {
                    $webposPrice = Mage::getSingleton('core/session')->getData('webpos_price');
                    $webposPriceArray = unserialize($webposPrice);
                    if (isset($webposPriceArray[$itemId])) {
                        $itemPrice = $webposPriceArray[$itemId];
                    }
                }
                if ($resetPrice == 'yes') {
                    $item->setCustomPrice($itemPrice);
                    $item->setOriginalCustomPrice($itemPrice);
                    $item->getProduct()->setIsSuperMode(true);
                } else
                if ($hasChange == 'yes') {

                    if ($number != '') {
                        if ($changeType == 'dollar') {
                            if ($number >= $itemPrice) {
                                $customPrice = 0;
                            } else {
                                $customPrice = $itemPrice - $number;
                            }
                        } else {
                            if ($number >= 100) {
                                $customPrice = 0;
                            } else {
                                $customPrice = $itemPrice * (100 - $number) / 100;
                            }
                        }
                        if ($editPriceType != 'discount') {
                            $customPrice = $priceEdit;
                        }
                        if ($editPriceType == 'discount') {
                            if ($number != 0 || ($number == 0 && $item->getCustomPrice() != null)) {
                                $item->setCustomPrice($customPrice);
                                $item->setOriginalCustomPrice($customPrice);
                                $item->getProduct()->setIsSuperMode(true);
                            }
                        } else {
                            $item->setCustomPrice($customPrice);
                            $item->setOriginalCustomPrice($customPrice);
                            $item->getProduct()->setIsSuperMode(true);
                        }
                    }
                }
                $magentoVersion = Mage::getVersion();
                if (version_compare($magentoVersion, '1.5', '>=') && version_compare($magentoVersion, '1.9', '<')) {
                    //for magento 1.7 can't edit qty when custom sale not manage stock
                    if ($item->getProduct()->getSku() == 'webpos-customsale') {
                        $product_id = $item->getProduct()->getId();
                        $stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                        if (!$stock_item->getId()) {
                            $stock_item->setData('product_id', $product_id);
                            $stock_item->setData('stock_id', 1);
                        }
                        $stock_item->setData('qty', 9999);
                        $stock_item->setData('is_in_stock', 1); // is 0 or 1
                        $stock_item->setData('manage_stock', 1); // should be 1 to make something out of stock

                        try {
                            $stock_item->save();
                        } catch (Exception $e) {
                            echo "{$e}";
                        }
                    }
                }
                $item->setQty($qty);

                $item->save();
            }
        }
        $result = array();
        $session->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
        $quote = $session->getQuote();
        $grandTotal = $quote->getGrandTotal();
//        $downgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50);
//        $upgrandtotal = ($grandTotal % 50 == 0) ? $grandTotal : floor($grandTotal - $grandTotal % 50 + 50);
        $downgrandtotal = Mage::helper('webpos')->round_down_cashin($grandTotal);
        $upgrandtotal = Mage::helper('webpos')->round_up_cashin($grandTotal);
        $result['grand_total'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['grandTotal'] = Mage::app()->getStore()->formatPrice($grandTotal);
        $result['downgrandtotal'] = Mage::app()->getStore()->formatPrice($downgrandtotal);
        $result['upgrandtotal'] = Mage::app()->getStore()->formatPrice($upgrandtotal);
        $result['number_item'] = Mage::helper('checkout/cart')->getSummaryCount();
        $result['shipping_method'] = $this->getLayout()->createBlock('checkout/onepage_shipping_method_available')
                ->setTemplate('webpos/webpos/shipping_method.phtml')
                ->toHtml();
        $result['payment_method'] = $this->getLayout()->createBlock('webpos/onepage_payment_methods')
                ->setTemplate('webpos/webpos/payment_method.phtml')
                ->toHtml();
        $result['totals'] = $this->getLayout()->createBlock('webpos/cart_totals')
                ->setTemplate('webpos/webpos/review/totals.phtml')
                ->toHtml();
        $result['pos_items'] = $this->getLayout()->createBlock('webpos/cart_items')
                ->setTemplate('webpos/webpos/cart/items.phtml')
                ->toHtml();
        $this->getResponse()->setBody(Zend_Json::encode($result));
        //Mage::helper('webpos')->getDefaultCustomer();
//        $this->loadLayout(false);
//        $this->renderLayout();
//        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function loadAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $productHelper = Mage::helper('webpos/product');
        $attributeForSearch = $productHelper->getProductAttributeForSearch();
        $attributeForSearch = explode(',', $attributeForSearch);
        $offlineConfig = Mage::helper('webpos')->getOfflineConfig();
        if (isset($offlineConfig['product_per_request']) && $offlineConfig['product_per_request'] > 0)
            $numberProductPerpage = $offlineConfig['product_per_request'];
        else
            $numberProductPerpage = 50;
        if (isset($offlineConfig['customer_per_request']) && $offlineConfig['customer_per_request'] > 0)
            $numberCustomerPerpage = $offlineConfig['customer_per_request'];
        else
            $numberCustomerPerpage = 50;
        $result = $productIds = $customerIds = $taxCalculations = array();
        $curpage = 1;
        $websiteId = Mage::app()->getWebsite()->getWebsiteId();
        $storeId = Mage::app()->getStore()->getStoreId();
        $number_product_saved = $this->getRequest()->getParam('number_product_saved');
        $number_customer_saved = $this->getRequest()->getParam('number_customer_saved');
        $type = $this->getRequest()->getParam('type');
        $saved_tax_calculation = $this->getRequest()->getParam('saved_tax_calculation');
        try {

            if (isset($type) && $type == 'product') {
                $productCollection = Mage::getModel('catalog/product')->getCollection();
                $productCollection->addAttributeToSelect('*');
                Mage::dispatchEvent('webpos_block_listproduct_event', array('pos_get_product_colection' => $productCollection));

                $productCollection->setStoreId($storeId);
                $productCollection->addStoreFilter($storeId);
                $productCollection->addAttributeToFilter("status", 1);
                $productCollection->setPageSize($numberProductPerpage);
                Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($productCollection);
                //Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productCollection);
                $productCollection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
                $totalsProduct = $productCollection->getSize();
                if ($totalsProduct == $number_product_saved) {
                    
                } else {
                    if ($number_product_saved == 0) {
                        $curpage = 1;
                    } else {
                        $curpage = ($number_product_saved / $numberProductPerpage) + 1;
                    }
                    $productCollection->setCurPage($curpage);
                    foreach ($productCollection as $product) {
                        $productIds[] = $productHelper->getProductInfoResponse($product);
                    }
                }
            }
            $accountShare = Mage::getStoreConfig('customer/account_share/scope', $storeId);
            $customerCollection = Mage::getModel('customer/customer')->getCollection();
            if ($accountShare == 1)
                $customerCollection->addAttributeToFilter('website_id', array('eq' => $websiteId));
            $customerCollection->addAttributeToSelect('*');
            $customerCollection->setPageSize($numberCustomerPerpage);
            $totalsCustomer = $customerCollection->getSize();
            if ($totalsCustomer == $number_customer_saved) {
                
            } else {
                if (isset($type) && $type == 'customer') {
                    if ($number_customer_saved == 0) {


                        $curpage = 1;
                    } else {
                        $curpage = ($number_customer_saved / $numberCustomerPerpage) + 1;
                    }
                    $customerCollection->setCurPage($curpage);
                    foreach ($customerCollection as $customer) {
                        $customerIds[] = $productHelper->getCustomerInfoResponse($customer);
                    }
                }
            }
            if (isset($saved_tax_calculation) && $saved_tax_calculation == 'false') {
                $taxRates = Mage::getModel('tax/calculation_rate')->getCollection();
                if ($taxRates->getSize() > 0)
                    foreach ($taxRates as $rate) {
                        $taxCalculations[$rate->getData('tax_calculation_rate_id')] = $rate->getData();
                    }
            }
            $date = getdate();
            $updated_time = $date[0];
            $result['updated_time'] = $updated_time;
            $result['taxCalculations'] = $taxCalculations;
            $result['productIds'] = $productIds;
            $result['totalsProduct'] = $totalsProduct;
            $result['customerIds'] = $customerIds;
            $result['totalsCustomer'] = $totalsCustomer;
            $result['success'] = true;
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function checkStockAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        try {
            $last_updated_time = $this->getRequest()->getParam('last_updated_time');
            $result = $productIds = $updated_products = $product_updated_data = $customer_updated_data = array();
            $productHelper = Mage::helper('webpos/product');
            $outOfStockItems = Mage::getModel('cataloginventory/stock_item')->getCollection()
                    ->addFieldToSelect('product_id')
                    ->addFieldToFilter('is_in_stock', 0);
            if ($outOfStockItems->getSize() > 0)
                foreach ($outOfStockItems as $item) {
                    $productIds[] = $item->getData('product_id');
                }
            $products = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            if ($products->getSize() > 0) {
                foreach ($products as $product) {
                    $productIds[] = $product->getId();
                }
            }
            $updated_products = $productHelper->getUpdatedProductFromFile($last_updated_time);
            if (count($updated_products) > 0) {
                foreach ($updated_products as $productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $product_updated_data[$productId] = $productHelper->getProductInfoResponse($product);
                }
            }
            $updated_customers = $productHelper->getUpdatedCustomerFromFile($last_updated_time);
            if (count($updated_customers) > 0) {
                foreach ($updated_customers as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer_updated_data[$customerId] = $productHelper->getCustomerInfoResponse($customer);
                }
            }
            $date = getdate();
            $updated_time = $date[0];
            $result['updated_time'] = $updated_time;
            $result['product_updated_data'] = $product_updated_data;
            $result['customer_updated_data'] = $customer_updated_data;
            $result['productIds'] = $productIds;
            $result['success'] = true;
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function uploadFileAction() {
        if (isset($_FILES['cofile']['name']) && $_FILES['cofile']['name'] != '') {
            try {
                $date = getdate();
                $uploaded_time = $date[0];
                $path = Mage::getBaseDir('media') . DS . 'webpos' . DS . 'custom_options' . DS;
                $fname = $_FILES['cofile']['name'];
                $uploader = new Varien_File_Uploader('cofile');
                $uploader->setAllowedExtensions(array('doc', 'pdf', 'txt', 'docx', 'jpg', 'jpeg', 'png', 'gif'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->save($path, $fname);
                echo $path . $fname;
            } catch (Exception $e) {
                echo 'Error Message: ' . $e->getMessage();
            } catch (Exception $e) {
                echo 'Error Message: ' . $e->getMessage();
            }
        }
    }

    public function reinstallAction() {
        $installer = new Mage_Core_Model_Resource_Setup('core_setup');

        $installer->startSetup();
        $installer->run("
			DELETE FROM {$installer->getTable('core_resource')} WHERE `{$installer->getTable('core_resource')}`.`code` = 'webpos_setup';
		");
        $installer->endSetup();
        echo $this->__('Successfully! Please refresh magento cache to get the new version');
    }

    public function reinstall2Action() {
        $installer = new Mage_Core_Model_Resource_Setup();
        $installer->startSetup();
        $webposHelper = Mage::helper("webpos");

        $installer->run("
	DROP TABLE IF EXISTS {$installer->getTable('webpos_user')};

	CREATE TABLE {$installer->getTable('webpos_user')} (
	  `user_id` int(11) unsigned NOT NULL auto_increment,
	  `username` varchar(255) NOT NULL default '',
	  `password` varchar(255) NOT NULL default '',
	  `display_name` text default '',
          `email` text default '',
	  `location_id` int(11) NULL,
	  `role_id` int(11) NULL,
	  `status` smallint(6) NOT NULL default '1',
	  `auto_logout` tinyint(2) DEFAULT 0,
	  PRIMARY KEY (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS {$installer->getTable('webpos_role')};

	CREATE TABLE {$installer->getTable('webpos_role')} (
	  `role_id` int(11) unsigned NOT NULL auto_increment,
	  `display_name` text default '',
      `permission_ids` text default '',
      `description` text default '',
      `active` tinyint(1) default 1,
      PRIMARY KEY (`role_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS {$installer->getTable('webpos_user_location')};

	CREATE TABLE {$installer->getTable('webpos_user_location')} (
	  `location_id` int(11) unsigned NOT NULL auto_increment,
      `display_name` text default '',
      `address` text default '',
      `description` text default '',
      PRIMARY KEY (`location_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    DROP TABLE IF EXISTS {$installer->getTable('webpos_order')};

	CREATE TABLE {$installer->getTable('webpos_order')} (
	  `webpos_order_id` int(11) unsigned NOT NULL auto_increment,
      `order_id` text default '',
      `user_id` int(11) NULL,
      `order_comment` text default '',
      `order_totals` decimal(12,4) UNSIGNED,
      `product_ids` text default '',
      `unit_prices` text default '',
      `order_status` text default '',
      `user_location_id` int(11) NULL,
	  `user_role_id` int(11) NULL,
	  `created_date` datetime,
	  PRIMARY KEY (`webpos_order_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	DROP TABLE IF EXISTS {$installer->getTable('webpos_products')};

	CREATE TABLE {$installer->getTable('webpos_products')} (
	  `webpos_product_id` int(11) unsigned NOT NULL auto_increment,
      `product_id` int NOT NULL,
      `type` text ,
      `popularity` int ,
      `last_added` datetime,
      `user_id` int(11) DEFAULT 0,
      PRIMARY KEY (`webpos_product_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
          DROP TABLE IF EXISTS {$installer->getTable('webpos_transaction')};
          CREATE TABLE {$installer->getTable('webpos_transaction')} (
          `transaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `cash_in` float DEFAULT '0',
          `cash_out` float DEFAULT '0',
          `type` varchar(20) DEFAULT NULL,
          `user_id` int(4) unsigned DEFAULT NULL,
          `location_id` int(4) unsigned NULL DEFAULT 0,
          `till_id` int(4) unsigned NULL DEFAULT 0,
          `created_time` datetime DEFAULT NULL,
          `order_id` varchar(20) DEFAULT NULL,
          `store_id` int(4) unsigned DEFAULT '0',
          `previous_balance` float DEFAULT '0',
          `current_balance` float DEFAULT '0',
          `payment_method` varchar(255) DEFAULT NULL,
          `comment` varchar(255) DEFAULT NULL,
          `transac_flag` int(2) unsigned NOT NULL DEFAULT '1',
          PRIMARY KEY (`transaction_id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

          DROP TABLE IF EXISTS {$installer->getTable('webpos_xreport')};
          CREATE TABLE {$installer->getTable('webpos_xreport')} (
          `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `created_time` datetime DEFAULT NULL,
          `user_id` int(11) unsigned NOT NULL DEFAULT '0',
          `store_id` int(11) unsigned NOT NULL DEFAULT '0',
          `order_total` int(11) unsigned NOT NULL DEFAULT '0',
          `amount_total` float DEFAULT '0',
          `transfer_amount` float DEFAULT '0',
          `tax_amount` float DEFAULT '0',
          `refund_amount` float DEFAULT '0',
          `discount_amount` float DEFAULT '0',
          `cash_system` float DEFAULT '0',
          `cash_count` float DEFAULT '0',
          `check_system` float DEFAULT '0',
          `check_count` float DEFAULT '0',
          `cc_system` float DEFAULT '0',
          `cc_count` float DEFAULT '0',
          `other_system` float DEFAULT '0',
          `other_count` float DEFAULT '0',
          `till_id` int(11) unsigned NOT NULL DEFAULT 0,
          `location_id` int(11) unsigned NOT NULL DEFAULT 0,
          PRIMARY KEY (`report_id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

          DROP TABLE IF EXISTS {$installer->getTable('webpos_till')};
          CREATE TABLE {$installer->getTable('webpos_till')} (
          `till_id` int(11) unsigned NOT NULL auto_increment,
          `till_name` varchar(255) NOT NULL default '',
          `location_id` int(11) NULL,
          `status` smallint(6) NOT NULL default '1',
          PRIMARY KEY (`till_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

          ");
        if (!$webposHelper->columnExist($installer->getTable('sales_flat_order'), 'webpos_admin_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales_flat_order')} ADD COLUMN `webpos_admin_id` int(10) default NULL");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales_flat_order'), 'webpos_admin_name')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales_flat_order')} ADD COLUMN `webpos_admin_name` varchar(255) default NULL");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_user'), 'store_ids')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_user')} ADD `store_ids` VARCHAR(255) NOT NULL AFTER `user_id`;");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_transaction'), 'user_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_transaction')} ADD `user_id` int(4) unsigned DEFAULT NULL;");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_transaction'), 'till_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_transaction')} ADD `till_id` unsigned NULL DEFAULT 0;");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_transaction'), 'location_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_transaction')} ADD `location_id` unsigned NULL DEFAULT 0;");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'till_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/order')} ADD `till_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'location_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/order')} ADD `location_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/quote_payment'), 'ccforpos_ref_no')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/quote_payment')} ADD `ccforpos_ref_no` VARCHAR( 255 ) NOT NULL; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order_payment'), 'ccforpos_ref_no')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/order_payment')} ADD `ccforpos_ref_no` VARCHAR( 255 ) NOT NULL; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_order'), 'till_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('webpos_order')} ADD `till_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_order'), 'location_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('webpos_order')} ADD `location_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
		if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_cash` decimal(12,4) NOT NULL default '0'");
		}
		if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_cash` decimal(12,4) NOT NULL default '0'");
		}
		if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_cash` decimal(12,4) NOT NULL default '0'");
		}
		if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_cash` decimal(12,4) NOT NULL default '0'");
		}
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_change` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_change` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_change` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_change` decimal(12,4) NOT NULL default '0'");
        }
        $installer->endSetup();
        Mage::app()->setUpdateMode(false);
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = Mage::getModel('catalog/product');

        if ($productId = $product->getIdBySku('webpos-customsale')) {
            return $product->load($productId);
        }

        $entityType = $product->getResource()->getEntityType();
        $attributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityType->getId())
                ->getFirstItem();
        $websiteIds = Mage::getModel('core/website')->getCollection()
                ->addFieldToFilter('website_id', array('neq' => 0))
                ->getAllIds();
        $product->setAttributeSetId($attributeSet->getId())
                ->setTypeId('customsale')
                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                ->setSku('webpos-customsale')
                ->setWebsiteIds($websiteIds)
                ->setStockData(array(
                    'manage_stock' => 0,
                    'use_config_manage_stock' => 0,
        ));
        $product->addData(array(
            'name' => 'Custom Sale',
            'weight' => 1,
            'status' => 1,
            'visibility' => 1,
            'price' => 0,
            'description' => 'Custom Sale for POS system',
            'short_description' => 'Custom Sale for POS system',
        ));

        if (!is_array($errors = $product->validate())) {
            try {
                $product->save();
                if (!$product->getId()) {
                    $lastProduct = Mage::getModel('catalog/product')->getCollection()->setOrder('entity_id', 'DESC')->getFirstItem();
                    $lastProductId = $lastProduct->getId();
                    $product->setName('Custom Sale')->setId($lastProductId + 1)->save();
                    Mage::getModel('catalog/product')->load(0)->delete();
                }
            } catch (Exception $e) {
                return false;
            }
        }
		$webposHelper->addDefaultData();
		$installer = new Mage_Eav_Model_Entity_Setup ();
		$installer->startSetup();
		$fieldList = array(
					'tax_class_id'
		);
		foreach ($fieldList as $field) {
			$applyTo = explode(',', $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to'));
			if (!in_array('customsale', $applyTo)) {
				$applyTo[] = 'customsale';
				$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
			}
		}
		$installer->endSetup();

		$product = Mage::getModel('catalog/product');
		$product->getIdBySku('webpos-customsale');
		if ($product->getId()) {
			try {
				$product->setData('tax_class_id',0);
				$product->save();
			} catch (Exception $e) {
				Zend_debug::dump($e->getMessage());
			}
		}
        echo $this->__('Successfully! Please refresh magento cache to get the new version');
    }

    public function updatedb23Action() {
        $installer = new Mage_Core_Model_Resource_Setup();
        $installer->startSetup();
        $webposHelper = Mage::helper("webpos");
        if (!$webposHelper->columnExist($installer->getTable('sales_flat_order'), 'webpos_admin_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales_flat_order')} ADD COLUMN `webpos_admin_id` int(10) default NULL");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales_flat_order'), 'webpos_admin_name')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales_flat_order')} ADD COLUMN `webpos_admin_name` varchar(255) default NULL");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_user'), 'store_ids')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_user')} ADD `store_ids` VARCHAR(255) NOT NULL AFTER `user_id`;");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_transaction'), 'user_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_transaction')} ADD `user_id` int(4) unsigned DEFAULT NULL;");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_transaction'), 'till_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_transaction')} ADD `till_id` unsigned NULL DEFAULT 0;");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_transaction'), 'location_id')) {
            $installer->run("ALTER TABLE {$installer->getTable('webpos_transaction')} ADD `location_id` unsigned NULL DEFAULT 0;");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'till_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/order')} ADD `till_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'location_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/order')} ADD `location_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/quote_payment'), 'ccforpos_ref_no')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/quote_payment')} ADD `ccforpos_ref_no` VARCHAR( 255 ) NOT NULL; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order_payment'), 'ccforpos_ref_no')) {
            $installer->run(" ALTER TABLE {$installer->getTable('sales/order_payment')} ADD `ccforpos_ref_no` VARCHAR( 255 ) NOT NULL; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_order'), 'till_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('webpos_order')} ADD `till_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
        if (!$webposHelper->columnExist($installer->getTable('webpos_order'), 'location_id')) {
            $installer->run(" ALTER TABLE {$installer->getTable('webpos_order')} ADD `location_id` int(11) unsigned NOT NULL DEFAULT 0; ");
        }
		if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_cash` decimal(12,4) NOT NULL default '0'");
		}
		if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_cash` decimal(12,4) NOT NULL default '0'");
		}
		if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_cash` decimal(12,4) NOT NULL default '0'");
		}
		if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_cash')) {
			$installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_cash` decimal(12,4) NOT NULL default '0'");
		}
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_ccforpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_ccforpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_cp1forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_cp1forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_cp2forpos')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_cp2forpos` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_change` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/order'), 'webpos_base_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/order')} ADD COLUMN `webpos_base_change` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_change` decimal(12,4) NOT NULL default '0'");
        }
        if (!$webposHelper->columnExist($installer->getTable('sales/invoice'), 'webpos_base_change')) {
            $installer->run("ALTER TABLE {$installer->getTable('sales/invoice')} ADD COLUMN `webpos_base_change` decimal(12,4) NOT NULL default '0'");
        }
        $installer->endSetup();
        
		$installer = new Mage_Eav_Model_Entity_Setup ();
		$installer->startSetup();
		$fieldList = array(
					'tax_class_id'
		);
		foreach ($fieldList as $field) {
			$applyTo = explode(',', $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to'));
			if (!in_array('customsale', $applyTo)) {
				$applyTo[] = 'customsale';
				$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
			}
		}
		$installer->endSetup();

		$product = Mage::getModel('catalog/product');
		$product->getIdBySku('webpos-customsale');
		if ($product->getId()) {
			try {
				$product->setData('tax_class_id',0);
				$product->save();
			} catch (Exception $e) {
				Zend_debug::dump($e->getMessage());
			}
		}
    }

}
