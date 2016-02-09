<?php

class Magestore_Webpos_Block_Listproduct extends Mage_Core_Block_Template {

    private $_productCollection, $_storeId, $_numberProductPerpage, $_currentPage, $_categoryId;
    private $_coreSession;

    public function __construct() {
        $this->_coreSession = Mage::getModel('core/session');
        $this->_categoryId = null;
        $this->_numberProductPerpage = 16;
        $this->_currentPage = 1;
        $pageNumber = $this->_coreSession->getData('pos_cpage');
        if (isset($pageNumber))
            $this->_currentPage = $pageNumber;
        else
            $this->_coreSession->setData('pos_cpage', 1);
        $this->_storeId = Mage::app()->getStore()->getStoreId();
        $this->_productCollection = Mage::getModel('catalog/product')->getCollection();
        $this->_productCollection->addAttributeToSelect('*')
                ->setStoreId($this->_storeId)
                ->addStoreFilter($this->_storeId)
                ->addAttributeToFilter("status", 1);
        $this->_productCollection->setCurPage($this->_currentPage)->setPageSize($this->_numberProductPerpage);
    }

    public function setProductCollection($collection) {
        $this->_productCollection = $collection;
    }

    protected function getProductIdByKeyword($keyword, $storeId) {
        /*
          $arrkeyword = explode(" ", $keyword);

          $read 	= Mage::getSingleton('core/resource')->getConnection('core_read');
          $table = $coreTable = Mage::getModel('core/resource')->getTableName('catalogsearch/fulltext');
          $query = "SELECT * FROM $table where store_id=$storeId";
          $condition = ' AND ';
          $i = 0;
          foreach($arrkeyword as $keyword) {
          if(!$i)
          $condition .= "(data_index like '%$keyword%'";
          else
          $condition .= "OR data_index like '%$keyword%'";
          $i++;
          }
          $condition .= ')';
          $query .= $condition;
          //            $query = "SELECT * FROM $table where store_id=$storeId AND data_index like '%$keyword%'";
          $data = $read->fetchAll($query);
          $productIds = array();
          foreach($data as $item) {
          $productIds[] = $item['product_id'];
          }

          return $productIds;
         */
        $productId = Mage::helper('webpos/product')->getProductIdByInventoryBarcode($keyword);
        if ($productId == 0)
            return array();
        return $productId;
    }

    public function getProductCollection($categoryId = null, $storeId = null, $pageNumber = null, $numberProductPerpage = null) {
        if (!isset($storeId))
            $storeId = $this->_storeId;
        if (!isset($pageNumber))
            $pageNumber = $this->_currentPage;
        if (!isset($numberProductPerpage))
            $numberProductPerpage = $this->_numberProductPerpage;


        $keyword = $this->getRequest()->getParam('keyword');
        $keyword = strtolower($keyword);

        $currentCategoryId = $this->_categoryId;
        $cpage = $this->_coreSession->getData('pos_cpage');

        if (isset($cpage))
            $pageNumber = $cpage;
        else
            $this->_coreSession->setData('pos_cpage', 1);
        $specialCate = $this->_coreSession->getData('special_category');

        if ($specialCate == 'recent') {
            $productIds = array();
            $userId = Mage::helper('webpos/permission')->getCurrentUser();
            $recentCollection = Mage::getModel('webpos/products')
                    ->getCollection()
                    ->addFieldToFilter('user_id', $userId)

            ;
            $numberProduct = $recentCollection->getSize();

            //fix product list missing position when product disable
//                $recentCollection
//                    ->setPageSize($numberProductPerpage)
//                    ->setCurPage($pageNumber);
            $recentCollection->setOrder('last_added', 'DESC');
            foreach ($recentCollection as $recent) {
                $productIds[] = $recent->getProductId();
            }
            $this->_productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->setStoreId($storeId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter("status", 1)
            ;

            $orderString = array('CASE e.entity_id');
            foreach ($productIds as $i => $productId) {
                $orderString[] = 'WHEN ' . $productId . ' THEN ' . $i;
            }
            $orderString[] = 'END';
            $orderString = implode(' ', $orderString);
            $this->_productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
            $this->_productCollection->getSelect()
                    ->order(new Zend_Db_Expr($orderString));

            $this->_coreSession->setData('numberProduct', $numberProduct);
            $this->_coreSession->setData('pos_cpage', $pageNumber);
            $offlineConfig = Mage::helper('webpos')->getOfflineConfig();


            $useLocalSearch = $offlineConfig['search_offline'];

            if ($useLocalSearch == true) {
                $numberProductPerpage = 12;
                if ($this->_productCollection->getSize() > 12)
                    $this->_coreSession->setData('numberProduct', 12);
                else
                    $this->_coreSession->setData('numberProduct', $this->_productCollection->getSize());
            }
            Mage::dispatchEvent('webpos_block_listproduct_event', array('pos_get_product_colection' => $this->_productCollection));
            $this->_productCollection->setCurPage($pageNumber)->setPageSize($numberProductPerpage);


            return $this->_productCollection;
        } elseif ($specialCate == 'popular') {
            $productIds = array();
            $popularCollection = Mage::getModel('webpos/products')
                            ->getCollection()->addFieldToFilter('user_id', 0);
            //fix product list missing position when product disable
//                $popularCollection
//                    ->setCurPage($pageNumber)
//                    ->setPageSize($numberProductPerpage);

            $numberProduct = $popularCollection->getSize();
            $popularCollection->setOrder('popularity', 'DESC');

            foreach ($popularCollection as $popular) {
                $productIds[] = $popular->getProductId();
            }
            $this->_productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->setStoreId($storeId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter("status", 1)

            ;
            $orderString = array('CASE e.entity_id');
            foreach ($productIds as $i => $productId) {
                $orderString[] = 'WHEN ' . $productId . ' THEN ' . $i;
            }
            $orderString[] = 'END';
            $orderString = implode(' ', $orderString);
            $this->_productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
            $this->_productCollection->getSelect()
                    ->order(new Zend_Db_Expr($orderString));
            $this->_coreSession->setData('numberProduct', $numberProduct);
            $this->_coreSession->setData('pos_cpage', $pageNumber);
            $offlineConfig = Mage::helper('webpos')->getOfflineConfig();
            $useLocalSearch = $offlineConfig['search_offline'];

            if ($useLocalSearch == true) {
                $numberProductPerpage = 12;
                if ($this->_productCollection->getSize() > 12)
                    $this->_coreSession->setData('numberProduct', 12);
                else
                    $this->_coreSession->setData('numberProduct', $this->_productCollection->getSize());
            }
            Mage::dispatchEvent('webpos_block_listproduct_event', array('pos_get_product_colection' => $this->_productCollection));
            $this->_productCollection->setCurPage($pageNumber)->setPageSize($numberProductPerpage);

            return $this->_productCollection;
        }
        $cCategory = Mage::getSingleton('core/session')->getData('pos_ccategory');
        if (isset($cCategory)) {
            $categoryId = Mage::getSingleton('core/session')->getData('pos_ccategory');
            //Mage::getSingleton('core/session')->setData('pos_ccategory',null);
        }
        $showOutofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock', $storeId);
        if ($keyword) {
            $sql = array();
            $strAttributes = Mage::helper('webpos')->getProductAttributeForSearch();
            $attributes = explode(",", $strAttributes);
            $attributes = $attributes ? $attributes : 'name,sku';   // search by name, sku by default


            if (is_array($attributes) && count($attributes)) {
                foreach ($attributes as $attribute) {
                    $sql[] = array('attribute' => $attribute, 'like' => '%' . $keyword . '%');
                }
            }

            $productIds = $this->getProductIdByKeyword($keyword, $storeId);
            $this->_productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->setStoreId($storeId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter("status", 1)
            ;
            if (isset($categoryId)) {
                //$category = Mage::getModel('catalog/category')->load($categoryId);
                //$this->_productCollection->addCategoryFilter($category);
            }
            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_productCollection);

            //Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($this->_productCollection);
            /*
            $this->_productCollection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
            */
            /* filter product is in stock */
            if (!$showOutofstock)
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_productCollection);
            /* end */


            if ($sql) {
                $this->_productCollection->addAttributeToFilter($sql);
                if (!$this->_productCollection->getSize()) {

                    $this->_productCollection = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect('*')
                            ->setStoreId($storeId)
                            ->addStoreFilter($storeId)
                            ->addAttributeToFilter("status", 1)
                    ;
                    if (isset($categoryId)) {
                        //$category = Mage::getModel('catalog/category')->load($categoryId);
                        //$this->_productCollection->addCategoryFilter($category);
                    }
                    Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_productCollection);

                    //Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($this->_productCollection);
                    /*
                    $this->_productCollection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
                    */
                    /* filter product is in stock */
                    if (!$showOutofstock)
                        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_productCollection);
                    /* end */
                    $this->_productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
                }
            }else {
                $this->_productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
            }
//                        $this->_productCollection->addAttributeToFilter('entity_id', array('in'=>$productIds));
            Mage::dispatchEvent('webpos_block_listproduct_event', array('pos_get_product_colection' => $this->_productCollection));
            if(count($this->_productCollection) != 1){
				 $this->_productCollection->addFieldToFilter('visibility',array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
			}
			
			$this->_coreSession->setData('numberProduct', $this->_productCollection->getSize());
            $this->_coreSession->setData('pos_cpage', $pageNumber);
            $this->_productCollection->setCurPage($pageNumber)->setPageSize($numberProductPerpage);
            return $this->_productCollection;
        }
        $this->_productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('status', 1);
        $simpleCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('product_id')->addAttributeToSelect('category_id')->addAttributeToFilter('status', 1);

        if (isset($categoryId) && $categoryId != 0) {
            $this->_productCollection->getSelect()->joinLeft(array("t1" => $this->_productCollection->getTable('catalog/category_product')), "t1.product_id = e.entity_id", array("*"));
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $categories = $category->getAllChildren(true);
            $categories = implode(',', $categories);
            $this->_productCollection->getSelect()->where('t1.category_id IN(' . $categories . ')')->group('t1.product_id');

            $simpleCollection->getSelect()->joinLeft(array("t1" => $simpleCollection->getTable('catalog/category_product')), "t1.product_id = e.entity_id", array("*"));
            $simpleCollection->getSelect()->where('t1.category_id IN(' . $categories . ')')->group('t1.product_id');
        }
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($simpleCollection);
        $simpleCollection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        if (!$showOutofstock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($simpleCollection);
        Mage::dispatchEvent('webpos_block_listproduct_event', array('pos_get_product_colection' => $simpleCollection));

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_productCollection);
        $this->_productCollection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        if (!$showOutofstock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_productCollection);
        Mage::dispatchEvent('webpos_block_listproduct_event', array('pos_get_product_colection' => $this->_productCollection));

        Mage::getSingleton('core/session')->setData('numberProduct', $simpleCollection->count());
        Mage::getSingleton('core/session')->setData('pos_cpage', $pageNumber);
        $this->_productCollection->setCurPage($pageNumber)->setPageSize($numberProductPerpage);
        return $this->_productCollection;
    }

    public function getCurrentPage() {
        return $this->_currentPage;
    }

    public function setCurrentPage($page) {
        $this->_currentPage = $page;
    }

    public function getNumberProductPerpage() {
        return $this->_numberProductPerpage;
    }

    public function setNumberProductPerpage($numberproduct) {
        $this->_numberProductPerpage = $numberproduct;
    }

    public function getCurrentCategory() {
        return $this->_categoryId;
    }

    public function setCurrentCategory($categoryId) {
        $this->_coreSession->setData('', $categoryId);
        $this->_categoryId = $categoryId;
    }

    public function getProductsInCart() {
        $storeId = Mage::app()->getStore()->getStoreId();
        $showOutofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock', $storeId);
        $cartitems = Mage::getModel('checkout/session')->getQuote()->getAllItems();
        $productIdsInCart = array();
        if (count($cartitems) > 0)
            foreach ($cartitems as $item) {
                $prdIdIncart = $item->getProduct()->getId();
                $productIdsInCart[] = $prdIdIncart;
            }
        $productsInCart = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('entity_id', array('in' => $productIdsInCart));
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($productsInCart);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productsInCart);
        if (!$showOutofstock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productsInCart);
        return $productsInCart;
    }

}
