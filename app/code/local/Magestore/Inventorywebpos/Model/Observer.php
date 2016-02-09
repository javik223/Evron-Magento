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
 * @package     Magestore_Inventorywebpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywebpos_Model_Observer {

    /**
     * process controller_action_predispatch_webpos_index_productsearch event
     *
     * @return Magestore_Inventorywebpos_Model_Observer
     */
    public function initProductSearch($observer) {
        $keyword = Mage::app()->getRequest()->getPost('keyword');
        $barcode = Mage::getModel('inventorybarcode/barcode')->load($keyword, 'barcode');
        $result = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $showOutofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock', $storeId);
        $productBlock = Mage::getBlockSingleton('catalog/product_list');
        if ($barcode->getId()) {
            $productId = $barcode->getProductEntityId();
            $product = Mage::getModel('catalog/product')->load($productId);
            $addToCart = $productBlock->getAddToCartUrl($product) . 'tempadd/1';
            $result[] = $productId;
            $html = '';
            $html .= '<ul>';
            $html .= '<li id="sku_only" url="' . $addToCart . '" onclick="setLocation(\'' . $addToCart . '\')">';
            $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
            $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
            if ($showOutofstock) {
                $html .= '<br />';
                if ($product->isAvailable()) {
                    $html .= '<p class="availability in-stock">' . Mage::helper('inventorywebpos')->__('Availability:') . '<span>' . Mage::helper('inventorywebpos')->__('In stock') . '</span></p><div style="clear:both"></div>';
                } else {
                    $html .= '<p class="availability out-of-stock">' . Mage::helper('inventorywebpos')->__('Availability:') . '<span>' . Mage::helper('inventorywebpos')->__('Out of stock') . '</span></p><div style="clear:both"></div>';
                }
            }
            $html .= '</li>';
            $html .= '</ul>';
            echo $html;
            return;
        } else {
            $searchInstance = new Magestore_Inventorywebpos_Model_Search_Barcode();
            $results = $searchInstance->setStart(1)
                    ->setLimit(10)
                    ->setQuery($keyword)
                    ->load()
                    ->getResults();

            if (count($results)) {
                $html = '';
                $html .= '<ul>';
                foreach ($results as $item) {
                    $productId = $item['product_id'];
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $addToCart = $productBlock->getAddToCartUrl($product) . 'tempadd/1';
                    $result[] = $product->getId();
                    $html .= '<li onclick="setLocation(\'' . $addToCart . '\')">';
                    $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
                    $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
                    if ($showOutofstock) {
                        $html .= '<br />';
                        if ($product->isAvailable()) {
                            $html .= '<p class="availability in-stock">' . Mage::helper('inventorywebpos')->__('Availability:') . '<span>' . Mage::helper('inventorywebpos')->__('In stock') . '</span></p><div style="clear:both"></div>';
                        } else {
                            $html .= '<p class="availability out-of-stock">' . Mage::helper('inventorywebpos')->__('Availability:') . '<span>' . Mage::helper('inventorywebpos')->__('Out of stock') . '</span></p><div style="clear:both"></div>';
                        }
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
                echo $html;
                return;
            }
        }
    }

    public function webposBlockListproductEvent($observer) {
        $show_outofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock');
        $warehouseSelected = Mage::helper('inventorywebpos')->_getCurrentWarehouseId();
        $coreResource = Mage::getSingleton('core/resource');
        $readConnection = $coreResource->getConnection('core_read');
        $collection = $observer->getEvent()->getPosGetProductColection();
        $tempTableArr = array('warehouse_products_temp_table');
        Mage::helper('inventorywebpos')->removeTempTables($tempTableArr);
        $w_productCol = Mage::helper('inventorywebpos')->getWarehouseProductCollection($warehouseSelected);
        Mage::helper('inventorywebpos')->createTempTable('warehouse_products_temp_table', $w_productCol);
        $collection->getSelect()
                ->joinLeft(
                        array('warehouse_product' => $coreResource->getTableName('warehouse_products_temp_table')), "e.entity_id=warehouse_product.product_id", array('*'));
        $sql = "SELECT DISTINCT(`parent_id`) FROM " . $coreResource->getTableName('catalog/product_super_link') . " as `product_sl`";
        $sql .= " JOIN " . $coreResource->getTableName('warehouse_products_temp_table') . " as `wp_temp`";
        $sql .= " ON product_sl.product_id = wp_temp.product_id";
        if (!$show_outofstock) {
            $sql .= " AND wp_temp.available_qty > 0 ";
            $parent_ids = $readConnection->fetchAll($sql);
            $parentIdArr = array();
            foreach ($parent_ids as $parent_id) {
                $parentIdArr[] = $parent_id['parent_id'];
            }
            $parentIdStr = implode(',', $parentIdArr);
            if ($parentIdStr) {
                $collection->getSelect()->where("e.entity_id IN({$parentIdStr}) OR warehouse_product.available_qty > 0");
            } else {
                $collection->getSelect()->where("warehouse_product.available_qty > 0");
            }
        }
    }

    //add webpos permission tab
    public function addWarehouseTab($observer) {
        if (Mage::helper('inventoryplus')->isWebPOS20Active()) {
            $warehouseId = $observer->getWarehouseId();
            if (!$warehouseId)
                return;
            $tab = $observer->getTab();
            $tab->addTab('webpos_permission_section', array(
                'label' => Mage::helper('inventoryplus')->__('WebPOS Users'),
                'title' => Mage::helper('inventoryplus')->__('WebPOS Users'),
                'url' => $tab->getUrl('*/inwe_warehouse/webpospermissions', array(
                    '_current' => true,
                    'id' => $tab->getRequest()->getParam('id'),
                    'store' => $tab->getRequest()->getParam('store')
                )),
                'class' => 'ajax',
            ));
        }
        return;
    }

    /**
     * Convert location of webpos to warehouse
     *
     * @param $observer
     * @throws Exception
     */
    public function convertWebposLocationToWarehouse($observer) {
        if (Mage::helper('inventoryplus')->isWebPOS20Active()) {
            // Get primary warehouse
            $primaryWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
                    ->addFieldToFilter('is_root', array('eq' => 1))
                    ->getFirstItem();

            $existingWebposUsers = Mage::getModel('webpos/user')->getCollection();
            foreach ($existingWebposUsers as $existingWebposUser) {
                $newWarehouseWebposUser = Mage::getModel('inventorywebpos/webposuser');
                $newWarehouseWebposUser->setUserId($existingWebposUser->getUserId())
                        ->setWarehouseId($primaryWarehouse->getId())
                        ->setCanCreateShipment(1);

                try {
                    $newWarehouseWebposUser->save();
                } catch (Exception $ex) {
                    
                }
            }

            $webposUserLocation = Mage::getModel('webpos/userlocation')->getCollection();
            $currentAdmin = Mage::getSingleton('admin/session')->getUser();
            if ($webposUserLocation->getFirstItem()->getId()) {
                $countryDefault = Mage::getStoreConfig('general/country/default');
                foreach ($webposUserLocation as $location) {
                    $newConvertWarehouse = Mage::getModel('inventoryplus/warehouse');
                    $newConvertWarehouse->setWarehouseName($location->getDisplayName());
                    $newConvertWarehouse->setStatus(1);
                    if ($currentAdmin->getId()) {
                        $newConvertWarehouse->setCreatedBy($currentAdmin->getUsername());
                    } else {
                        $newConvertWarehouse->setCreatedBy(Mage::helper('inventoryplus')->__('Webpos Location'));
                    }
                    $newConvertWarehouse->setCreatedAt(now());
                    $newConvertWarehouse->setCountryId($countryDefault);
                    $newConvertWarehouse->save();

                    if ($newConvertWarehouse->getId()) {
                        $permission = Mage::getModel('inventoryplus/warehouse_permission');
                        $permission->setData('warehouse_id', $newConvertWarehouse->getId())
                                ->setData('admin_id', $currentAdmin->getId())
                                ->setData('can_edit_warehouse', 1)
                                ->setData('can_adjust', 1);
                        try {
                            $permission->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }
        }
    }

    /**
     * Redirect webpos/userlocation/index to inventoryplus/warehouse/index
     * 
     */
    public function controller_action_predispatch_webpos_userlocation_index($observer) {
        $controllerAction = Mage::app()->getRequest()->getControllerName() . '/' . Mage::app()->getRequest()->getActionName();
        if ($controllerAction != 'userlocation/index')
            return;

        $url = Mage::getSingleton("adminhtml/url")->getUrl("adminhtml/inp_warehouse/index", array("_secure" => true));
        Mage::app()->getResponse()->setRedirect($url)->sendResponse();
        exit;
    }

    /**
     * Catch event inp_get_current_username to switch to webpos user
     * 
     * @param type $observer
     */
    public function inp_get_current_username($observer) {
        $user = $observer->getEvent()->getUser();
        if ($user->getUsername()) {
            return;
        }
        if (!Mage::helper('inventoryplus')->isWebPOS20Active()) {
            return;
        }
        if ($curUser = Mage::getSingleton('webpos/session')->getUser()) {
            $user->setUsername($curUser->getUsername());
        }
    }

    public function inp_prepare_post_creditmemo_data($observer) {
        $dataObject = $observer->getEvent()->getCreditmemoData();
        if (!$dataObject->getQty() || strpos($dataObject->getQty(), '$refund$') < 0) {
            return;
        }
        $qtyEx = explode('$refund$', $dataObject->getQty());
        $stockEx = explode('$refund$', $dataObject->getStock());
        $tempData = array();
        $creditmemoData = array();
        for ($i = 0; $i < count($qtyEx); $i++) {
            $anItemQty = explode('/', $qtyEx[$i]);
            $anItemStock = explode('/', $stockEx[$i]);
            if (isset($anItemQty[1]))
                $tempData[$i]['qty'] = $anItemQty[1];
            if (isset($anItemQty[0]) && $anItemQty[0] != '')
                $tempData[$i]['order_item_id'] = $anItemQty[0];
            if (isset($anItemStock[1]))
                $tempData[$i]['back_to_stock'] = ($anItemStock[1] == 'true' ? 1 : 0);
        }

        if (count($tempData)) {
            $creditmemoData['items'] = array();
            foreach ($tempData as $itemData) {
                $creditmemoData['items'][$itemData['order_item_id']] = array('back_to_stock' => $itemData['back_to_stock'],
                    'qty' => $itemData['qty']);
            }
        }
        $creditmemoData['select-warehouse-supplier'] = 1;
        $creditmemoData['warehouse-select'] = Mage::helper('inventorywebpos')->_getCurrentWarehouseId();
        $dataObject->setCreditmemo($creditmemoData);
    }

}
