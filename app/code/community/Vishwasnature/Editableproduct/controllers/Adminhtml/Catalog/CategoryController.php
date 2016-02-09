<?php
require_once "Mage/Adminhtml/controllers/Catalog/CategoryController.php";
class Vishwasnature_Editableproduct_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController {

    public function editablegridAction() {
        if (!$category = $this->_initCategory(true)) {
            return;
        }
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('editableproduct/adminhtml_catalog_category_tab_editableproduct', 'category.editableproduct.grid')
                        ->toHtml()
        );
    }

    public function editproductAction() {
        if ($editableValue = $this->getRequest()->getParam('editable_value')) {
            try {
                $attrinfo = $this->getRequest()->getParam('attrinfo');
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id_info'));
                if ($product->getId()) {
                    if ($attrinfo == 'name') {
                        $product->setName($editableValue);
                    }
                    if ($attrinfo == 'sku') {
                        $product->setSku($editableValue);
                    }
                    if ($attrinfo == 'price') {
                        $product->setPrice($editableValue);
                    }
                    if ($attrinfo == 'special_price') {
                        $product->setSpecialPrice($editableValue);
                    }
                    if ($attrinfo == 'qty') {
                        $product->setStockData(array(
                       'qty' => $editableValue //qty
                        ));
                    }
                    if ($attrinfo == 'status') {
                        $product->setStatus($editableValue);
                    }
                    $product->save();
                    echo json_encode(array('status' => 'success', 'message' => 'Product has been updated'));
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                echo json_encode(array('status' => 'error','message'=> $e->getMessage()));
            }
        }
    }

}
?>
