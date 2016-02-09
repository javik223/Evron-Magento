<?php

class SSTech_Categorygridfilter_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const XML_PATH_CATEGORY_COLUMN_ENABLED = 'categorygridfilter/settings/category_column_enabled';
    const XML_PATH_THUMBNAIL_COLUMN_ENABLED = 'categorygridfilter/settings/thumbnail_column_enabled';
    const XML_PATH_THUMBNAIL_WIDTH = 'categorygridfilter/settings/thumbnail_width';
    
    
    public function _getConfig($path){        
        return Mage::getStoreConfig($path);
    }
    
   
    
    public function isCategoryEnabled(){
        return $this->_getConfig(self::XML_PATH_CATEGORY_COLUMN_ENABLED);
    }
    
    public function isThumbnailEnabled(){
        return $this->_getConfig(self::XML_PATH_THUMBNAIL_COLUMN_ENABLED);
    }
    
    public function getThumbnailWidth(){
         return $this->_getConfig(self::XML_PATH_THUMBNAIL_WIDTH);
    }
}