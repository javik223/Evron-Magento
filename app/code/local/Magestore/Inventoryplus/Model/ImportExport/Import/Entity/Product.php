<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import entity product model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestore_Inventoryplus_Model_ImportExport_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{    
	protected $_newRootAdjust;		/* Storage ID of new adjust stock for root warehouse */
    /**
     * Stock item saving.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _saveStockItem()
    {
        $defaultStockData = array(
            'manage_stock'                  => 1,
            'use_config_manage_stock'       => 1,
            'qty'                           => 0,
            'min_qty'                       => 0,
            'use_config_min_qty'            => 1,
            'min_sale_qty'                  => 1,
            'use_config_min_sale_qty'       => 1,
            'max_sale_qty'                  => 10000,
            'use_config_max_sale_qty'       => 1,
            'is_qty_decimal'                => 0,
            'backorders'                    => 0,
            'use_config_backorders'         => 1,
            'notify_stock_qty'              => 1,
            'use_config_notify_stock_qty'   => 1,
            'enable_qty_increments'         => 0,
            'use_config_enable_qty_inc'     => 1,
            'qty_increments'                => 0,
            'use_config_qty_increments'     => 1,
            'is_in_stock'                   => 0,
            'low_stock_date'                => null,
            'stock_status_changed_auto'     => 0,
            'is_decimal_divided'            => 0
        );
		
        $entityTable = $this->getResourceModel('cataloginventory/stock_item')->getMainTable();
        $helper      = $this->getHelper('catalogInventory');
		/* Start of customization */
		$imHelper = Mage::helper('inventoryplus');
		$loopStep = 1;
		$admin = Mage::getModel('admin/session')->getUser()->getUsername();
		$rootWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
					->addFieldToFilter('is_root',1)->getFirstItem();
		if(!$rootWarehouse->getId()){
			$rootWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
					->addFieldToFilter('status',1)->getFirstItem();
		}
		/* End step one of customization */
        while ($bunch = $this->getNextBunch()) {
            $stockData = array();
            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                $this->_filterRowData($rowData);
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                // only SCOPE_DEFAULT can contain stock data
                if (self::SCOPE_DEFAULT != $this->getRowScope($rowData)) {
                    continue;
                }

                $row = array();
                $row['product_id'] = $this->_newSku[$rowData[self::COL_SKU]]['entity_id'];
                $row['stock_id'] = 1;

                /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                $stockItem = $this->getModel('cataloginventory/stock_item');
                $stockItem->loadByProduct($row['product_id']);
                $existStockData = $stockItem->getData();
				$oldQty = $existStockData['qty'];		/* Customization - Get old catalog qty of product */
                $row = array_merge(
                    $defaultStockData,
                    array_intersect_key($existStockData, $defaultStockData),
                    array_intersect_key($rowData, $defaultStockData),
                    $row
                );
				/* Customization - Change quantity in warehouses */
				try{
					$productId = $row['product_id'];					
					if($loopStep==1){						/* The first step in loop */	
						if(!isset($warehouseImports)){		
							$warehouseImports = array();
							$warehouseCol = Mage::getModel('inventoryplus/warehouse')->getCollection();
							$adjustmentArr = array();
							foreach($warehouseCol as $warehouse){		/* Create Adjust Stock for each warehouse */
								$f_warehouseId = $warehouse->getId();
								$element = 'warehouse_'.$f_warehouseId;
								if(isset($rowData[$element])){				/* If warehouse is in csv file */
									$warehouseImports[] = $f_warehouseId;	/* Get all warehouse IDs in csv file. */
									/* Create adjust stock here */
									$model = Mage::getModel('inventoryplus/adjuststock');
									$model->setData('warehouse_id',$f_warehouseId);
									$model->setData('warehouse_name',$warehouse->getWarehouseName());
									$model->setData('reason',$imHelper->__('Stock Ajustment available qty in System => Import/Export'));
									$model->setData('created_by',$admin);
									$model->setData('created_at',now());
									$model->setData('confirmed_by',$admin);
									$model->setData('confirmed_at',now());
									$model->setData('status',1);
									$model->save();
									$adjustmentArr[] = $model->getId();
									/* Endl Create adjust stock */	
								}
							}
						}
					}
					if(isset($warehouseImports) && $warehouseImports){	/* From step one to end */	 
						/* Update qty warehouse from csv file */
						$resource = Mage::getSingleton('core/resource');
						$readConnection = $resource->getConnection('core_read');
						$writeConnection = $resource->getConnection('core_write');
						foreach($warehouseImports as $warehouseId){
							$qty = $rowData['warehouse_'.$warehouseId];
							$firstSql = 'SELECT * from ' . $resource->getTableName("erp_inventory_warehouse_product") . ' WHERE warehouse_id = '.$warehouseId.' AND product_id = '.$productId;
							$results = $readConnection->fetchAll($firstSql);
							if(count($results)==0){		/* Create new product => insert into warehouse product table */
								$secondSql = 'INSERT INTO '.$resource->getTableName("erp_inventory_warehouse_product") . ' (	warehouse_id,product_id,total_qty,available_qty) VALUES ('.$warehouseId.','.$productId.','.$qty.','.$qty.');';
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
					. "WHERE warehouse_id = $warehouseId AND product_id =  $productId;";
								$writeConnection->query($thirdSql);								
							}
							/* Create adjust stock product here */
							$adjustCol = Mage::getModel('inventoryplus/adjuststock')->getCollection();
							$adjustCol->addFieldToFilter('adjuststock_id',array("IN"=>$adjustmentArr));
							$adjustCol->addFieldToFilter('warehouse_id',$warehouseId);
							$adjustedModel = $adjustCol->getFirstItem();
							$adjustProduct = Mage::getModel('inventoryplus/adjuststock_product');
							$adjustProduct->setData('adjuststock_id',$adjustedModel->getId());
							$adjustProduct->setData('product_id',$productId);
							$adjustProduct->setData('old_qty',$oldTotalQty);
							$adjustProduct->setData('suggest_qty',$newTotalQty);
							$adjustProduct->setData('adjust_qty',$newTotalQty);
							$adjustProduct->save();
							/* Endl create adjust stock product */
						}
						/* Re-fix qty catalog by total available qty in warehouse */
							$fourthSql = 'SELECT SUM(available_qty) as total_avail_qty from ' . $resource->getTableName("erp_inventory_warehouse_product") . ' WHERE product_id = '.$productId;
							$results = $readConnection->fetchAll($fourthSql);
							$row['qty']	= $results[0]['total_avail_qty'];
						/* Endl Re-fix qty catalog by total available qty in warehouse */
					}
				}catch(Exception $e){echo $e->getMessage();die('----');}
				/* End Change quantity in warehouses */
                $stockItem->setData($row);
                unset($row);
                if ($helper->isQty($this->_newSku[$rowData[self::COL_SKU]]['type_id'])) {
                    if ($stockItem->verifyNotification()) {
                        $stockItem->setLowStockDate(Mage::app()->getLocale()
                            ->date(null, null, null, false)
                            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
                        );
                    }
                    $stockItem->setStockStatusChangedAutomatically((int) !$stockItem->verifyStock());
                } else {
                    $stockItem->setQty(0);
                }
				/* Customization - For Magestore Inventory Management */  
				$stockData[] = $newStock = $stockItem->unsetOldData()->getData();	
				try{
					/* Does not have qty warehouse column. But has qty column */
					$newQty = $newStock['qty'];
					$createAdjust = false;
					if((!isset($warehouseImports) || count($warehouseImports)==0) && $rowData['qty']){	
						$warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
													->getCollection()
													->addFieldToFilter('product_id',$productId)
													->getFirstItem();
						if(!$warehouseProduct->getId()){		/* Add new product */
							Mage::getModel('inventoryplus/warehouse_product')
									->setData('warehouse_id',$rootWarehouse->getId())
									->setData('product_id',$productId)
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
													->addFieldToFilter('product_id',$productId)
													->getFirstItem();
								$warehouseProduct->setData('total_qty',$newTotalQty)
												 ->setData('available_qty',$newAvailQty)
												 ->save();								
								$createAdjust = true;
							}
						}
						if($createAdjust == true){
							if($loopStep==1){
								/* Create adjust stock here */
								$adjustModel = Mage::getModel('inventoryplus/adjuststock');
								$adjustModel->setData('warehouse_id',$rootWarehouse->getId());
								$adjustModel->setData('warehouse_name',$rootWarehouse->getWarehouseName());
								$adjustModel->setData('reason',$imHelper->__('Stock Ajustment available qty in System => Import/Export'));
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
							$_adjustProduct->setData('product_id',$productId);
							$_adjustProduct->setData('old_qty',$oldAvailQty);
							$_adjustProduct->setData('suggest_qty',$newAvailQty);
							$_adjustProduct->setData('adjust_qty',$newAvailQty);
							$_adjustProduct->save();
							/* Endl create adjust stock product */
						}
					}
					if(isset($rowData['suppliers'])){
						/* Insert products into supplier's product */
						$suppliers = explode(",",$rowData['suppliers']);
						foreach($suppliers as $supplierId){
							$resource = Mage::getSingleton('core/resource');
							$readConnection = $resource->getConnection('core_read');
							$writeConnection = $resource->getConnection('core_write');
							$sql = 'SELECT * from ' . $resource->getTableName("erp_inventory_supplier_product") . ' WHERE (supplier_id = '.$supplierId.' AND product_id = '.$productId.')';
							$results = $readConnection->fetchAll($sql);
							if(count($results)==0){
								$sqlInsert = 'INSERT INTO '.$resource->getTableName("erp_inventory_supplier_product") . ' (	supplier_id,product_id) VALUES ('.$supplierId.','.$productId.');';
								$writeConnection->query($sqlInsert);
							}
						}
						/* Endl Insert products into supplier's product */
					}
				}catch(Exception $e){echo $e->getMessage();die('----');}
				$loopStep++;	
            }
			/* Endl Custom For Magestore Inventory Management - Magnus */
            // Insert rows
            if ($stockData) {
                $this->_connection->insertOnDuplicate($entityTable, $stockData);
            }
        }
        return $this;
    }  
}
