<?php
/**
 * 
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * 
 */
class NanoWebG_ElasticSearch_Helper_Elasticsearch extends NanoWebG_ElasticSearch_Helper_Data
{
    
    /**
     * Get Elasticsearch engine configuration data.
     *
     * @param string $prefix
     * @param mixed $store
     * @return array
     */
    public function getEngineConfig($prefix = '', $store = null)
    {
        $config = parent::getEngineConfig('elasticsearch_',$store);

        return $config;
    }

    /**
     * Escapes characters from specified value.
     *
     * @link http://lucene.apache.org/core/4_7_0/queryparser/index.html
     * @param string $value
     * @return mixed
     */
    public function _escapeChar($value)
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        $value = preg_replace($pattern, $replace, $value);
        $value = str_replace('/', '\\\/', $value);

        return $value;
    }

    
    /**
     * Escapes entire specified phrase.
     *
     * @param string $value
     * @return string
     */
    public function _escapePhrase($value)
    {
        $pattern = '/("|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * Adds quotes around the phrase.
     *
     * @param string $value
     * @return string
     */
    public function _phrase($value)
    {
        return '"' . $this->_escapePhrase($value) . '"';
    }

    /**
     * @param $query
     * @return string
     */
    public function _prepareQuery($field, $query)
    {
        return $this->_prepareCondition($field, $this->_queryText($query));
    }

    /**
     * @param array $ranges
     * @return array
     */
    public function _prepareRanges(array $ranges)
    {
        foreach ($ranges as &$ran) {
            if (isset($ran['from']) && isset($ran['to'])) {
                $from = (isset($ran['from']) && strlen(trim($ran['from'])))
                    ? $this->_queryText($ran['from'])
                    : '';
                $to = (isset($ran['to']) && strlen(trim($ran['to'])))
                    ? $this->_queryText($ran['to'])
                    : '';
                if (!$from) {
                    unset($ran['from']);
                } else {
                    $ran['from'] = $from;
                }
                if (!$to) {
                    unset($ran['to']);
                } else {
                    $ran['to'] = $to;
                }
            }
        }

        return $ranges;
    }

    /**
     * Craetes the field condition.
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public function _prepareCondition($field, $value)
    {
        if ($field == 'categories') {
            $condition = "(categories:{$value} OR show_in_categories:{$value})";
        } else {
            $condition = $field . ':' . $value;
        }

        return $condition;
    }

    /**
     * Creates the filter query text.
     *
     * @param string $text
     * @return mixed|string
     */
    public function _filterQueryText($text)
    {
        $queryWords = explode(' ', $text);
        if (count($queryWords) > 1) {
            $text = $this->_phrase($text);
        } else {
            $text = $this->_escapeChar($text);
        }

        return $text;
    }

    /**
     * Creates the filters.
     *
     * @param array $filters
     * @return array
     */
    public function _prepareFilter($filters, $asString = true)
    {
        $result = array();
        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    if ($field == 'price' || isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']))
                            ? $this->_filterQueryText($value['from'])
                            : '';
                        $to = (isset($value['to']))
                            ? $this->_filterQueryText($value['to'])
                            : '';
                        $condition = "$field:[$from TO $to]";
                    } else {
                        $condition = array();
                        foreach ($value as $part) {
                            $part = $this->_filterQueryText($part);
                            $condition[] = $this->_prepareCondition($field, $part);
                        }
                        $condition = '(' . implode(' OR ', $condition) . ')';
                    }
                } else {
                    $value = $this->_filterQueryText($value);
                    $condition = $this->_prepareCondition($field, $value);
                }
                $result[] = $condition;
            }
        }

        return $asString ? implode(' AND ', $result) : $result;
    }

    /**
     * Creates the query text.
     *
     * @param $text
     * @return string
     */
    public function _queryText($text)
    {
        $queryWords = explode(' ', $text);
        if (count($queryWords) > 1) {
            foreach ($queryWords as $key => &$val) {
                if (!empty($val)) {
                    $val = $this->_escapeChar($val);
                } else {
                    unset($queryWords[$key]);
                }
            }
            $text = '(' . implode(' ', $queryWords) . ')';
        } else {
            $text = $this->_escapeChar($text);
        }

        return $text;
    }

    /**
     * Craetes search query conditions.
     *
     * @param mixed $query
     * @return string
     */
    public function _searchQuery($query)
    {
        if (!is_array($query)) {
            $searchConditions = $this->_queryText($query);
        } else {
            $searchConditions = array();
            foreach ($query as $field => $value) {
                if (is_array($value)) {
                    if ($field == 'price' || isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']) && strlen(trim($value['from'])))
                            ? $this->_queryText($value['from'])
                            : '';
                        $to = (isset($value['to']) && strlen(trim($value['to'])))
                            ? $this->_queryText($value['to'])
                            : '';
                        $condition = "$field:[$from TO $to]";
                    } else {
                        $condition = array();
                        foreach ($value as $part) {
                            $part = $this->_filterQueryText($part);
                            $condition[] = $field .':'. $part;
                        }
                        $condition = '('. implode(' OR ', $condition) .')';
                    }
                } else {
                    if ($value != '*') {
                        $value = $this->_queryText($value);
                    }
                    $condition = $field .':'. $value;
                }
                $searchConditions[] = $condition;
            }
            $searchConditions = implode(' AND ', $searchConditions);
        }

        return $searchConditions;
    }

    /**
     * Sorts the fields.
     *
     * @param array $sortBy
     * @return array
     */
    public function _sortFields($sortBy)
    {
        $result = array();
        foreach ($sortBy as $sort) {
            $_sort = each($sort);
            $field = $_sort['key'];
            $type = $_sort['value'];
            if ($field == 'relevance') {
                $field = '_score';
            } elseif ($field == 'position') {
                  $field = 'pos_cat_' . Mage::registry('current_category')->getId();
            } elseif ($field == 'price') {
                $websiteId = Mage::app()->getStore()->getWebsiteId();
                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                $field = 'price_'. $customerGroupId .'_'. $websiteId;
            } else {
                $field = $this->getSortableFieldName($field);
            }
            $result[] = array($field => trim(strtolower($type)));
        }

        return $result;
    }

    public function isLog() {
        $log = Mage::getStoreConfig('elasticsearch/advanced/elasticsearch_enable_debug_mode', Mage::app()->getStore());
        if($log) {
            return true;
        } else {
            return false;
        }
    }
}