<?php
/**
 * Filters the boolean attributes in the layered navigation.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Boolean extends NanoWebG_ElasticSearch_Model_Catalog_Layer_Filter_Attribute
{
    /**
     * Returns facets data.
     *
     * @return array
     */
    protected function _getAllFacets()
    {
        $facets = parent::_getAllFacets();
        $results = array();
        foreach ($facets as $key => $value) {
            $index = 0; // false by default
            if ($key === 'true' || $key === 'T' || $key === '1' || $key === 1 || $key === true) {
                $index = 1;
            }
            $results[$index] = $value;
        }

        return $results;
    }

    /**
     * Checks if filter is valid.
     *
     * @param string $filter
     * @return bool
     */
    protected function _isFilter($filter)
    {
        return $filter === '0' || $filter === '1' || false === $filter || true === $filter;
    }
}
