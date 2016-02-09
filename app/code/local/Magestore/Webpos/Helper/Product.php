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
 * Webpos Helper
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Helper_Product extends Mage_Core_Helper_Abstract {

    protected $_current_currency;
    protected $_store_id;
    protected $_productInWarehouseSesion; //Magnus
    protected $_isModuleInventoryEnabled = false; //Magnus

    public function __construct() {
        $this->_current_currency = Mage::app()->getRequest()->getParam('current_currency');
        $this->_store_id = Mage::app()->getStore()->getId();
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) { //Magnus
            $this->_isModuleInventoryEnabled = true;
            $warehouseId = Mage::getSingleton('core/session')->getCurrentWarehouseId();
            $this->getProductsByWarehouseSession($warehouseId);
        }
    }

    protected function getProductsByWarehouseSession($warehouseId) { //Magnus
        $show_outofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock');
        $coreResource = Mage::getSingleton('core/resource');
        $readConnection = $coreResource->getConnection('core_read');
        $sql = "SELECT `main_table`.`product_id` FROM " . $coreResource->getTableName('erp_inventory_warehouse_product') . " AS `main_table` WHERE (warehouse_id = '" . $warehouseId . "')";
        if (!$show_outofstock) {
            $sql .= " AND (available_qty > 0)";
        }
        $productIds = $readConnection->fetchAll($sql);
        $wProducts = array();
        foreach ($productIds as $productId) {
            $wProducts[] = $productId['product_id'];
        }
        $this->_productInWarehouseSesion = $wProducts;
        return;
    }

    public function getProductHtml(Mage_Catalog_Model_Product $product, $extraClass = '') {
        $productName = $product->getName();
        $productName = htmlspecialchars($productName);
        $productShortName = (strlen($productName) > 100) ? substr($productName, 0, 100) . '...' : $productName;
        $productId = $product->getId();
        $productType = $product->getTypeId();
        $productSku = $product->getSku();

        $productData = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qtyinc = 1;
        if ($productData->getQtyIncrements())
            $qtyinc = $productData->getQtyIncrements();
        $priceAndTaxData = $this->getProductPriceAndTaxData($product);
        $finalPrice = $priceAndTaxData['finalPrice'];
        $tax_amount = $priceAndTaxData['tax_amount'];
        $price = $priceAndTaxData['price'];
        $priceInclTax = $priceAndTaxData['priceInclTax'];
        $tax = $priceAndTaxData['tax'];
        $includeTax = $priceAndTaxData['includeTax'];
        $productPrice = $priceAndTaxData['productPrice'];
        if ($includeTax == true) {
            //$tax = 0;
            //$finalPrice = $price;
        }

        $storeId = $this->_store_id;
        $store = Mage::getModel('core/store')->load($storeId);
        $imgPath = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(120);
        $productOptions = $options = $customOptions = $groupedChilds = $bundleChilds = $storeCreditOptions = array();
        $priceConditions = '';
        $productOptions = $this->getProductOptions($product);
        $hasOption = $this->isProductHasOptions($product);
        $hasOptionString = ($hasOption == true) ? 'hasOption' : '0';
        if (isset($productOptions['bundle']) && count($productOptions['bundle']) > 0) {
            $bundleChilds = $productOptions['bundle'];
            $isBundle = true;
            $totalDefault = 0;
            foreach ($bundleChilds as $childId => $childData) {
                $items = $childData['items'];
                if (count($items) > 0)
                    foreach ($items as $itemId => $itemData) {
                        //if ($itemData['is_default'] == '1') {
                        $totalDefault += $itemData['price'] * $itemData['qty'];
                        //}
                    }
            }
        }
        $priceDisplay = ($isBundle == true) ? $store->formatPrice($totalDefault) : $store->formatPrice($finalPrice);
        $html = "<div qtyinc='" . $qtyinc . "' tax_amount='" . $tax_amount . "' class='product " . $extraClass . " col-lg-3 col-md-3 col-sm-3 col-xs-3' id='prd_" . $productId . "' prd_type='" . $product->getTypeID() . "' hasoption='" . $hasOptionString . "'>
					<div class='item' hasoption=" . $hasOptionString . " prdid='" . $productId . "' onclick=\"addProduct('" . $productId . "', '" . $finalPrice . "', '" . $imgPath . "')\">
						<div class='img-product'>
							<div class='prd_sku'>" . $productSku . "</div>
							<div class='prd_price_bg'></div>
							<div class='prd_price'>" . $priceDisplay . "</div>
							<img class='product_image' src='' imgpath='" . $imgPath . "'/>
						</div>
						<h2 id='prd_name_" . $productId . "'>" . $productShortName . "</h2>
						<p id='prd_fullname_" . $productId . "' class='hide'>" . $productName . "</p>
					</div>";
        if (
                (isset($productOptions['configurable']) && count($productOptions['configurable']) > 0) ||
                (isset($productOptions['custom_options']) && count($productOptions['custom_options']) > 0) ||
                (isset($productOptions['grouped']) && count($productOptions['grouped']) > 0) ||
                (isset($productOptions['bundle']) && count($productOptions['bundle']) > 0) ||
                (isset($productOptions['customercredit']) && count($productOptions['customercredit']) > 0)
        ):
            if (isset($productOptions['configurable']) && count($productOptions['configurable']) > 0) {
                $options = $productOptions['configurable'];
                if (isset($options['price_condition'])) {
                    $priceConditions = $options['price_condition'];
                }
            }
            if (isset($productOptions['custom_options']) && count($productOptions['custom_options']) > 0)
                $customOptions = $productOptions['custom_options'];
            if (isset($productOptions['grouped']) && count($productOptions['grouped']) > 0) {
                $groupedChilds = $productOptions['grouped'];
                $isGrouped = true;
            }
            if (isset($productOptions['bundle']) && count($productOptions['bundle']) > 0) {
                $bundleChilds = $productOptions['bundle'];
                $isBundle = true;
            }
            if (isset($productOptions['customercredit']) && count($productOptions['customercredit']) > 0) {
                $storeCreditOptions = $productOptions['customercredit'];
            }
            $totalDefault = $store->formatPrice($finalPrice);
            $options_label = 'Select Options';
            $option_value_label = "Price <span finalprice='" . $finalPrice . "' id='totals_price_" . $productId . "' class='bundle_price'>" . $totalDefault . "<span>";
            if (isset($isGrouped) && $isGrouped == true) {
                $options_label = 'Select Child';
                $option_value_label = 'Qty';
            }
            if (isset($isBundle) && $isBundle == true) {
                $totalDefault = 0;
                foreach ($bundleChilds as $childId => $childData) {
                    $items = $childData['items'];
                    if (count($items) > 0)
                        foreach ($items as $itemId => $itemData) {
                            if ($itemData['is_default'] == '1') {
                                $totalDefault += $itemData['price'] * $itemData['qty'];
                            }
                        }
                }
                $totalDefault = $store->formatPrice($totalDefault);
                $options_label = 'Select childs';
                $option_value_label = "Price <span finalprice='" . $finalPrice . "' id='totals_price_" . $productId . "' class='bundle_price'>" . $totalDefault . "<span>";
            }
            $html .= "<div class='product_options hide' id='" . $productId . "_options_popup' price_condition='" . $priceConditions . "'>
						<ul>
							<div class='product-name col-lg-12 col-md-12 col-sm-12 col-xs-12' style='padding:0'>
								<h2>" . $productShortName . "<button type='button' class='btn btn-warning' onclick=\"addProductWidthOptions('" . $productId . "','" . $product->getFinalPrice() . "','" . Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(120) . "')\">" . $this->__('Add to Cart') . "</button></h2>
							</div>									  
							<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12' style='padding:0'>
								<div class='col col-lg-6 col-md-6 col-sm-6 col-xs-6'>
									<div class='pospanel panel-default'>
										<div class='panel-heading'>" . $this->__($options_label) . "</div>
											<div class='options'>
												<ul>";
            $countOption = 1;
            if (count($options) > 0) {
                foreach ($options as $optionCode => $optionValues):
                    if ($optionCode == 'price_condition')
                        continue;
                    $optionActive = ($countOption == 1) ? 'option_active' : '';
                    $html .= "<li optiontype='configurable' id='" . $productId . "_" . $optionCode . "' onclick=\"showProductOptions('" . $productId . "','" . $optionCode . "')\"  class='" . $productId . "_options_label " . $optionActive . "'>
								<span>" . $optionValues['optionLabel'] . "<span class='required'>*</span></span><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
							</li>";
                    $countOption++;
                endforeach;
            }
            if (count($customOptions) > 0) {
                foreach ($customOptions as $option):
                    if ($option['price'] > 0) {
                        if ($option['price_type'] == 'fixed')
                            $priceFormated = ' +' . $store->formatPrice($option['price']);
                        else
                            $priceFormated = ' +' . $store->formatPrice($option['price'] * $product->getFinalPrice() / 100);
                    } else
                        $priceFormated = '';
                    $optionActive = ($countOption == 1) ? 'option_active' : '';
                    $optionTitle = ($option['is_require'] == '1') ? $option['title'] . "<span class='required'>*</span>" : $option['title'];
                    $html .= "<li optiontype='custom' id='" . $productId . "_co_" . $option['option_id'] . "' onclick=\"showProductOptions('" . $productId . "', '" . $option['option_id'] . "')\" value='" . $productId . "_co_" . $option['option_id'] . "' class='" . $productId . "_options_label " . $optionActive . "'>
								<span>" . $optionTitle . $priceFormated . "</span><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
							</li>";
                    $countOption++;
                endforeach;
            }
            if (count($groupedChilds) > 0) {
                foreach ($groupedChilds as $childId => $childData):
                    $optionActive = ($countOption == 1) ? 'option_active' : '';
                    $html .= "<li optiontype='grouped' id='" . $productId . "_grouped_" . $childId . "' onclick=\"showProductOptions('" . $productId . "', '" . $childId . "')\" value='" . $productId . "_grouped_" . $childId . "' class='" . $productId . "_options_label grouped_label " . $optionActive . "'>
					<span>" . $childData['name'] . "</span><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
				</li>";
                    $countOption++;
                endforeach;
            }
            if (count($bundleChilds) > 0) {
                foreach ($bundleChilds as $childId => $childData):

                    $optionActive = ($countOption == 1) ? 'option_active' : '';
                    $childName = ($childData['required'] == '1') ? $childData['title'] . "<span class='required'>*</span>" : $childData['title'];
                    $html .= "<li optiontype='bundle' id='" . $productId . "_bundle_" . $childId . "' onclick=\"showProductOptions('" . $productId . "', '" . $childId . "')\" value='" . $productId . "_bundle_" . $childId . "' class='" . $productId . "_options_label bundle_label " . $optionActive . "'>
					<span>" . $childName . "</span><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
				</li>";
                    $countOption++;
                endforeach;
            }
            if (count($storeCreditOptions) > 0) {
                $optionActive = 'option_active';
                $childName = $this->__('Credit option');
                $html .= "<li optiontype='storecredit' id='" . $productId . "_storecredit_' onclick=\"showProductOptions('" . $productId . "', '')\" value='" . $productId . "_storecredit_' class='" . $productId . "_options_label " . $optionActive . "'>
					<span>" . $childName . "</span><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
				</li>";
            }
            $html .= "</ul></div></div></div>
                     <div class='col col-value col-lg-6 col-md-6 col-sm-6 col-xs-6'>
						<div class='pospanel panel-default'>
							<div class='panel-heading'><span>" . $this->__($option_value_label) . "</span></div>
								<div class='options'>";
            $numberOption = 1;
            if (count($options) > 0)
                foreach ($options as $optionCode => $optionValues):
                    if ($optionCode == 'price_condition')
                        continue;
                    $onlyOption = false;
                    $onlyOptionValue = '';
                    $onlyOptionLabel = '';
                    if (count($optionValues) == 3) {
                        $onlyOption = true;
                        foreach ($optionValues as $optionValue => $optionLabel) {
                            if ($optionValue == 'optionId' || $optionValue == 'optionLabel')
                                continue;
                            $onlyOptionValue = $optionValue;
                            $onlyOptionLabel = "optionLabel='" . $optionLabel . "'";
                        }
                    }
                    $hideClass = ($numberOption != 1) ? 'hide' : '';
                    $hideOnlyClass = (!$onlyOption) ? 'hide' : '';
                    $html .= "<ul id='" . $productId . "_" . $optionCode . "_values' class='" . $productId . "_option_values " . $hideClass . "'>
								<input " . $onlyOptionLabel . " type='hidden' optionCode='" . $optionCode . "' optionId='" . $optionValues['optionId'] . "'  id='" . $productId . "_" . $optionCode . "_value' class='input_options input_options_" . $productId . "' value='" . $onlyOptionValue . "'/>";
                    foreach ($optionValues as $optionValue => $optionLabel):
                        if ($optionValue == 'optionId' || $optionValue == 'optionLabel')
                            continue;
                        $html .= "<li onclick=\"selectProductOption('" . $productId . "', '" . $optionCode . "', '" . $optionValue . "', '" . $optionLabel . "')\" value='" . $optionValue . "'>" . $optionLabel . "
									<span class='glyphicon glyphicon-ok prd_options_selected_icon prd_options_selected_icon_" . $optionCode . " " . $hideOnlyClass . "' id='" . $product->getId() . "_" . $optionCode . "_" . $optionValue . "_selected_icon'></span>
									</li>";
                        $numberOption++;
                    endforeach;
                    $html .="</ul>";
                endforeach;
            if (count($customOptions) > 0)
                foreach ($customOptions as $option):
                    $hideClass = ($numberOption != 1) ? 'hide' : '';
                    $html .= "<ul id='" . $productId . "_co_" . $option['option_id'] . "_values' class='" . $productId . "_option_values custom_options_values  " . $hideClass . "'>
								<li>";
                    $html .= $this->getCustomOptionHtml($option);
                    $html .= "<span class='glyphicon glyphicon-ok prd_options_selected_icon prd_options_selected_icon_" . $optionCode . " hide' id='" . $productId . "_" . $optionCode . "_" . $optionValue . "_selected_icon'></span>
                               </li>
                               </ul>";
                    $numberOption++;
                endforeach;
            if (count($storeCreditOptions) > 0) {
                $html .= "<ul id='" . $productId . "_storecredit__values' class='" . $productId . "_option_values custom_options_values '><li>";
                $html .= $this->getStoreCreditOptionHtml($product, $storeCreditOptions);
                $html .= "</li></ul>";
            }
            if (count($groupedChilds) > 0):
                $hideClass = ($numberOption != 1) ? 'hide' : '';
                $html .= "<ul id='" . $productId . "_grouped_qtys' class='" . $productId . "_option_values grouped_childs_qty  " . $hideClass . "'>";
                foreach ($groupedChilds as $childId => $childData):
                    $html .= "<li class='grouped_qty_area'>
								<p id='prd_fullname_" . $childId . "' class='hide'>" . $childData['name'] . "</p>
								<img id='grouped_child_imgpath_" . $childId . "' src='" . $childData['imgpath'] . "'/>
								<input id='grouped_child_price_" . $childId . "' value='" . $childData['price'] . "' class='hide'/>
								<input value='0' childid='" . $childId . "' class='" . $productId . "_grouped_childs_qty'/>
								<span class='price'>" . Mage::helper('core')->currency($childData['price'], true, false) . "</span>
								<span class='glyphicon glyphicon-ok prd_options_selected_icon prd_options_selected_icon_" . $optionCode . " hide' id='" . $productId . "_" . $optionCode . "_" . $optionValue . "_selected_icon'></span>
								</li>";
                    $numberOption++;
                endforeach;
                $html .= "</ul>";
            endif;
            if (count($bundleChilds) > 0)
                foreach ($bundleChilds as $childId => $childData):
                    $hideClass = ($numberOption != 1) ? 'hide' : '';
                    $html .= "<ul id='" . $productId . "_bundle_" . $childId . "_values' class='" . $productId . "_option_values bundle_childs_values  " . $hideClass . "'>
								<li>";
                    $html .= $this->getBundleSelectionHtml($childData);
                    $html .= "</li>
                               </ul>";
                    $numberOption++;
                endforeach;
            $html .= "</div>
                               </div>
                            </div>
                        </div>
                     </ul>
                 </div>";
            if ($extraClass == '') {
                $html .="<script type='text/javascript'>
							
						</script> ";
            }
        endif;
        $html .= " </div>";
        return $html;
    }

    public function getProductOptions(Mage_Catalog_Model_Product $product) {
        $optionsData = array();
        $productType = $product->getTypeId();
        if ($productType == 'configurable')
            $optionsData['configurable'] = $this->getConfigurableOptions($product);
        if ($productType == 'grouped')
            $optionsData['grouped'] = $this->getGroupedChilds($product);
        if ($productType == 'bundle')
            $optionsData['bundle'] = $this->getBundleChilds($product);
        if ($productType == 'customercredit')
            $optionsData['customercredit'] = $this->getStoreCreditOptions($product);
        $optionsData['custom_options'] = $this->getCustomOptions($product);
        return $optionsData;
    }

    public function getStoreCreditOptions(Mage_Catalog_Model_Product $product) {
        $options = array();
        $blockView = Mage::getBlockSingleton('customercredit/product_view');
        if ($blockView)
            $options = $blockView->getCreditAmount($product);
        return $options;
    }

    public function getStoreCreditOptionHtml($product, $storeCreditOptions) {
        $html = '';
        $blockView = Mage::getBlockSingleton('customercredit/product_view');
        if ($blockView) {
            $blockView->setProduct($product);
            $html .= $blockView->setTemplate('webpos/webpos/storecredit/product/view/view.phtml')->toHtml();
        }
        return $html;
    }

    public function getGroupedChilds(Mage_Catalog_Model_Product $product) {
        $childProducts = array();
        $childs = $product->getTypeInstance(true)->getAssociatedProducts($product);
        if (count($childs) > 0)
            foreach ($childs as $child) {
                if ($this->_isModuleInventoryEnabled == true) {//Magnus
                    if (in_array($child->getId(), $this->_productInWarehouseSesion)) {
                        $childProducts[$child->getId()]['price'] = $child->getFinalPrice();
                        $childProducts[$child->getId()]['name'] = $child->getName();
                        $childProducts[$child->getId()]['imgpath'] = Mage::helper('catalog/image')->init($child, 'thumbnail')->resize(120);
                    }
                } else {
                    $childProducts[$child->getId()]['price'] = $child->getFinalPrice();
                    $childProducts[$child->getId()]['name'] = $child->getName();
                    $childProducts[$child->getId()]['imgpath'] = Mage::helper('catalog/image')->init($child, 'thumbnail')->resize(120);
                }
            }
        return $childProducts;
    }

    public function getBundleChilds(Mage_Catalog_Model_Product $product) {
        $bundleChilds = array();
        $store_id = Mage::app()->getStore()->getId();
        $options = Mage::getModel('bundle/option')->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder();
        $options->joinValues($store_id);
        $selections = $product->getTypeInstance(true)
                ->getSelectionsCollection($product->getTypeInstance(true)
                ->getOptionsIds($product), $product);
		$price_type = $product->getData('price_type');
        foreach ($options->getItems() as $option) {
            $bundleChilds[$option->getId()]['title'] = $option->getTitle();
            $bundleChilds[$option->getId()]['required'] = $option->getRequired();
            $bundleChilds[$option->getId()]['type'] = $option->getType();
            $bundleChilds[$option->getId()]['id'] = $option->getId();
            $bundleChilds[$option->getId()]['product_id'] = $product->getId();
            foreach ($selections as $selection) {
                $selection_price_type = $selection->getData('selection_price_type');
                $selection_price_value = $selection->getData('selection_price_value');
                $price = $selection->getData('price');
                $selection_price = ($selection_price_type == 0) ? $selection_price_value : $price * $selection_price_value;

				if($price_type == 0){
					$selection_price = $price;
				}
                if ($option->getId() == $selection->getOptionId()) {
                    if ($this->_isModuleInventoryEnabled == true) { /* Magnus(78787878) */
                        if (in_array($selection->getProductId(), $this->_productInWarehouseSesion)) {
                            $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()] = array();
                            $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['name'] = $selection->getName();
                            $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['qty'] = $selection->getSelectionQty();
                            $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['price'] = $selection_price;
                            $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['can_change_qty'] = $selection->getSelectionCanChangeQty();
                            $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['is_default'] = $selection->getIsDefault();
                        }
                    } else {
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()] = array();
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['name'] = $selection->getName();
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['qty'] = $selection->getSelectionQty();
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['price'] = $selection_price;
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['can_change_qty'] = $selection->getSelectionCanChangeQty();
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()]['is_default'] = $selection->getIsDefault();
                    }
                }
            }
        }
        return $bundleChilds;
    }

    public function getConfigurableOptions(Mage_Catalog_Model_Product $product) {
        if ($product->getTypeId() != 'configurable')
            return '';
        $productTypeInstance = $product->getTypeInstance(true);
        if (
                version_compare(Mage::getVersion(), '1.7.0.0', '<') || version_compare(Mage::getVersion(), '1.7.0.0', '>=')
        ) {
            $productAttributeOptions = $productTypeInstance->getConfigurableAttributesAsArray($product);
        } else {
            $productAttributeOptions = $productTypeInstance->getConfigurableOptions($product);
        }
        if (
                version_compare(Mage::getVersion(), '1.7.0.0', '<') || version_compare(Mage::getVersion(), '1.7.0.0', '>=')
        ) {
            $options = $prices = array();
            foreach ($productAttributeOptions as $productAttributeOption) {
                $values = $productAttributeOption['values'];
                $optionId = $productAttributeOption['attribute_id'];
                $code = $productAttributeOption['attribute_code'];
                $optionLabel = $productAttributeOption['label'];
                $options[$code]['optionId'] = $optionId;
                $options[$code]['optionLabel'] = $optionLabel;
                foreach ($values as $value) {
                    $optionValueId = $value['value_index'];
                    $pricing_value = $value['pricing_value'];
                    $val = $value['label'];
                    $is_percent = $value['is_percent'];
                    $options[$code][$optionValueId] = $val;
                    /*
                      if($pricing_value != null && $pricing_value != 0){
                      $prices[$code][$code] = $optionValueId;
                      $prices[$code]['is_percent'] = $is_percent;
                      $prices[$code]['price'] = $pricing_value;
                      }
                     */
                }
            }
            if (count($options) > 0) {
                $loaded = array();
                $product = Mage::getModel('catalog/product')->load($product->getId());
                $productAttributeOptions2 = $productTypeInstance->getConfigurableOptions($product);
                foreach ($productAttributeOptions2 as $productAttributeOption) {
                    foreach ($productAttributeOption as $optionValues) {
                        if (in_array($optionValues['sku'], $loaded))
                            continue;
                        $loaded[] = $optionValues['sku'];
                        $collection = Mage::getModel('catalog/product')->getCollection();
                        $collection->addAttributeToFilter('sku', $optionValues['sku']);
                        $childProduct = $collection->getFirstItem();
                        if (!empty($childProduct)) {
                            if ($this->_isModuleInventoryEnabled == true) {
                                if (in_array($childProduct->getId(), $this->_productInWarehouseSesion)) {
                                    $childProduct = Mage::getModel('catalog/product')->load($childProduct->getId());
                                    $added = array();
                                    foreach ($options as $code => $data) {
                                        if (in_array($code, $added))
                                            continue;
                                        $added[] = $code;
                                        $optionId = $data['optionId'];
                                        $prices[$childProduct->getData('sku')][$optionId] = $childProduct->getData($code);
                                    }
                                    $prices[$childProduct->getData('sku')]['price'] = $this->convertPrice($childProduct->getPrice(), $this->_current_currency);
                                    $prices[$childProduct->getData('sku')]['isSaleable'] = ($childProduct->isSaleable()) ? 'true' : 'false';
                                }
                            } else {
                                $childProduct = Mage::getModel('catalog/product')->load($childProduct->getId());
                                $added = array();
                                foreach ($options as $code => $data) {
                                    if (in_array($code, $added))
                                        continue;
                                    $added[] = $code;
                                    $optionId = $data['optionId'];
                                    $prices[$childProduct->getData('sku')][$optionId] = $childProduct->getData($code);
                                }
                                $prices[$childProduct->getData('sku')]['price'] = $this->convertPrice($childProduct->getPrice(), $this->_current_currency);
                                $prices[$childProduct->getData('sku')]['isSaleable'] = ($childProduct->isSaleable()) ? 'true' : 'false';
                            }
                        }
                    }
                }
            }
            $options['price_condition'] = Zend_Json::encode(array_values($prices));
        } else {
            $options = $prices = array();
            foreach ($productAttributeOptions as $productAttributeOption) {
                foreach ($productAttributeOption as $optionValues) {
                    $childProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $optionValues['sku']);
                    if (!empty($childProduct) && $childProduct->isSaleable()) {
                        if ($this->_isModuleInventoryEnabled == true) {//Magnus
                            if (in_array($childProduct->getId(), $this->_productInWarehouseSesion)) {
                                $val = $optionValues['option_title'];
                                $code = $optionValues['attribute_code'];
                                $attr = $product->getResource()->getAttribute($code);
                                if ($attr->usesSource()) {
                                    $is_required = $attr->getData('is_required');
                                    $optionId = $attr->getAttributeId();
                                    $allOptions = $attr->getSource()->getAllOptions(false);
                                    foreach ($allOptions as $option) {
                                        if ($option['label'] == $val)
                                            $optionValueId = $option['value'];
                                    }
                                }
                                $options[$code][$optionValueId] = $val;
                                $options[$code]['optionLabel'] = $code;
                                $options[$code]['optionId'] = $optionId;
                                $prices[$childProduct->getData('sku')][$optionId] = $childProduct->getData($code);
                                $prices[$childProduct->getData('sku')]['price'] = $this->convertPrice($childProduct->getPrice(), $this->_current_currency);
                            }
                        }else {
                            $val = $optionValues['option_title'];
                            $code = $optionValues['attribute_code'];
                            $attr = $product->getResource()->getAttribute($code);
                            if ($attr->usesSource()) {
                                $is_required = $attr->getData('is_required');
                                $optionId = $attr->getAttributeId();
                                $allOptions = $attr->getSource()->getAllOptions(false);
                                foreach ($allOptions as $option) {
                                    if ($option['label'] == $val)
                                        $optionValueId = $option['value'];
                                }
                            }
                            $options[$code][$optionValueId] = $val;
                            $options[$code]['optionLabel'] = $code;
                            $options[$code]['optionId'] = $optionId;
                            $prices[$childProduct->getData('sku')][$optionId] = $childProduct->getData($code);
                            $prices[$childProduct->getData('sku')]['price'] = $this->convertPrice($childProduct->getPrice(), $this->_current_currency);
                        }
                    }
                }
            }
            $options['price_condition'] = Zend_Json::encode(array_values($prices));
        }
        return $options;
    }

    public function getCustomOptions(Mage_Catalog_Model_Product $product) {
        $options = null;
        $productTypeInstance = $product->getTypeInstance(true);
        $hasOption = $productTypeInstance->hasOptions($product);
        if ($hasOption) {
            $optionCollection = Mage::getModel('catalog/product_option')->getProductOptionCollection($product);
            if (count($optionCollection) > 0) {
                $options = array();
                foreach ($optionCollection as $option) {
                    $this->getCustomOptionValues($option);
                    $options[$option->getOptionId()] = $option->getData();
                }
            }
        }
        return $options;
    }

    public function getRequiredOptions(Mage_Catalog_Model_Product $product) {
        $options = null;
        $productTypeInstance = $product->getTypeInstance(true);
        $hasOption = $productTypeInstance->hasOptions($product);
        if ($hasOption) {
            $optionCollection = Mage::getModel('catalog/product_option')->getProductOptionCollection($product);
            $optionCollection->addFieldToFilter('is_require', 1);
            if (count($optionCollection) > 0) {
                $options = array();
                foreach ($optionCollection as $option) {
                    $options[$option->getOptionId()] = $option->getData();
                }
            }
        }
        return $options;
    }

    public function getCustomOptionHtml($optionData) {
        $type = $optionData['type'];
        $product_id = $optionData['product_id'];
        $option_id = $optionData['option_id'];
        $is_require = $optionData['is_require'];
        $sku = $optionData['sku'];
        $max_characters = $optionData['max_characters'];
        $file_extension = $optionData['file_extension'];
        $image_size_x = $optionData['image_size_x'];
        $image_size_y = $optionData['image_size_y'];
        $sort_order = $optionData['sort_order'];
        $default_title = $optionData['default_title'];
        $title = $optionData['title'];
        $default_price = $optionData['default_price'];
        $default_price_type = $optionData['default_price_type'];
        $store_price = $optionData['store_price'];
        $store_price_type = $optionData['store_price_type'];
        $price = $optionData['price'];
        $price = $this->convertPrice($price, $this->_current_currency);
        $store = Mage::getModel('core/store')->load($this->_store_id);
        $price_type = $optionData['price_type'];
        $product = Mage::getModel('catalog/product')->load($product_id);
        $product->setFinalPrice($this->convertPrice($product->getFinalPrice(), $this->_current_currency));
        if ($price > 0) {
            if ($price_type == 'fixed') {
                $priceFormated = ' +' . $store->formatPrice($price);
                $extraprice = $price;
            } else {
                $priceFormated = ' +' . $store->formatPrice($price * $product->getFinalPrice() / 100);
                $extraprice = $price * $product->getFinalPrice() / 100;
            }
        } else {
            $priceFormated = '';
            $extraprice = 0;
        }
        $optionHtml = '';
        $requireClassName = '';
        if ($is_require == 1)
            $requireClassName = ' option_required ';

        switch ($type) {
            case "field":
                $optionHtml = "<input onchange='optionChangeSelection(this)' extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value' type='text' placeholder='" . $title . "' class='" . $product_id . "_co_values custom_options text " . $requireClassName . " '/>";
                break;
            case "drop_down":
                $options = $this->getCustomOptionValues('', $option_id);
                if (count($options) > 0) {
                    $optionHtml = "<select onchange='optionChangeSelection(this)' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value' class='custom_options select " . $product_id . "_co_values " . $requireClassName . "'>";
                    $optionHtml .= "<option value=''>-- Please select --</option>";
                    foreach ($options as $option) {
                        if ($option['price'] > 0) {
                            if ($option['price_type'] == 'fixed') {
                                $priceFormated = ' +' . $store->formatPrice($option['price']);
                                $extraprice = $option['price'];
                            } else {
                                $priceFormated = ' +' . $store->formatPrice($option['price'] * $product->getFinalPrice() / 100);
                                $extraprice = $option['price'] * $product->getFinalPrice() / 100;
                            }
                        } else {
                            $priceFormated = '';
                            $extraprice = 0;
                        }
                        $optionHtml .= "<option extraprice='" . $extraprice . "' value='" . $option['option_type_id'] . "'>" . $option['title'] . $priceFormated . "</option>";
                    }
                    $optionHtml .= "</select>";
                }
                break;
            case "date_time":
                $optionHtml .= $this->getDateHtml($product_id, $option_id, $requireClassName . ' type_date_time ', $extraprice);
                $optionHtml .= ":";
                $optionHtml .= $this->getTimeHtml($product_id, $option_id, $requireClassName . ' type_date_time ', $extraprice);
                break;
            case "area":
                $optionHtml = "<textarea onchange='optionChangeSelection(this)' extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value' class='" . $product_id . "_co_values custom_options area " . $requireClassName . " ' rows='5' cols='25'></textarea>";
                break;
            case "file":
                $optionHtml = "
				<form enctype='multipart/form-data' method='post' action='" . Mage::getUrl('webpos/product/uploadFile') . "' id='" . $product_id . "_co_" . $option_id . "_value_form' target=''>
				<input name='cofile' onchange='optionChangeSelection(this)' extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value' type='file' placeholder='" . $title . "' class='" . $product_id . "_co_values custom_options file " . $requireClassName . " '/>
				<input  prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_file_action' name='" . $product_id . "_co_" . $option_id . "_value_file_action' type='hidden'  value='save_new' />
				</form>
				";
                if (isset($image_size_x))
                    $optionHtml .= "<p><span>Maximum image width</span><strong>" . $image_size_x . "</strong></p>";
                if (isset($image_size_x))
                    $optionHtml .= "<span><span>Maximum image height</span><strong>" . $image_size_y . "</strong></span>";
                break;
            case "radio":
                $options = $this->getCustomOptionValues('', $option_id);
                if (count($options) > 0) {
                    if ($requireClassName == '')
                        $optionHtml .= "
						<input onclick='optionChangeSelection(this)' value='' name='" . $product_id . "_co_" . $option_id . "_value' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_none_value' type='radio' class='" . $product_id . "_co_values custom_options " . $requireClassName . " '/>
						<label for='" . $product_id . "_co_" . $option_id . "_none_value'>" . $this->__('None') . "</label>
						";
                    foreach ($options as $option) {
                        if ($option['price'] > 0) {
                            if ($option['price_type'] == 'fixed') {
                                $priceFormated = ' +' . $store->formatPrice($option['price']);
                                $extraprice = $option['price'];
                            } else {
                                $priceFormated = ' +' . $store->formatPrice($option['price'] * $product->getFinalPrice() / 100);
                                $extraprice = $option['price'] * $product->getFinalPrice() / 100;
                            }
                        } else {
                            $priceFormated = '';
                            $extraprice = 0;
                        }
                        $optionHtml .= "
						<div class='bundle_item_row'>
						<input onclick='optionChangeSelection(this)' value='" . $option['option_type_id'] . "' extraprice='" . $extraprice . "' name='" . $product_id . "_co_" . $option_id . "_value' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_" . $option['option_type_id'] . "_value' type='radio' class='" . $product_id . "_co_values custom_options radio " . $requireClassName . " '/>
						<label for='" . $product_id . "_co_" . $option_id . "_" . $option['option_type_id'] . "_value'>" . $option['title'] . $priceFormated . "</label>
						</div>
						";
                    }
                }
                break;
            case "checkbox":
                $options = $this->getCustomOptionValues('', $option_id);
                if (count($options) > 0) {
                    foreach ($options as $option) {
                        if ($option['price'] > 0) {
                            if ($option['price_type'] == 'fixed') {
                                $priceFormated = ' +' . $store->formatPrice($option['price']);
                                $extraprice = $option['price'];
                            } else {
                                $priceFormated = ' +' . $store->formatPrice($option['price'] * $product->getFinalPrice() / 100);
                                $extraprice = $option['price'] * $product->getFinalPrice() / 100;
                            }
                        } else {
                            $priceFormated = '';
                            $extraprice = 0;
                        }
                        $optionHtml .= "
						<div class='bundle_item_row'>
							<input onclick='optionChangeSelection(this)' value='" . $option['option_type_id'] . "' extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_" . $option['option_type_id'] . "_value' type='checkbox' class='" . $product_id . "_co_values custom_options checkbox " . $requireClassName . " '/>
							<label for='" . $product_id . "_co_" . $option_id . "_" . $option['option_type_id'] . "_value'>" . $option['title'] . $priceFormated . "</label>
						</div>
						";
                    }
                }

                break;
            case "multiple":
                $options = $this->getCustomOptionValues('', $option_id);
                if (count($options) > 0) {
                    $optionHtml = "<select onchange='optionChangeSelection(this)' prdid='" . $product_id . "' optionid='" . $option_id . "' multiple='multiple' id='" . $product_id . "_co_" . $option_id . "_value' class='" . $product_id . "_co_values custom_options multiple " . $requireClassName . "'>";
                    foreach ($options as $option) {
                        if ($option['price'] > 0) {
                            if ($option['price_type'] == 'fixed') {
                                $priceFormated = ' +' . $store->formatPrice($option['price']);
                                $extraprice = $option['price'];
                            } else {
                                $priceFormated = ' +' . $store->formatPrice($option['price'] * $product->getFinalPrice() / 100);
                                $extraprice = $option['price'] * $product->getFinalPrice() / 100;
                            }
                        } else {
                            $priceFormated = '';
                            $extraprice = 0;
                        }
                        $optionHtml .= "<option extraprice='" . $extraprice . "' value='" . $option['option_type_id'] . "'>" . $option['title'] . $priceFormated . "</option>";
                    }
                    $optionHtml .= "</select>";
                }
                break;
            case "date":
                $optionHtml .= $this->getDateHtml($product_id, $option_id, $requireClassName . ' type_date ', $extraprice);
                break;
            case "time":
                $optionHtml .= $this->getTimeHtml($product_id, $option_id, $requireClassName . ' type_time ', $extraprice);
                break;
        }
        return $optionHtml;
    }

    public function getBundleSelectionHtml($bundleOptionData) {
        $type = $bundleOptionData['type'];
        $required = $bundleOptionData['required'];
        $title = $bundleOptionData['title'];
        $option_id = $bundleOptionData['id'];
        $items = $bundleOptionData['items'];
        $product_id = $bundleOptionData['product_id'];
        $store = Mage::getModel('core/store')->load($this->_store_id);
        $optionHtml = '';
        $requireClassName = '';
        if ($required == 1)
            $requireClassName = ' option_required ';

        switch ($type) {
            case "select":
                if (count($items) > 0) {
                    $canChangeQty = 'disabled';
                    $defaultQty = 0;
                    $optionHtml = "<select onchange='bundleChangeSelection(this)' prdid='" . $product_id . "' optionid='" . $option_id . "' qty_input_id='" . $product_id . "_bundle_" . $option_id . "_value_qty' id='" . $product_id . "_bundle_" . $option_id . "_value' class='bundle_options select " . $product_id . "_bundle_values " . $requireClassName . "'>";
                    $optionHtml .= "<option value=''>Choose a selection...</option>";
                    foreach ($items as $itemId => $itemData) {
                        $selected = ($itemData['is_default'] == '1' || count($items) == 1) ? 'selected' : '';
                        if ($itemData['can_change_qty'] == '1')
                            $canChangeQty = '';
                        $defaultQty = (int) $itemData['qty'];
                        $itemData['price'] = $this->convertPrice($itemData['price'], $this->_current_currency);
                        $price = $store->formatPrice($itemData['price']);
                        $optionHtml .= "<option " . $selected . " value='" . $itemId . "|" . $itemData['qty'] . "|" . $itemData['price'] . "|" . $itemData['can_change_qty'] . "'>" . $itemData['name'] . "<span class='price'>+" . $price . "</span></option>";
                    }
                    $optionHtml .= "</select>
							<div>
								<label for='" . $product_id . "_bundle_" . $option_id . "_value_qty'>" . $this->__('Qty') . "</label>
								<input prdid='" . $product_id . "' onchange='bundleChangeSelection(this)' " . $canChangeQty . " class='bundle_item_qty ' value='" . $defaultQty . "' id='" . $product_id . "_bundle_" . $option_id . "_value_qty'/>
							</div>
					";
                }
                break;
            case "radio":
                $canChangeQty = 'disabled';
                $defaultQty = 0;
                foreach ($items as $itemId => $itemData) {
                    $itemData['price'] = $this->convertPrice($itemData['price'], $this->_current_currency);
                    $price = $store->formatPrice($itemData['price']);
                    $selected = ($itemData['is_default'] == '1') ? 'checked' : '';
                    if ($itemData['can_change_qty'] == '1')
                        $canChangeQty = '';
                    if ($itemData['is_default'] == '1')
                        $defaultQty = (int) $itemData['qty'];
                    $optionHtml .= "
						<div class='bundle_item_row'>
							<input  onclick='bundleChangeSelection(this)' " . $selected . " name='" . $product_id . "_bundle_" . $option_id . "_value' qty_input_id='" . $product_id . "_bundle_" . $option_id . "_value_qty' value='" . $itemId . "|" . $itemData['qty'] . "|" . $itemData['price'] . "|" . $itemData['can_change_qty'] . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_bundle_" . $option_id . "_" . $itemId . "_value' type='radio' class='" . $product_id . "_bundle_values bundle_options radio " . $requireClassName . " '/>
							<label for='" . $product_id . "_bundle_" . $option_id . "_" . $itemId . "_value'>" . $itemData['name'] . "<span class='price'>+" . $price . "</span></label>
						</div>
					";
                }
                $optionHtml .= "
							<div>
								<label for='" . $product_id . "_bundle_" . $option_id . "_value_qty'>" . $this->__('Qty') . "</label>
								<input prdid='" . $product_id . "' onchange='bundleChangeSelection(this)' " . $canChangeQty . " class='bundle_item_qty' value='" . $defaultQty . "' id='" . $product_id . "_bundle_" . $option_id . "_value_qty'/>
							</div>
					";
                break;
            case "checkbox":
                foreach ($items as $itemId => $itemData) {
                    $itemData['price'] = $this->convertPrice($itemData['price'], $this->_current_currency);
                    $price = $store->formatPrice($itemData['price']);
                    $selected = ($itemData['is_default'] == '1') ? 'checked' : '';
                    $optionHtml .= "
						<div class='bundle_item_row'>
							<input onclick='bundleChangeSelection(this)' " . $selected . " value='" . $itemId . "|" . $itemData['qty'] . "|" . $itemData['price'] . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_bundle_" . $option_id . "_" . $itemId . "_value' type='checkbox' class='" . $product_id . "_bundle_values bundle_options checkbox " . $requireClassName . " '/>
							<label for='" . $product_id . "_bundle_" . $option_id . "_" . $itemId . "_value'>" . (int) $itemData['qty'] . ' x ' . $itemData['name'] . "<span class='price'>+" . $price . "</span></label>
						</div>
					";
                }
                break;
            case "multi":
                if (count($items) > 0) {
                    $optionHtml = "<select onchange='bundleChangeSelection(this)' prdid='" . $product_id . "' optionid='" . $option_id . "' multiple='multiple' id='" . $product_id . "_bundle_" . $option_id . "_value' class='" . $product_id . "_bundle_values bundle_options multiple " . $requireClassName . "'>";
                    foreach ($items as $itemId => $itemData) {
                        $selected = ($itemData['is_default'] == '1') ? 'selected' : '';
                        $itemData['price'] = $this->convertPrice($itemData['price'], $this->_current_currency);
                        $price = $store->formatPrice($itemData['price']);
                        $optionHtml .= "<option " . $selected . " value='" . $itemId . "|" . (int) $itemData['qty'] . "|" . $itemData['price'] . "'>" . (int) $itemData['qty'] . ' x ' . $itemData['name'] . "<span class='price'>+" . $price . "</span></option>";
                    }
                    $optionHtml .= "</select>";
                }
                break;
        }
        return $optionHtml;
    }

    public function isProductHasOptions(Mage_Catalog_Model_Product $product) {
        $groupedChilds = $bundleChilds = $optionCollection = $storeCreditOptions = array();
        $productTypeInstance = $product->getTypeInstance(true);
        $hasOption = $productTypeInstance->hasOptions($product);
        if ($hasOption == true)
            $optionCollection = Mage::getModel('catalog/product_option')->getProductOptionCollection($product);
        $configurableOptions = $this->getConfigurableOptions($product);
        if ($product->getTypeId() == 'grouped')
            $groupedChilds = $this->getGroupedChilds($product);
        if ($product->getTypeId() == 'bundle')
            $bundleChilds = $this->getBundleChilds($product);
        if ($product->getTypeId() == 'customercredit')
            $storeCreditOptions = $this->getStoreCreditOptions($product);

        return (count($storeCreditOptions) > 0 || count($bundleChilds) > 0 || count($groupedChilds) > 0 || count($optionCollection) > 0 || ($configurableOptions != '' && count($configurableOptions) > 1)) ? true : false;
    }

    public function getCustomOptionValues(Mage_Catalog_Model_Product $option, $optionId = null) {
        $optionsValues = array();
        if ($optionId != null)
            $option = Mage::getModel('catalog/product_option')->load($optionId);
        $optionValueCollection = Mage::getModel('catalog/product_option_value')->getValuesCollection($option);
        if (count($optionValueCollection) > 0)
            foreach ($optionValueCollection as $values) {
                $optionsValues[] = $values->getData();
            }
        return $optionsValues;
    }

    public function getDateOptionsHtml($minNumber, $maxNumber) {
        $optionHtml = '';
        $optionHtml .= "<option value=''>--</option>";
        for ($i = (int) $minNumber; $i <= (int) $maxNumber; $i++) {
            $numberFormated = ($i < 10) ? '0' . $i : $i;
            $optionHtml .= "<option value='" . $i . "'>" . $numberFormated . "</option>";
        }
        return $optionHtml;
    }

    public function getDateHtml($product_id, $option_id, $requireClassName, $extraprice) {
        $cyear = date("Y");
        $optionHtml = "<select extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_year' class='" . $product_id . "_co_values custom_options " . $requireClassName . "'>";
        $optionHtml .= "<option value=''>--</option>";
        $optionHtml .= "<option value='" . $cyear . "'>" . $cyear . "</option>";
        $optionHtml .= "</select>";
        $optionHtml .= "<select extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_month' class='" . $product_id . "_co_values custom_options " . $requireClassName . "'>";
        $optionHtml .= $this->getDateOptionsHtml(1, 12);
        $optionHtml .= "</select>";
        $optionHtml .= "<select extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_day' class='" . $product_id . "_co_values custom_options " . $requireClassName . "'>";
        $optionHtml .= $this->getDateOptionsHtml(1, 31);
        $optionHtml .= "</select>";
        $optionHtml .= "
					<script type='text/javascript'>
						var month31day = [1,3,5,7,8,10,12];
						var month30day = [4,6,9,11];
						var monthObj = $('" . $product_id . "_co_" . $option_id . "_value_month');
						var dayObj = $('" . $product_id . "_co_" . $option_id . "_value_day');
						Event.observe(monthObj,'change',function(){
							if(month31day.indexOf(monthObj.value)){
								dayObj.innerHTML = \"" . $this->getDateOptionsHtml(1, 31) . "\";
							}else if(month30day.indexOf(monthObj.value)){
								dayObj.innerHTML = \"" . $this->getDateOptionsHtml(1, 30) . "\";
							}else if(monthObj.value == 2){
								dayObj.innerHTML = \"" . $this->getDateOptionsHtml(1, 28) . "\";
							}
						});
					</script>
		";
        return $optionHtml;
    }

    public function getTimeHtml($product_id, $option_id, $requireClassName, $extraprice) {
        $optionHtml = '';
        $optionHtml = "<select extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_hour' class='" . $product_id . "_co_values custom_options " . $requireClassName . "'>";
        $optionHtml .= $this->getDateOptionsHtml(1, 12);
        $optionHtml .= "</select>";
        $optionHtml .= "<select extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_minute' class='" . $product_id . "_co_values custom_options " . $requireClassName . "'>";
        $optionHtml .= $this->getDateOptionsHtml(0, 59);
        $optionHtml .= "</select>";
        $optionHtml .= "<select extraprice='" . $extraprice . "' prdid='" . $product_id . "' optionid='" . $option_id . "' id='" . $product_id . "_co_" . $option_id . "_value_day_part' class='" . $product_id . "_co_values custom_options " . $requireClassName . "'>";
        $optionHtml .= "<option value='am'>AM</option>";
        $optionHtml .= "<option value='pm'>PM</option>";
        $optionHtml .= "</select>";
        return $optionHtml;
    }

    public function getProductAttributeForSearch() {
        return Mage::getStoreConfig('webpos/product_search/product_attribute', $this->getStoreId());
    }

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function isEnableSearchOnLocal() {
        $config = Mage::getStoreConfig('webpos/product_search/search_offline', $this->getStoreId());
        return ($config == true) ? true : false;
    }

    public function getUpdatedProduct() {
        $dir = Mage::getBaseDir('media') . DS . 'webpos';
        $product_updatedfile = $dir . DS . 'product_updated.txt';
        if (is_file($product_updatedfile)) {
            $fileContent = file_get_contents($product_updatedfile);
            $productIds = explode(',', $fileContent);
            return $productIds;
        }
    }

    public function convertPrice($price, $currentCurrencyCode = null) {
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        if (!$currentCurrencyCode)
            $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($currentCurrencyCode == $baseCurrencyCode)
            $currentPrice = Mage::helper('directory')->currencyConvert($price, $currentCurrencyCode, $baseCurrencyCode);
        else
            $currentPrice = Mage::helper('directory')->currencyConvert($price, $baseCurrencyCode, $currentCurrencyCode);
        return $currentPrice;
    }

    public function getProductPriceAndTaxData($product) {
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $tax = Mage::helper('tax');
        $calcTax = true;
        $finalPriceValue = $product->getFinalPrice();
        $finalPriceWithTax = $tax->getPrice($product, $finalPriceValue, $calcTax);
        $price_includes_tax = Mage::getStoreConfig('tax/calculation/price_includes_tax');
        $tax_display_type = Mage::getStoreConfig('tax/display/type');
        $tax_cart_display = Mage::getStoreConfig('tax/cart_display/price');
        $taxClassId = $product->getData("tax_class_id");
        $taxClasses = Mage::helper("core")->jsonDecode($tax->getAllRatesByProductClass());
        $taxRate = isset($taxClasses["value_" . $taxClassId]) ? $taxClasses["value_" . $taxClassId] : 0;
        $tax_amount = 0;
        $price = $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        if ($price == $finalPrice) {
            if ($finalPriceWithTax == $price) {
                if ($price_includes_tax == 1) {
                    $priceEx = round(100 * $price / ($taxRate + 100), 2);
                    $tax_amount = $price - $priceEx;
                } else {
                    $tax_amount = round($taxRate * $price / 100, 2);
                }
            } else {
                if (floatval($finalPriceWithTax) > floatval($price)) {
                    $tax_amount = floatval($finalPriceWithTax) - floatval($price);
                } else {
                    $priceEx = round(100 * $finalPrice / ($taxRate + 100), 2);
                    $tax_amount = $finalPrice - $priceEx;
                }
            }
        } else {
            if ($price_includes_tax == 1) {
                $priceEx = round(100 * $finalPrice / ($taxRate + 100), 2);
                $tax_amount = $finalPrice - $priceEx;
            } else {
                $tax_amount = $taxRate * $finalPrice / 100;
            }
        }

        if ($price == null) {
            $price = $finalPrice;
        }

        if ($price_includes_tax == 1) {
            $calcTax = false;
            $finalPriceWithTax = $product->getFinalPrice();
            $price = $finalPrice = $tax->getPrice($product, $finalPriceValue, $calcTax);
        } else {
            $finalPriceWithTax = round($price + $price * $taxRate / 100, 2);
            $tax_amount = $finalPriceWithTax - $price;
        }

        if ($tax_display_type == 2) {
            $finalPrice = $finalPriceWithTax;
        }
        $tax_amount = Mage::helper('directory')->currencyConvert($tax_amount, $baseCurrencyCode, $currentCurrencyCode);
        $finalPrice = Mage::helper('directory')->currencyConvert($finalPrice, $baseCurrencyCode, $currentCurrencyCode);
        $price = Mage::helper('directory')->currencyConvert($price, $baseCurrencyCode, $currentCurrencyCode);
        $finalPriceValue = Mage::helper('directory')->currencyConvert($finalPriceValue, $baseCurrencyCode, $currentCurrencyCode);
        $finalPriceWithTax = Mage::helper('directory')->currencyConvert($finalPriceWithTax, $baseCurrencyCode, $currentCurrencyCode);
        $productPrice = Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrencyCode, $currentCurrencyCode);

        $data = array(
            'tax_amount' => $tax_amount,
            'finalPrice' => $finalPrice,
            'price' => $price,
            'priceInclTax' => $finalPriceWithTax,
            'tax' => $taxRate,
            'includeTax' => ($price_includes_tax == 1) ? 'true' : 'false',
            'productPrice' => $productPrice
        );
        return $data;
    }

    public function getUpdatedProductFromFile($lastUpdatedTime) {
        $productIds = array();
        $dir = Mage::getBaseDir('media') . DS . 'webpos';
        $product_updatedfile = $dir . DS . 'product_updated.txt';
        if (!is_dir_writeable($dir)) {
            $file = new Varien_Io_File;
            $file->checkAndCreateFolder($dir);
        }
        $fileContent = file_get_contents($product_updatedfile);
        $fileContent = Zend_Json::decode($fileContent);
        if (count($fileContent) > 0) {
            foreach ($fileContent as $productId => $updated_time) {
                if ($lastUpdatedTime < $updated_time)
                    $productIds[] = $productId;
            }
        }
        return $productIds;
    }

    public function getProductInfoResponse($product) {
        $product = Mage::getModel('catalog/product')->load($product->getId());
        $productHelper = Mage::helper('webpos/product');
        $attributeForSearch = $productHelper->getProductAttributeForSearch();
        $attributeForSearch = explode(',', $attributeForSearch);
        $configOptions = $productHelper->getConfigurableOptions($product);
        $searchstring = array();
        $outofstock = true;
        if (count($attributeForSearch) > 0) {
            foreach ($attributeForSearch as $productAttribute) {
                $value = $product->getData($productAttribute);
                if (!empty($value)) {
                    $value = htmlspecialchars($value);
                    $searchstring[] = $value;
                }
                if (isset($configOptions[$productAttribute])) {
                    $searchstring[] = implode(' ', $configOptions[$productAttribute]);
                }
            }
        }
        $imBarcode = $this->getInventoryBarcodeByProductId($product->getId());
        if ($imBarcode != 0) {
            $searchstring[] = implode(' ', $imBarcode);
        }

        $stockItem = $product->getStockItem();
        if ($stockItem->getIsInStock())
            $outofstock = false;
        return array(
            'productId' => $product->getId(),
            'searchstring' => strtolower(implode(' ', $searchstring)),
            'outofstock' => $outofstock,
            'category' => implode(',', $product->getCategoryIds()),
            'html' => $productHelper->getProductHtml($product)
        );
    }

    public function getUpdatedCustomerFromFile($lastUpdatedTime) {
        $customerIds = array();
        $dir = Mage::getBaseDir('media') . DS . 'webpos';
        $customer_updatedfile = $dir . DS . 'customer_updated.txt';
        if (!is_dir_writeable($dir)) {
            $file = new Varien_Io_File;
            $file->checkAndCreateFolder($dir);
        }
        $fileContent = file_get_contents($customer_updatedfile);
        $fileContent = Zend_Json::decode($fileContent);
        if (count($fileContent) > 0) {
            foreach ($fileContent as $customerId => $updated_time) {
                if ($lastUpdatedTime < $updated_time)
                    $customerIds[] = $customerId;
            }
        }
        return $customerIds;
    }

    public function getCustomerInfoResponse($customer) {
        $customerId = $customer->getId();
        $groupId = $customer->getGroupId();
        $email = $customer->getEmail();
        $firstname = $customer->getFirstname();
        $lastname = $customer->getLastname();
        $address = $customer->getPrimaryBillingAddress();
        $telephone = (!empty($address)) ? $address->getTelephone() : '';
        $billingAddress = $customer->getDefaultBillingAddress();
        $shippingAddress = $customer->getDefaultShippingAddress();
        $billingAddress = (!empty($billingAddress)) ? $billingAddress->getData() : array();
        $shippingAddress = (!empty($shippingAddress)) ? $shippingAddress->getData() : array();
        return array(
            'customerId' => $customerId,
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'telephone' => $telephone,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'taxRate' => $this->getCustomerRateByGroup($groupId)
        );
    }

    public function getCustomerRateByGroup($customerGroupId) {
        $tax = Mage::getModel('tax/calculation');
        $rate = 0;
        $customerTaxClass = Mage::getModel('customer/group')
                ->load($customerGroupId)
                ->getTaxClassId();
        if (!empty($customerTaxClass)) {
            $taxModel = $tax->load($customerTaxClass, 'customer_tax_class_id');
            $rateId = $taxModel->getData('tax_calculation_rate_id');
            if (!empty($rateId)) {
                $rateModel = Mage::getModel('tax/calculation_rate')->load($rateId);
                if (!empty($rateModel))
                    $rate = $rateModel->getData('rate');
            }
        }
        return $rate;
    }

    public function getProductIdByInventoryBarcode($barcode = '') {
        if ($this->_isModuleInventoryEnabled == true) {
            $barcodeCol = Mage::getModel('inventorybarcode/barcode')->getCollection()
                    ->addFieldToFilter('barcode', $barcode);
            $barcode = $barcodeCol->getFirstItem();
            if ($productId = $barcode->getProductEntityId())
                return $productId;
            else
                return 0;
        }
        return 0;
    }

    public function getInventoryBarcodeByProductId($product_id = '') {
        if ($this->_isModuleInventoryEnabled == true) {
            $barcodeCol = Mage::getModel('inventorybarcode/barcode')->getCollection()
                    ->addFieldToFilter('product_entity_id', $product_id);
            if (count($barcodeCol) > 0) {
                $barcodes = array();
                foreach ($barcodeCol as $barcodeItem) {
                    $barcodes[] = $barcodeItem->getBarcode();
                }
                return $barcodes;
            }
            return 0;
        }
        return 0;
    }

}
