<?php

require_once 'Mage/Checkout/controllers/CartController.php';
class Excellence_Ajax_IndexController extends Mage_Checkout_CartController {

    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        if($params['isAjax'] == 1){
            $response = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }
 
                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');
                $productId = $params['product_id'];

                $response['myId'] = $product->getId();
 
                /**
                 * Check product availability
                 */
                if (!$product) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Unable to find Product ID');
                }
 
                /**
                * Reduce item if subtract
                **/
                if (!isset($params['subtract'])) {
                    $cart->addProduct($product, $params);
                    //$quote =  Mage::getSingleton('checkout/cart')->getQuote();
                    /*$cart_id = $quote->getItemByProduct($product)->getId();
                    $response['cart_id'] = $cart_id;*/
                    // Get Cart Id 
                    if (isset($params['fromList'])) {
                        $quote = Mage::getSingleton('checkout/cart')->getQuote();
                        $_mCartItem = $quote->getItemByProduct($product);
                        $_mCartId = $_mCartItem->getId();
                        $response['updateId'] = $_mCartId;
                    }

                } else {
                   /* $_items = $cart->getItems();
                    $thisProduct = Mage::getModel('catalog/product')->load($params['product_id']);


                    foreach ($_items as $item) {
                        if ($item->getSku() == $thisProduct->getSku()) {
                            $a  = $item->getQty();
                            $response['found'] = true;
                            $item->setQty($a-1);

                        }
                    */
                    //$cartHelper = Mage::helper('checkout/cart');
                    $quote = Mage::getSingleton('checkout/cart')->getQuote();
                    $_mCartItem = $quote->getItemByProduct($product);
                   
                   if ($_mCartItem) {
                        $_mCartId = $_mCartItem->getId();
                        $_mCartPrev = $_mCartItem->getQty();
                        $_mQty = $_mCartPrev - $params['qty'];
                       
                        if ($_mQty <= 0) {
                            $quote->removeItem($_mCartId);
                       } else {
                            $_mCartItem->setQty($_mQty); 
                       }


                   } else {
                        if (isset($params['updateid'])) {
                            $updateid = $params['updateid'];

                            $_mCartItem = $quote->getItemById($updateid);  

                            if ($_mCartItem) {
                                 $_mCartPrev = $_mCartItem->getQty();
                                 $_mQty = $_mCartPrev - $params['qty'];
                                 
                                 if ($_mQty <= 0) {
                                    $quote->removeItem($updateid);
                                } else {
                                   $_mCartItem->setQty($_mQty); 
                                }
                            }
                        }
                   }
                }


                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }
 
                $cart->save();
 
                $this->_getSession()->setCartWasUpdated(true);
 
                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
 
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $response['status'] = 'SUCCESS';
                    $response['message'] = $message;
                    $response['cart_id'] = $cart->getQuote()->getId();
                    //New Code Here
                    $this->loadLayout();
                    //$toplink = "Hey";
                    //$toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                    $sidebar_block = $this->getLayout()->getBlock('topcart');
                    Mage::register('referrer_url', $this->_getRefererUrl());
                    //$sidebar = "Na";
                    $sidebar = $sidebar_block->toHtml();
                    $response['toplink'] = $toplink;
                    $response['sidebar'] = $sidebar;
                    //$response['other-items'] = json_decode($cart->_getCart());
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $msg .= $message.'<br/>';
                    }
                }
 
                $response['status'] = 'ERROR';
                $response['message'] = $msg;
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::logException($e);
            }
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }else{
            return parent::addAction();
        }
    }

    public function updateItemOptionsAction() {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('product');
        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();

         if (!isset($params['options'])) {
            $params['options'] = array();
        }
        if($params['isAjax'] == 1){
            $response = array();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $qq = Mage::getModel('checkout/cart')->getQuote();

            foreach($qq->getAllItems() as $item) {
                if ($id == $item->getProductId()) {
                    $quoteItem = $cart->getQuote()->getItemById($item->getId());
                    $itemId = $item->getId();
                }
            }

            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($itemId, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }

            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $response['status'] = 'SUCCESS';
                    $response['message'] = $message;
                    $response['cart_id'] = $cart->getQuote()->getId();
                    //New Code Here
                    $this->loadLayout();
                    //$toplink = "Hey";
                    //$toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                    $sidebar_block = $this->getLayout()->getBlock('topcart');
                    Mage::register('referrer_url', $this->_getRefererUrl());
                    //$sidebar = "Na";
                    $sidebar = $sidebar_block->toHtml();
                    $response['toplink'] = $toplink;
                    $response['sidebar'] = $sidebar;
                    //$response['other-items'] = json_decode($cart->_getCart());
                }
        } catch (Mage_Core_Exception $e) {
            $msg = "";
            if ($this->_getSession()->getUseNotice(true)) {
                $msg = $e->getMessage();
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $msg .= $message.'<br/>';
                }
            }

            $response['status'] = 'ERROR';
            $response['message'] = $msg;
        } catch (Exception $e) {
            $response['status'] = 'ERROR';
            $response['message'] = $this->__('Cannot add the item to shopping cart.');
            Mage::logException($e);
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    } else{
        return parent::updateAction();
    }
}
    
    public function optionsAction(){
        $productId = $this->getRequest()->getParam('product_id');
        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');
 
        $params = new Varien_Object();
        $params->setCategoryId(false);
        $params->setSpecifyOptions(false);
 
        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }
    
    protected function _getWishlist($wishlistId = null)
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }
        try {
            if (!$wishlistId) {
                $wishlistId = $this->getRequest()->getParam('wishlist_id');
            }
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $wishlist = Mage::getModel('wishlist/wishlist');
            
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
                $wishlist->loadByCustomer($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                $wishlist = null;
                Mage::throwException(
                    Mage::helper('wishlist')->__("Requested wishlist doesn't exist")
                );
            }
            
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
            Mage::helper('wishlist')->__('Cannot create wishlist.')
            );
            return false;
        }
 
        return $wishlist;
    }
    public function addwishAction()
    {
 
        $response = array();
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            $response['status'] = 'ERROR';
            $response['message'] = $this->__('Wishlist Has Been Disabled By Admin');
        }
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            $response['status'] = 'ERROR';
            $response['message'] = $this->__('Please Login First');
        }
 
        if(empty($response)){
            $session = Mage::getSingleton('customer/session');
            $wishlist = $this->_getWishlist();
            if (!$wishlist) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Unable to Create Wishlist');
            }else{
 
                $productId = (int) $this->getRequest()->getParam('product');
                if (!$productId) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Product Not Found');
                }else{
 
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if (!$product->getId() || !$product->isVisibleInCatalog()) {
                        $response['status'] = 'ERROR';
                        $response['message'] = $this->__('Cannot specify product.');
                    }else{
 
                        try {
                            $requestParams = $this->getRequest()->getParams();
                            if ($session->getBeforeWishlistRequest()) {
                                $requestParams = $session->getBeforeWishlistRequest();
                                $session->unsBeforeWishlistRequest();
                            }
                            $buyRequest = new Varien_Object($requestParams);
 
                            $result = $wishlist->addNewItem($product, $buyRequest);
                            if (is_string($result)) {
                                Mage::throwException($result);
                            }
                            $wishlist->save();
 
                            Mage::dispatchEvent(
                                'wishlist_add_product',
                            array(
                                'wishlist'  => $wishlist,
                                'product'   => $product,
                                'item'      => $result
                            )
                            );
 
                            
                            $referer = $session->getBeforeWishlistUrl();
                            if ($referer) {
                                $session->setBeforeWishlistUrl(null);
                            } else {
                                $referer = $this->_getRefererUrl();
                            }
                            $session->setAddActionReferer($referer);
                            
                            Mage::helper('wishlist')->calculate();
                            
                            $message = $this->__('%1$s has been added to your wishlist.',
                            $product->getName(), Mage::helper('core')->escapeUrl($referer));
                            
                            $response['status'] = 'SUCCESS';
                            $response['message'] = $message;
 
                            Mage::unregister('wishlist');
 
                            $this->loadLayout();
                            $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                            $sidebar_block = $this->getLayout()->getBlock('wishlist_sidebar');
                            $sidebar = $sidebar_block->toHtml();
                            $response['toplink'] = $toplink;
                            $response['sidebar'] = $sidebar;
                        }
                        catch (Mage_Core_Exception $e) {
                            $response['status'] = 'ERROR';
                            $response['message'] = $this->__('An error occurred while adding item to wishlist: %s', $e->getMessage());
                        }
                        catch (Exception $e) {
                            mage::log($e->getMessage());
                            $response['status'] = 'ERROR';
                            $response['message'] = $this->__('An error occurred while adding item to wishlist.');
                        }
                    }
                }
            }
 
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
    public function compareAction(){
        $response = array();
        
        $productId = (int) $this->getRequest()->getParam('product');
        
        if ($productId && (Mage::getSingleton('log/visitor')->getId() || Mage::getSingleton('customer/session')->isLoggedIn())) {
            $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
 
            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                $response['status'] = 'SUCCESS';
                $response['message'] = $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()));
                Mage::register('referrer_url', $this->_getRefererUrl());
                Mage::helper('catalog/product_compare')->calculate();
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
                $this->loadLayout();
                $sidebar_block = $this->getLayout()->getBlock('catalog.compare.sidebar');
                $sidebar_block->setTemplate('ajaxwishlist/catalog/product/compare/sidebar.phtml');
                $sidebar = $sidebar_block->toHtml();
                $response['sidebar'] = $sidebar;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
}
