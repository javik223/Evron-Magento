<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Magestore_Inventoryplus_Model_Catalog_Convert_Adapter_Product extends Mage_Catalog_Model_Convert_Adapter_Product
{
	protected $_listNewAdjustStock = array();
	protected $_warehouseImports = array();
	protected $_loopStep = 1;
	protected $_newRootAdjust;		/* Storage ID of new adjust stock for root warehouse */
	
    public function saveRow(array $importData)
    {
        $product = $this->getProductModel()
            ->reset();                                        
        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'store');
                Mage::throwException($message);
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skipping import row, store "%s" field does not exist.', $importData['store']);
            Mage::throwException($message);
        }
        
        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $product_id = $product->getIdBySku($importData['sku']);

        if ($product_id) {
            $product->load($product_id);
        } else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();
            
            /**
             * Check product define type
             */
            
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                Mage::throwException($message);
            } 
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, the value "%s" is invalid for field "%s"', $value, 'attribute_set');
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" for new products is not defined.', $field);
                    Mage::throwException($message);
                }
            }
        }   
		
        $this->setProductTypeInstance($product);
        
        if (isset($importData['category_ids'])) {   
            $product->setCategoryIds($importData['category_ids']);
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds) || !$store->getId()) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                } catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }
        
        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (is_null($value)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $setValue = false;
                    foreach ($options as $item) {
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subValue) {
                                if (isset($subValue['value']) && $subValue['value'] == $value) {
                                    $setValue = $value;
                                }
                            }
                        } else if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $product->setData($field, $setValue);
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                } else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);

        $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();

        $arrayToMassAdd = array();

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            if (isset($importData[$mediaAttributeCode])) {
                $file = trim($importData[$mediaAttributeCode]);
                if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                    $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                }
            }
        }

        $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
            $product,
            $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import',
            false,
            false
        );

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            $addedFile = '';
            if (isset($importData[$mediaAttributeCode . '_label'])) {
                $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                if (isset($importData[$mediaAttributeCode])) {
                    $keyInAddedFile = array_search($importData[$mediaAttributeCode],
                        $addedFilesCorrespondence['alreadyAddedFiles']);
                    if ($keyInAddedFile !== false) {
                        $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                    }
                }

                if (!$addedFile) {
                    $addedFile = $product->getData($mediaAttributeCode);
                }
                if ($fileLabel && $addedFile) {
                    $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
                }
            }
        }

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->save();
		/* Inventoryplus - Import Dataflow integrating */    
        if(!(isset($importData['type']) && in_array($importData['type'], array('configurable', 'group' , 'bundle')) || in_array($product->getProductTypes(), array('configurable', 'group' , 'bundle')))){
			try{
				$imHelper = Mage::helper('inventoryplus');
				$admin = Mage::getModel('admin/session')->getUser()->getUsername();
				$rootWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
							->addFieldToFilter('is_root',1)->getFirstItem();
				if(!$rootWarehouse->getId()){
					$rootWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
							->addFieldToFilter('status',1)->getFirstItem();
				}      
				$product_id = $product->getEntityId();           
				$oldQty = Mage::getModel('cataloginventory/stock_item')
						->loadByProduct($product_id)
						->getQty();
				$newQty = $importData['qty'];
				if($this->_loopStep==1){						/* The first step in loop */
					$warehouseCol = Mage::getModel('inventoryplus/warehouse')->getCollection();
					foreach($warehouseCol as $warehouse){		/* Create Adjust Stock for each warehouse */
						$f_warehouseId = $warehouse->getId();
						$element = 'warehouse_'.$f_warehouseId;
						if(isset($importData[$element])){				/* If warehouse is in csv file */
							$this->_warehouseImports[] = $f_warehouseId;	/* Get all warehouse IDs in csv file. */
							/* Create adjust stock here */
							$model = Mage::getModel('inventoryplus/adjuststock');
							$model->setData('warehouse_id',$f_warehouseId);
							$model->setData('warehouse_name',$warehouse->getWarehouseName());
							$model->setData('reason',$imHelper->__('Stock Ajustment available qty in System => Import/Export => Dataflow - Profiles'));
							$model->setData('created_by',$admin);
							$model->setData('created_at',now());
							$model->setData('confirmed_by',$admin);
							$model->setData('confirmed_at',now());
							$model->setData('status',1);
							$model->save();
							$this->_listNewAdjustStock[] = $model->getId();
							/* Endl Create adjust stock */	
						}
					}
				}
				if(isset($this->_warehouseImports) && $this->_warehouseImports){	/* From step one to end */	 
					/* Update qty warehouse from csv file */
					$resource = Mage::getSingleton('core/resource');
					$readConnection = $resource->getConnection('core_read');
					$writeConnection = $resource->getConnection('core_write');
					foreach($this->_warehouseImports as $warehouseId){
						$qty = $importData['warehouse_'.$warehouseId];
						$firstSql = 'SELECT * from ' . $resource->getTableName("erp_inventory_warehouse_product") . ' WHERE warehouse_id = '.$warehouseId.' AND product_id = '.$product_id;
						$results = $readConnection->fetchAll($firstSql);
						if(count($results)==0){		/* Create new product => insert into warehouse product table */
							$secondSql = 'INSERT INTO '.$resource->getTableName("erp_inventory_warehouse_product") . ' (	warehouse_id,product_id,total_qty,available_qty) VALUES ('.$warehouseId.','.$product_id.','.$qty.','.$qty.');';
							$writeConnection->query($secondSql);
							$oldAvailQty = $oldTotalQty = 0;
							$difference = $qty;
							$newTotalQty = $oldTotalQty + $difference;
						}else{						/* Update quantity of product in warehouse  */
							$oldAvailQty = $results[0]['available_qty'];
							$difference = $qty - $results[0]['available_qty'];
							$oldTotalQty = $results[0]['total_qty'];
							$newTotalQty = $oldTotalQty + $difference;
							$thirdSql = 'UPDATE ' . $resource->getTableName('erp_inventory_warehouse_product')
				. " SET `available_qty` = $qty, `total_qty` = $newTotalQty "
				. "WHERE warehouse_id = $warehouseId AND product_id =  $product_id;";
							$writeConnection->query($thirdSql);								
						}
						/* Create adjust stock product here */
						$adjustCol = Mage::getModel('inventoryplus/adjuststock')->getCollection();
						$adjustCol->addFieldToFilter('adjuststock_id',array("IN"=>$this->_listNewAdjustStock));
						$adjustCol->addFieldToFilter('warehouse_id',$warehouseId);
						$adjustedModel = $adjustCol->getFirstItem();
						$adjustProduct = Mage::getModel('inventoryplus/adjuststock_product');
						$adjustProduct->setData('adjuststock_id',$adjustedModel->getId());
						$adjustProduct->setData('product_id',$product_id);
						$adjustProduct->setData('old_qty',$oldTotalQty);
						$adjustProduct->setData('suggest_qty',$newTotalQty);
						$adjustProduct->setData('adjust_qty',$newTotalQty);
						$adjustProduct->save();
						/* Endl create adjust stock product */
					}
					/* Re-fix qty catalog by total available qty in warehouse */
						$fourthSql = 'SELECT SUM(available_qty) as total_avail_qty from ' . $resource->getTableName("erp_inventory_warehouse_product") . ' WHERE product_id = '.$product_id;
						$results = $readConnection->fetchAll($fourthSql);
						$importData['qty']	= $results[0]['total_avail_qty'];
					/* Endl Re-fix qty catalog by total available qty in warehouse */
				}
				/* Does not have qty warehouse column. But has qty column */
				$createAdjust = false;
				if((!isset($this->_warehouseImports) || count($this->_warehouseImports)==0) && $importData['qty']){
					$warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
												->getCollection()
												->addFieldToFilter('product_id',$product_id)
												->getFirstItem();
					if(!$warehouseProduct->getId()){		/* Add new product */
						Mage::getModel('inventoryplus/warehouse_product')
								->setData('warehouse_id',$rootWarehouse->getId())
								->setData('product_id',$product_id)
								->setData('total_qty',$newQty)
								->setData('available_qty',$newQty)
								->save();
						$oldAvailQty = $oldTotalQty = 0;
						$newTotalQty =	$newAvailQty = $newQty;
						$createAdjust = true;
					}else{						/* Update total Qty and Available Qty for is_root warehouse */
						if(floatval($newQty) != floatval($oldQty)){
							$oldTotalQty = $warehouseProduct->getTotalQty();
							$oldAvailQty = $warehouseProduct->getAvailableQty();
							$difference = $newQty-$oldQty;
							$newTotalQty =	$oldTotalQty + $difference;
							$newAvailQty = $oldAvailQty + $difference;
							$warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
												->getCollection()
												->addFieldToFilter('warehouse_id',$rootWarehouse->getId())
												->addFieldToFilter('product_id',$product_id)
												->getFirstItem();
							$warehouseProduct->setData('total_qty',$newTotalQty)
											 ->setData('available_qty',$newAvailQty)
											 ->save();								
							$createAdjust = true;
						}
					}
					if($createAdjust == true){
						if($this->_loopStep==1){
							/* Create adjust stock here */
							$adjustModel = Mage::getModel('inventoryplus/adjuststock');
							$adjustModel->setData('warehouse_id',$rootWarehouse->getId());
							$adjustModel->setData('warehouse_name',$rootWarehouse->getWarehouseName());
							$adjustModel->setData('reason',$imHelper->__('Stock Ajustment available qty in System => Import/Export  => Dataflow - Profiles'));
							$adjustModel->setData('created_by',$admin);
							$adjustModel->setData('created_at',now());
							$adjustModel->setData('confirmed_by',$admin);
							$adjustModel->setData('confirmed_at',now());
							$adjustModel->setData('status',1);
							$adjustModel->save();
							$this->_newRootAdjust = $adjustModel;
							/* Endl create adjust stock*/
						}	
						/* Create adjust stock product here */
						$_adjustProduct = Mage::getModel('inventoryplus/adjuststock_product');
						$_adjustProduct->setData('adjuststock_id',$this->_newRootAdjust->getId());
						$_adjustProduct->setData('product_id',$product_id);
						$_adjustProduct->setData('old_qty',$oldAvailQty);
						$_adjustProduct->setData('suggest_qty',$newAvailQty);
						$_adjustProduct->setData('adjust_qty',$newAvailQty);
						$_adjustProduct->save();
						/* Endl create adjust stock product */
					}
				}
				if(isset($importData['suppliers'])){
					/* Insert products into supplier's product */
					$suppliers = explode(",",$importData['suppliers']);
					foreach($suppliers as $supplierId){
						$resource = Mage::getSingleton('core/resource');
						$readConnection = $resource->getConnection('core_read');
						$writeConnection = $resource->getConnection('core_write');
						$sql = 'SELECT * from ' . $resource->getTableName("erp_inventory_supplier_product") . ' WHERE (supplier_id = '.$supplierId.' AND product_id = '.$product_id.')';
						$results = $readConnection->fetchAll($sql);
						if(count($results)==0){
							$sqlInsert = 'INSERT INTO '.$resource->getTableName("erp_inventory_supplier_product") . ' (	supplier_id,product_id) VALUES ('.$supplierId.','.$product_id.');';
							$writeConnection->query($sqlInsert);
						}
					}
					/* Endl Insert products into supplier's product */
				}
			$this->_loopStep++;
			}catch(Exception $e){echo $e->getMessage();die('----'.$product_id);}
        }
		/* Endl Inventoryplus - Import Dataflow integrating */	
        // Store affected products ids
        $this->_addAffectedEntityIds($product->getId());

        return true;
    }
}
