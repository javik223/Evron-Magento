<?php
/**
 * @author Adam 07/07/2015
 * return array
 * all the product attributes that can be used to search
 */
class Magestore_Webpos_Model_Source_Adminhtml_Productattribute extends Varien_Object {
    static public function toOptionArray() {
		/*
        $attributes = Mage::getSingleton('catalogsearch/advanced')->getAttributes();
        $options = array();
        foreach($attributes as $attribute){
            $options[] = array('value' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel());
        }
        return $options;
		*/
		$result = null;
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();
        if ($attributes != null && $attributes->count() > 0):
            $result[] = array('value' => 'entity_id' ,'label' => 'ID');
            foreach ($attributes as $item):
                 $result[] = array('value' => $item->getAttributeCode(), 'label' => Mage::helper('webpos')->__($item->getFrontendLabel()));
            endforeach;
        endif;
        return $result;
    }
}
