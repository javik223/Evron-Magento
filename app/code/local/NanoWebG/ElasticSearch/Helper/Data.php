<?php
/**
 * 
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * 
 */
class NanoWebG_ElasticSearch_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Array of allowed languages.
     * Example: array('en_US' => 'en', 'fr_FR' => 'fr', 'es_ES' => 'es')
     *
     * @var array
     */
    protected $_languages = array();

    /**
     * Array of searchable attributes.
     *
     * @var array
     */
    protected $_searchables;

    /**
     * Array of sortable attributes.
     *
     * @var array
     */
    protected $_sortables;

    /**
     * Array of text field types.
     *
     * @var array
     */
    protected $_textTypes = array(
        'text',
        'varchar',
    );

    /**
     * Array of unlocalized field types.
     *
     * @var array
     */
    protected $_unlocalFieldTypes = array(
        'datetime',
        'decimal',
    );

    /**
     * Returns the attribute field name (localized if needed).
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string $localeCode
     * @return string
     */
    public function getFieldName($attribute, $localeCode = null)
    {
        if (is_string($attribute)) {
            $this->getSearchableAttributes(); // populate searchable attributes if not already set
            if (!isset($this->_searchables[$attribute])) {
                return $attribute;
            }
            $attribute = $this->_searchables[$attribute];
        }
        $attrCode = $attribute->getAttributeCode();
        $backendType = $attribute->getBackendType();

        if ($attrCode != 'score' &&
            in_array($backendType, $this->_textTypes) &&
            $attribute->getFrontendInput() != 'multiselect')
        {
            if (null === $localeCode) {
                $localeCode = $this->getLocale();
            }
            $languageCode = $this->getLangByLocaleCode($localeCode);
            $languageSuffix = $languageCode ? '_' . $languageCode : '';
            $attrCode .= $languageSuffix;
        }

        return $attrCode;
    }

    /**
     * Returns the cache lifetime (seconds).
     *
     * @return int
     */
    public function getCacheTime()
    {
        return Mage::getStoreConfig('core/cache/lifetime');
    }

    /**
     * Returns search engine configuration data.
     *
     * @param string $prefix
     * @param mixed $store
     * @return array
     */
     public function getEngineConfig($config_prefix = '', $store = null)
    {
        $config = Mage::getStoreConfig('elasticsearch', $store);
        $data = array();

        foreach($config as $key => $section_fields){

            foreach($section_fields as $name =>$value){
              
               $field = str_replace($config_prefix,"",$name);
               $data[$field] = $value;

            }

        }
        $data_servers = str_replace("http://", "", $data['servers']);

        $server_parts = explode(":", $data_servers);

        $port=80;

        if(count($server_parts==1)){
            $host = $server_parts[0];
        }elseif(count($server_parts)==2){
            $host = $server_parts[0];
            $port = $server_parts[1];
        }

         if($data['username']){
             $host = $data['username'].':'.$data['password'].'@'.$host;
         }

        $data['host'] = $host;
        $data['port'] = $port;

        return $data;
    }

    /**
     * Returns EAV configuration singleton.
     *
     * @return Mage_Eav_Model_Config
     */
    public function getEavConfig()
    {
        return Mage::getSingleton('eav/config');
    }

    /**
     * Returns locale code.
     *
     * @param string $localeCode
     * @return bool
     */
    public function getLangByLocaleCode($localeCode)
    {
        $localeCode = (string) $localeCode;
        if (!$localeCode) {
            return false;
        }

        if (!isset($this->_languages[$localeCode])) {
            $supportedLanguages = $this->getSupportedLangs();
            $this->_languages[$localeCode] = false;
            foreach ($supportedLanguages as $code => $locales) {
                if (is_array($locales)) {
                    if (in_array($localeCode, $locales)) {
                        $this->_languages[$localeCode] = $code;
                    }
                } elseif ($localeCode == $locales) {
                    $this->_languages[$localeCode] = $code;
                }
            }
        }

        return $this->_languages[$localeCode];
    }

    /**
     * Returns the language code of the current store.
     *
     * @param mixed $store
     * @return bool
     */
    public function getLangCodeByStore($store = null)
    {
        return $this->getLangByLocaleCode($this->getLocale($store));
    }

    /**
     * Returns the locale code for the current store.
     *
     * @param null $store
     * @return string
     */
    public function getLocale($store = null)
    {
        return Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $store);
    }

    /**
     * Defines the languages that are supported by the snowball filter.
     *
     * @return array
     */
    public function getSupportedLangs()
    {
        $default = array(
            /**
             * SnowBall filter based
             */
            // Danish
            'da' => 'da_DK',
            // Dutch
            'nl' => 'nl_NL',
            // English
            'en' => array('en_AU', 'en_CA', 'en_NZ', 'en_GB', 'en_US'),
            // Finnish
            'fi' => 'fi_FI',
            // French
            'fr' => array('fr_CA', 'fr_FR'),
            // German
            'de' => array('de_DE','de_DE','de_AT'),
            // Hungarian
            'hu' => 'hu_HU',
            // Italian
            'it' => array('it_IT','it_CH'),
            // Norwegian
            'nb' => array('nb_NO', 'nn_NO'),
            // Portuguese
            'pt' => array('pt_BR', 'pt_PT'),
            // Romanian
            'ro' => 'ro_RO',
            // Russian
            'ru' => 'ru_RU',
            // Spanish
            'es' => array('es_AR', 'es_CL', 'es_CO', 'es_CR', 'es_ES', 'es_MX', 'es_PA', 'es_PE', 'es_VE'),
            // Swedish
            'sv' => 'sv_SE',
            // Turkish
            'tr' => 'tr_TR',

            /**
             * Lucene class based
             */
            // Czech
            'cs' => 'cs_CZ',
            // Greek
            'el' => 'el_GR',
            // Thai
            'th' => 'th_TH',
            // Chinese
            'zh' => array('zh_CN', 'zh_HK', 'zh_TW'),
            // Japanese
            'ja' => 'ja_JP',
            // Korean
            'ko' => 'ko_KR'
        );

        return $default;
    }

    /**
     * Gets all searchable product attributes.
     * Possibility to filter attributes by backend type.
     *
     * @param array $backendType
     * @return array
     */
    public function getSearchableAttributes($backendType = null)
    {
        if (null === $this->_searchables) {
            $this->_searchables = array();
            $entityType = $this->getEavConfig()->getEntityType('catalog_product');
            $entity = $entityType->getEntity();

            /* @var $productAttribute Mage_Catalog_Model_Resource_Product_Attribute_Collection */
            $productAttribute = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setEntityTypeFilter($entityType->getEntityTypeId())
                ->addVisibleFilter()
                ->addToIndexFilter(true);

            $attributes = $productAttribute->getItems();
            foreach ($attributes as $attr) {
                /** @var $attr Mage_Catalog_Model_Resource_Eav_Attribute */
                $attr->setEntity($entity);
                $this->_searchables[$attr->getAttributeCode()] = $attr;
            }
        }

        if (null !== $backendType) {
            $backendType = (array) $backendType;
            $attributes = array();
            foreach ($this->_searchables as $attr) {
                /** @var $attr Mage_Catalog_Model_Resource_Eav_Attribute */
                if (in_array($attr->getBackendType(), $backendType)) {
                    $attributes[$attr->getAttributeCode()] = $attr;
                }
            }

            return $attributes;
        }

        return $this->_searchables;
    }

    /**
     * Returns seach configuration data.
     *
     * @param string $field
     * @param mixed $store
     * @return array
     */
    public function getConfigData($field, $store = null)
    {
        $path = 'catalog/search/' . $field;

        return Mage::getStoreConfig($path, $store);
    }

    /**
     * Returns an array of searched parameter.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param mixed $value
     * @return array
     */
    public function getParams($attribute, $value)
    {
        if (empty($value) ||
            (isset($value['from']) && empty($value['from']) &&
                isset($value['to']) && empty($value['to']))) {
            return false;
        }

        $fieldName = $this->getFieldName($attribute);
        $backendType = $attribute->getBackendType();
        if ($backendType == 'datetime') {
            $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            if (is_array($value)) {
                foreach ($value as &$val) {
                    if (!is_empty_date($val)) {
                        $date = new Zend_Date($val, $dateFormat);
                        $val = $date->toString(Zend_Date::ISO_8601) . 'Z';
                    }
                }
                unset($val);
            } else {
                if (!is_empty_date($value)) {
                    $date = new Zend_Date($value, $dateFormat);
                    $value = $date->toString(Zend_Date::ISO_8601) . 'Z';
                }
            }
        }

        if ($attribute->usesSource()) {
            $attribute->setStoreId(Mage::app()->getStore()->getId());
        }

        return array($fieldName => $value);
    }

    /**
     * Returns the field name of the sortable attributes (localized if needed).
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string $locale
     * @return string
     */
    public function getSortableFieldName($attribute, $locale = null)
    {
        if (is_string($attribute)) {
            $this->getSortables(); // populate sortable attributes if not already set
            if (!isset($this->_sortables[$attribute])) {
                return $attribute;
            }
            $attribute = $this->_sortables[$attribute];
        }

        $attrCode = $attribute->getAttributeCode();

        if ($attrCode != 'score' && !in_array($attribute->getBackendType(), $this->_unlocalFieldTypes)) {
            if (null === $locale) {
                $locale = $this->getLocale();
            }
            $languageCode = $this->getLangByLocaleCode($locale);
            $languageSuffix = $languageCode ? '_' . $languageCode : '';
            $attrCode .= $languageSuffix;
        }

        return 'sort_by_' . $attrCode;
    }

    /**
     * Gets all sortable product attributes.
     *
     * @return array
     */
    public function getSortables()
    {
        if (null === $this->_sortables) {
            $this->_sortables = Mage::getSingleton('catalog/config')->getAttributesUsedForSortBy();
            if (array_key_exists('price', $this->_sortables)) {
                unset($this->_sortables['price']); // Price sorting is handled with searchable attribute.
            }
        }

        return $this->_sortables;
    }

    /**
     * Returns suggested field name.
     *
     * @return string
     */
    public function getSuggestFieldName()
    {
        return $this->getFieldName('name');
    }

    /**
     * Checks if engine is active.
     *
     * @return bool
     */
    public function isActiveEngine()
    {
        $engine = $this->getConfigData('engine');
        if ($engine && Mage::getConfig()->getResourceModelClassName($engine)) {
            $model = Mage::getResourceSingleton($engine);
            return $model
              //  && $model instanceof NanoWebG_ElasticSearch_Model_Resource_Engine_Abstract
                && $model instanceof NanoWebG_ElasticSearch_Model_Resource_ElasticSearch_Engine
                && $model->test();
        }

        return false;
    }

    public function useElasticSearch(){

       
        if($this->isActiveEngine()){

            #with search configuration = Elastic Search and category is selected, use Magento categories collection
            $current_category = Mage::registry('current_category');

            if($current_category){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    /**
     * Checks if specified attribute is indexable.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
    public function isIndexable($attribute)
    {
        if ($attribute->getBackendType() == 'varchar' && !$attribute->getBackendModel()) {
            return true;
        }

        if ($attribute->getBackendType() == 'int'
            && $attribute->getSourceModel() != 'eav/entity_attribute_source_boolean'
            && ($attribute->getIsSearchable() || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch())
        ) {
            return true;
        }

        if ($attribute->getIsSearchable() || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if attribute is using options.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
    public function isUsingOptions($attribute)
    {
        $sourceModel = Mage::getModel($attribute->getSourceModel());
        $backendType = $attribute->getBackendType();

        return $attribute->usesSource() &&
            ($backendType == 'int' && $sourceModel instanceof Mage_Eav_Model_Entity_Attribute_Source_Table) ||
            ($backendType == 'varchar' && $attribute->getFrontendInput() == 'multiselect');
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugMode()
    {
        $config = $this->getEngineConfig();

        return array_key_exists('enable_debug_mode', $config) && $config['enable_debug_mode'];
    }

    /**
     * Checks if suggestion mode is enabled.
     *
     * @return bool
     */
    public function isSuggestEnabled()
    {
        $config = $this->getEngineConfig();

        return array_key_exists('enable_suggest', $config) && $config['enable_suggest'];
    }

    /**
     * Method for customing product data indexation.
     *
     * @param array $index
     * @param string $separator
     * @return array
     */
    // public function _prepareIndexData($index, $separator = null)
    // {
    //     return $index;
    // }

      public function prepareIndexData($index, $separator = null)
    {
        return $index;
    }
    /**
     * Displays errors.
     *
     * @param string $error
     */
    public function errorMode($error)
    {
        echo Mage::app()->getLayout()->createBlock('core/messages')
            ->addError($error)->getGroupedHtml();
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