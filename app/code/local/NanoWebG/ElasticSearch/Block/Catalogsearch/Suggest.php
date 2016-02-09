<?php
/**
 * Renders the recommendations in the catalog search results.
 *
 * @category    NanoWebGroup
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.0
 * @copyright   
 */
class NanoWebG_ElasticSearch_Block_Catalogsearch_Suggest extends Mage_CatalogSearch_Block_Result
{
    /**
     * @var array recommendations list.
     */
    protected $_recommendations = null;

    /**
     * Gets the search engine object.
     *
     * @return NanoWebG_ElasticSearch_Model_Resource_ElasticSearch_Engine
     */
    protected function _getEngine()
    {
        return $this->helper('catalogsearch')->getEngine();
    }
    
     /**
     * Returns one recommendation.
     *
     * @return string
     */
    public function getRecommendation()
    {
        $recommendations = $this->getRecommendations();

        return !empty($recommendations) ? $recommendations[0] : '';
    }

    /**
     * Builds search URL text query.
     *
     * @param $q
     * @return string
     */
    public function getQuery($q)
    {
        return $this->getUrl('catalogsearch/result', array('_query' => array('q' => $q)));
    }

   
    /**
     * Recommends better queries based on current text query.
     *
     * @return array
     */
    public function getRecommendations()
    {
        if (is_array($this->_recommendations)) {
            return $this->_recommendations;
        }

        $recommendations = array();
        $engine = $this->_getEngine();
        if ($engine instanceof NanoWebG_ElasticSearch_Model_Resource_ElasticSearch_Engine) {
            $q = $this->helper('catalogsearch')->getQueryText();
            $recommendations = $engine->suggest($q);

        }

        $this->_recommendations = $recommendations;

        return $this->_recommendations;
    }
}