<?php

class Algolia_Algoliasearch_Helper_Entity_Producthelper extends Algolia_Algoliasearch_Helper_Entity_Helper
{
    protected static $_productAttributes;

    protected static $_predefinedProductAttributes = array('name', 'url_key', 'description', 'image', 'small_image', 'thumbnail');

    protected function getIndexNameSuffix()
    {
        return '_products';
    }

    public function getAllAttributes($add_empty_row = false)
    {
        if (is_null(self::$_productAttributes))
        {
            self::$_productAttributes = array();

            /** @var $config Mage_Eav_Model_Config */
            $config = Mage::getSingleton('eav/config');

            $allAttributes = $config->getEntityAttributeCodes('catalog_product');

            $productAttributes = array_merge(array('name', 'path', 'categories', 'categories_without_path', 'description', 'ordered_qty', 'stock_qty', 'price', 'rating_summary', 'media_gallery'), $allAttributes);

            $excludedAttributes = array(
                'all_children', 'available_sort_by', 'children', 'children_count', 'custom_apply_to_products',
                'custom_design', 'custom_design_from', 'custom_design_to', 'custom_layout_update', 'custom_use_parent_settings',
                'default_sort_by', 'display_mode', 'filter_price_range', 'global_position', 'image', 'include_in_menu', 'is_active',
                'is_always_include_in_menu', 'is_anchor', 'landing_page', 'level', 'lower_cms_block',
                'page_layout', 'path_in_store', 'position', 'small_image', 'thumbnail', 'url_key', 'url_path',
                'visible_in_menu');

            $productAttributes = array_diff($productAttributes, $excludedAttributes);

            foreach ($productAttributes as $attributeCode)
                self::$_productAttributes[$attributeCode] = $config->getAttribute('catalog_product', $attributeCode)->getFrontendLabel();
        }

        $attributes = self::$_productAttributes;

        if ($add_empty_row === true)
            $attributes[''] = '';

        uksort($attributes, function ($a, $b) {
            return strcmp($a, $b);
        });

        return $attributes;
    }

    public function isAttributeEnabled($additionalAttributes, $attr_name)
    {
        foreach ($additionalAttributes as $attr)
            if ($attr['attribute'] === $attr_name)
                return true;

        return false;
    }

    public function getProductCollectionQuery($storeId, $productIds = null, $only_visible = true)
    {
        /** @var $products Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $products = Mage::getResourceModel('catalog/product_collection');

        $products = $products->setStoreId($storeId)
                        ->addStoreFilter($storeId);

        if ($only_visible)
            $products = $products->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds()));

        if (false === $this->config->getShowOutOfStock($storeId))
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);

        $products = $products->addFinalPrice()
                        ->addAttributeToSelect('special_from_date')
                        ->addAttributeToSelect('special_to_date')
                        ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        $additionalAttr = $this->config->getProductAdditionalAttributes($storeId);

        foreach ($additionalAttr as &$attr)
            $attr = $attr['attribute'];

        $products = $products->addAttributeToSelect(array_values(array_merge(static::$_predefinedProductAttributes, $additionalAttr)));

        if ($productIds && count($productIds) > 0)
            $products = $products->addAttributeToFilter('entity_id', array('in' => $productIds));

        Mage::dispatchEvent('algolia_rebuild_store_product_index_collection_load_before', array('store' => $storeId, 'collection' => $products));

        return $products;
    }

    public function setSettings($storeId)
    {
        $attributesToIndex          = array();
        $unretrievableAttributes    = array();
        $attributesForFaceting      = array();

        foreach ($this->config->getProductAdditionalAttributes($storeId) as $attribute)
        {
            if ($attribute['searchable'] == '1')
            {
                if ($attribute['order'] == 'ordered')
                    $attributesToIndex[] = $attribute['attribute'];
                else
                    $attributesToIndex[] = 'unordered('.$attribute['attribute'].')';
            }

            if ($attribute['retrievable'] != '1')
                $unretrievableAttributes[] = $attribute['attribute'];
        }

        $customRankings = $this->config->getProductCustomRanking($storeId);

        $customRankingsArr = array();

        $facets = $this->config->getFacets();

        foreach($facets as $facet)
        {
            if ($facet['attribute'] === 'price')
            {
                $facet['attribute'] = 'price.default';

                if ($this->config->isCustomerGroupsEnabled($storeId))
                {
                    foreach ($groups = Mage::getModel('customer/group')->getCollection() as $group)
                    {
                        $group_id = (int)$group->getData('customer_group_id');

                        $attributesForFaceting[] = 'price.group_' . $group_id;
                    }
                }
            }

            $attributesForFaceting[] = $facet['attribute'];
        }


        foreach ($customRankings as $ranking)
            $customRankingsArr[] =  $ranking['order'] . '(' . $ranking['attribute'] . ')';


        $indexSettings = array(
            'attributesToIndex'         => array_values(array_unique($attributesToIndex)),
            'customRanking'             => $customRankingsArr,
            'unretrievableAttributes'   => $unretrievableAttributes,
            'attributesForFaceting'     => $attributesForFaceting,
            'maxValuesPerFacet'         => (int) $this->config->getMaxValuesPerFacet($storeId)
        );

        // Additional index settings from event observer
        $transport = new Varien_Object($indexSettings);
        Mage::dispatchEvent('algolia_index_settings_prepare', array('store_id' => $storeId, 'index_settings' => $transport));
        $indexSettings = $transport->getData();

        $mergeSettings = $this->algolia_helper->mergeSettings($this->getIndexName($storeId), $indexSettings);

        $this->algolia_helper->setSettings($this->getIndexName($storeId), $mergeSettings);

        /**
         * Handle Slaves
         */
        $sorting_indices = $this->config->getSortingIndices();

        if (count($sorting_indices) > 0)
        {
            $slaves = array();

            foreach ($sorting_indices as $values)
            {
                if ($this->config->isCustomerGroupsEnabled($storeId))
                {
                    if ($values['attribute'] === 'price')
                    {
                        foreach ($groups = Mage::getModel('customer/group')->getCollection() as $group)
                        {
                            $group_id = (int)$group->getData('customer_group_id');

                            $suffix_index_name = 'group_' . $group_id;

                            $slaves[] = $this->getIndexName($storeId) . '_' .$values['attribute'].'_' . $suffix_index_name . '_' . $values['sort'];
                        }
                    }
                }
                else
                {
                    if ($values['attribute'] === 'price')
                        $slaves[] = $this->getIndexName($storeId) . '_' .$values['attribute']. '_default_' . $values['sort'];
                    else
                        $slaves[] = $this->getIndexName($storeId) . '_' .$values['attribute']. '_' . $values['sort'];
                }
            }

            $this->algolia_helper->setSettings($this->getIndexName($storeId), array('slaves' => $slaves));

            foreach ($sorting_indices as $values)
            {
                if ($this->config->isCustomerGroupsEnabled($storeId))
                {
                    if (strpos($values['attribute'], 'price') !== false)
                    {
                        foreach ($groups = Mage::getModel('customer/group')->getCollection() as $group)
                        {
                            $group_id = (int)$group->getData('customer_group_id');

                            $suffix_index_name = 'group_' . $group_id;

                            $sort_attribute = strpos($values['attribute'], 'price') !== false ? $values['attribute'].'.'.$suffix_index_name : $values['attribute'];

                            $mergeSettings['ranking'] = array($values['sort'].'('.$sort_attribute.')', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom');

                            $this->algolia_helper->setSettings($this->getIndexName($storeId).'_'.$values['attribute'].'_'. $suffix_index_name .'_'.$values['sort'], $mergeSettings);
                        }
                    }
                }
                else
                {
                    $sort_attribute = strpos($values['attribute'], 'price') !== false ? $values['attribute'].'.'.'default' : $values['attribute'];

                    $mergeSettings['ranking'] = array($values['sort'].'('.$sort_attribute.')', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom');

                    if ($values['attribute'] === 'price')
                        $this->algolia_helper->setSettings($this->getIndexName($storeId) . '_' .$values['attribute']. '_default_' . $values['sort'], $mergeSettings);
                    else
                        $this->algolia_helper->setSettings($this->getIndexName($storeId) . '_' .$values['attribute']. '_' . $values['sort'], $mergeSettings);

                }
            }
        }
    }

    private function getFields($store)
    {
        $tax_helper = Mage::helper('tax');

        if ($tax_helper->getPriceDisplayType($store) == Mage_Tax_Model_Config::DISPLAY_TYPE_EXCLUDING_TAX)
            return array('price' => false);

        if ($tax_helper->getPriceDisplayType($store) == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX)
            return array('price' => true);

        return array('price' => false, 'price_with_tax' => true);
    }

    private function handlePrice(&$product, $sub_products, &$customData)
    {
        $fields                     = $this->getFields($product->getStore());
        $customer_groups_enabled    = $this->config->isCustomerGroupsEnabled($product->getStoreId());
        $store                      = $product->getStore();
        $type                       = $this->config->getMappedProductType($product->getTypeId());

        $groups                     = array();

        if ($customer_groups_enabled)
            $groups = Mage::getModel('customer/group')->getCollection();

        foreach ($fields as $field => $with_tax)
        {
            $price = (double) Mage::helper('tax')->getPrice($product, $product->getPrice(), $with_tax, null, null, null, $product->getStore(), null);

            $customData[$field] = array();

            $customData[$field]['default'] = $price;
            $customData[$field]['default_formated'] = $product->getStore()->formatPrice($price, false);


            if ($customer_groups_enabled) // If fetch special price for groups
            {
                foreach ($groups as $group)
                {
                    $group_id = (int)$group->getData('customer_group_id');
                    $product->setCustomerGroupId($group_id);

                    $discounted_price = $product->getPriceModel()->getFinalPrice(1, $product);

                    if ($discounted_price !== false)
                    {
                        $customData[$field]['group_' . $group_id] = (double) Mage::helper('tax')->getPrice($product, $discounted_price, $with_tax, null, null, null, $product->getStore(), null);
                        $customData[$field]['group_' . $group_id . '_formated'] = $store->formatPrice($customData[$field]['group_' . $group_id], false);
                    }
                    else
                    {
                        $customData[$field]['group_' . $group_id] = $customData[$field]['default'];
                        $customData[$field]['group_' . $group_id . '_formated'] = $customData[$field]['default_formated'];
                    }
                }

                $product->setCustomerGroupId(null);
            }

            $special_price = (double) Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $with_tax, null, null, null, $product->getStore(), null);

            if ($special_price && $special_price !== $customData[$field]['default'])
            {
                $customData[$field]['special_from_date'] = strtotime($product->getSpecialFromDate());
                $customData[$field]['special_to_date'] = strtotime($product->getSpecialToDate());

                $customData[$field]['default_original_formated'] = $customData[$field]['default_formated'];

                $customData[$field]['default'] = $special_price;
                $customData[$field]['default_formated'] = $product->getStore()->formatPrice($special_price, false);
            }

            if ($type == 'configurable' || $type == 'grouped' || $type == 'bundle')
            {
                $min = PHP_INT_MAX;
                $max = 0;

                if ($type == 'bundle')
                {
                    $_priceModel = $product->getPriceModel();

                    list($min, $max) = $_priceModel->getTotalPrices($product, null, $with_tax, true);
                }

                if ($type == 'grouped' || $type == 'configurable')
                {
                    if (count($sub_products) > 0)
                    {
                        foreach ($sub_products as $sub_product)
                        {
                            $price = (double) Mage::helper('tax')->getPrice($product, $sub_product->getFinalPrice(), $with_tax, null, null, null, $product->getStore(), null);

                            $min = min($min, $price);
                            $max = max($max, $price);
                        }
                    }
                    else
                        $min = $max; // avoid to have PHP_INT_MAX in case of no subproducts (Corner case of visibility and stock options)
                }

                if ($min != $max)
                {
                    $dashed_format = $product->getStore()->formatPrice($min, false) . ' - ' . $product->getStore()->formatPrice($max, false);
                    $customData[$field]['default_formated'] = $dashed_format;

                    if ($customer_groups_enabled)
                    {
                        foreach ($groups as $group)
                        {
                            $group_id = (int)$group->getData('customer_group_id');

                            $customData[$field]['group_' . $group_id] = 0;
                            $customData[$field]['group_' . $group_id . '_formated'] = $dashed_format;
                        }
                    }

                    //// Do not keep special price that is already taken into account in min max
                    unset($customData['price']['special_from_date']);
                    unset($customData['price']['special_to_date']);
                    unset($customData['price']['default_original_formated']);

                    $customData[$field]['default'] = 0; // will be reset just after
                }

                if ($customData[$field]['default'] == 0)
                {
                    $customData[$field]['default'] = $min;

                    if ($min === $max)
                        $customData[$field]['default_formated'] = $product->getStore()->formatPrice($min, false);

                    if ($customer_groups_enabled)
                    {
                        foreach ($groups as $group)
                        {
                            $group_id = (int)$group->getData('customer_group_id');
                            $customData[$field]['group_' . $group_id] = $min;

                            if ($min === $max)
                                $customData[$field]['group_' . $group_id . '_formated'] = $customData[$field]['default_formated'];
                        }
                    }
                }
            }
        }
    }

    public function getObject(Mage_Catalog_Model_Product $product)
    {
        $type = $this->config->getMappedProductType($product->getTypeId());
        $this->logger->start('CREATE RECORD '.$product->getId(). ' '.$this->logger->getStoreName($product->storeId));
        $this->logger->log('Product type ('.$product->getTypeId().', mapped to: ' . $type . ')');
        $defaultData    = array();

        $transport      = new Varien_Object($defaultData);

        Mage::dispatchEvent('algolia_product_index_before', array('product' => $product, 'custom_data' => $transport));

        $defaultData    = $transport->getData();

        $defaultData    = is_array($defaultData) ? $defaultData : explode("|",$defaultData);

        $customData = array(
            'objectID'          => $product->getId(),
            'name'              => $product->getName(),
            'url'               => $product->getProductUrl()
        );

        $additionalAttributes = $this->config->getProductAdditionalAttributes($product->getStoreId());
        $groups = null;

        if ($this->isAttributeEnabled($additionalAttributes, 'description'))
            $customData['description'] = $product->getDescription();

        $categories             = array();
        $categories_with_path   = array();

        $_categoryIds = $product->getCategoryIds();

        if (is_array($_categoryIds) && count($_categoryIds) > 0)
        {
            $categoryCollection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', $_categoryIds)
                ->addFieldToFilter('level', array('gt' => 1))
                ->addIsActiveFilter();

            foreach ($categoryCollection as $category)
            {
                $categoryName = $category->getName();

                if ($categoryName)
                    $categories[] = $categoryName;

                $category->getUrlInstance()->setStore($product->getStoreId());
                $path = array();

                foreach ($category->getPathIds() as $treeCategoryId)
                {
                    $name = $this->getCategoryName($treeCategoryId, $product->getStoreId());
                    if ($name)
                        $path[] = $name;
                }

                $categories_with_path[] = $path;
            }
        }

        foreach ($categories_with_path as $result)
        {
            for ($i = count($result) - 1; $i > 0; $i--)
            {
                $categories_with_path[] = array_slice($result, 0, $i);
            }
        }

        $categories_with_path = array_intersect_key($categories_with_path, array_unique(array_map('serialize', $categories_with_path)));

        $categories_hierarchical = array();

        $level_name = 'level';

        foreach ($categories_with_path as $category)
        {
            for ($i = 0; $i < count($category); $i++)
            {
                if (isset($categories_hierarchical[$level_name.$i]) === false)
                    $categories_hierarchical[$level_name.$i] = array();

                $categories_hierarchical[$level_name.$i][] = implode(' /// ', array_slice($category, 0, $i + 1));
            }
        }

        foreach ($categories_hierarchical as &$level)
        {
            $level = array_values(array_unique($level));
        }

        foreach ($categories_with_path as &$category)
            $category = implode(' /// ',$category);

        $customData['categories'] = $categories_hierarchical;

        $customData['categories_without_path'] = $categories;

        if (false === isset($defaultData['thumbnail_url']))
        {
            $thumb = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(75, 75);

            try
            {
                $customData['thumbnail_url'] = $thumb->toString();
                $customData['thumbnail_url'] = str_replace(array('https://', 'http://'
                ), '//', $customData['thumbnail_url']);
            }
            catch (\Exception $e)
            {
                $this->logger->log($e->getMessage());
                $this->logger->log($e->getTraceAsString());

                $customData['thumbnail_url'] = str_replace(array('https://', 'http://'), '//', Mage::getDesign()->getSkinUrl($thumb->getPlaceholder()));
            }
        }

        if (false === isset($defaultData['image_url']))
        {
            $image = Mage::helper('catalog/image')->init($product, $this->config->getImageType())->resize($this->config->getImageWidth(), $this->config->getImageHeight());

            try
            {
                $customData['image_url'] = $image->toString();
                $customData['image_url'] = str_replace(array('https://', 'http://'), '//', $customData['image_url']);
            }
            catch (\Exception $e)
            {
                $this->logger->log($e->getMessage());
                $this->logger->log($e->getTraceAsString());

                $customData['image_url'] = str_replace(array('https://', 'http://'), '//', Mage::getDesign()->getSkinUrl($image->getPlaceholder()));
            }

            if ($this->isAttributeEnabled($additionalAttributes, 'media_gallery'))
            {
                $product->load('media_gallery');

                $customData['media_gallery'] = array();

                foreach ($product->getMediaGalleryImages() as $image)
                    $customData['media_gallery'][] = str_replace(array('https://', 'http://'), '//', $image->getUrl());
            }
        }

        $sub_products = null;
        $ids = null;

        if ($type == 'configurable' || $type == 'grouped' || $type == 'bundle')
        {
            if ($type == 'bundle')
            {
                $ids = array();

                $selection = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);

                foreach ($selection as $option)
                    $ids[] = $option->product_id;
            }

            if ($type == 'configurable' || $type == 'grouped')
                $ids = $product->getTypeInstance(true)->getChildrenIds($product->getId());

            if (count($ids))
            {
                $sub_products = $this->getProductCollectionQuery($product->getStoreId(), $ids, false)->load();
            }
            else
            {
                $sub_products = array();
            }
        }

        if (false === isset($defaultData['in_stock']))
        {
            $stockItem = $product->getStockItem();

            $customData['in_stock'] = (int) $stockItem->getIsInStock();
        }

        // skip default calculation if we have provided these attributes via the observer in $defaultData
        if (false === isset($defaultData['ordered_qty']) && $this->isAttributeEnabled($additionalAttributes, 'ordered_qty'))
            $customData['ordered_qty']   = (int) $product->getOrderedQty();

        if (false === isset($defaultData['stock_qty']) && $this->isAttributeEnabled($additionalAttributes, 'stock_qty'))
            $customData['stock_qty'] = (int) $product->getStockQty();

        if (Mage::helper('core')->isModuleEnabled('Mage_Review'))
            if ($this->isAttributeEnabled($additionalAttributes, 'rating_summary'))
                    $customData['rating_summary'] = (int) $product->getRatingSummary();

        foreach ($additionalAttributes as $attribute)
        {
            if (isset($customData[$attribute['attribute']]))
                continue;

            $value = $product->getData($attribute['attribute']);

            $attribute_ressource = $product->getResource()->getAttribute($attribute['attribute']);

            if ($attribute_ressource)
            {
                $attribute_ressource = $attribute_ressource->setStoreId($product->getStoreId());

                if ($value === null)
                {
                    /** Get values as array in children */
                    if ($type == 'configurable' || $type == 'grouped' || $type == 'bundle')
                    {
                        $values = array();

                        foreach ($sub_products as $sub_product)
                        {
                            $stock = (int) $sub_product->getStockItem()->getIsInStock();

                            if ($stock == false)
                                continue;

                            $value = $sub_product->getData($attribute['attribute']);

                            if ($value)
                            {
                                $value_text = $sub_product->getAttributeText($attribute['attribute']);

                                if ($value_text)
                                    $values[] = $value_text;
                                else
                                    $values[] = $attribute_ressource->getFrontend()->getValue($sub_product);
                            }
                        }

                        if (is_array($values) && count($values) > 0)
                        {
                            $customData[$attribute['attribute']] = array_values(array_unique($values));
                        }
                    }
                }
                else
                {
                    $value_text = $product->getAttributeText($attribute['attribute']);

                    if ($value_text)
                        $value = $value_text;
                    else
                    {
                        $attribute_ressource = $attribute_ressource->setStoreId($product->getStoreId());
                        $value = $attribute_ressource->getFrontend()->getValue($product);
                    }

                    if ($value)
                    {
                        $customData[$attribute['attribute']] = $value;
                    }
                }
            }
        }

        $this->handlePrice($product, $sub_products, $customData);

        $transport = new Varien_Object($customData);
        Mage::dispatchEvent('algolia_subproducts_index', array('custom_data' => $transport, 'sub_products' => $sub_products));
        $customData = $transport->getData();

        $customData = array_merge($customData, $defaultData);

        $customData['type_id'] = $type;

        $this->castProductObject($customData);

        $this->logger->stop('CREATE RECORD '.$product->getId(). ' '.$this->logger->getStoreName($product->storeId));

        return $customData;
    }
}
