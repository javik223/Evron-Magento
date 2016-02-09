<?php
/**
 * 
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   Copyright (c) 2014 Nanowebgroup (http://www.nanowebgroup.com)
 */
class NanoWebG_ElasticSearch_Model_Catalogsearch_Layer_Filter_Attribute extends NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute
{
    protected function _getIsFilterabled($attributes)
    {
        return $attributes->getIsFilterableInSearch();
    }
}
