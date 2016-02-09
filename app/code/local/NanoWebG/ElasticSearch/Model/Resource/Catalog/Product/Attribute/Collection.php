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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog product EAV additional attribute resource collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class NanoWebG_ElasticSearch_Model_Resource_Catalog_Product_Attribute_Collection
    extends Mage_Catalog_Model_Resource_Product_Attribute_Collection
{
   
    #overrite of parent method
    public function addToIndexFilter($addRequiredCodes = false)
    {
        
       // if(Mage::helper('nanowebg_elasticsearch')->isLog()) {
        //     Mage::log('NanoWebG_ElasticSearch_Model_Resource_Fulltext addToIndexFilter overrite', null, 'elasticsearch_debug.log');
        // }

        $conditions = array(
            'additional_table.is_searchable = 1',
            'additional_table.is_visible_in_advanced_search = 1',
            'additional_table.is_filterable > 0',
            'additional_table.is_filterable_in_search = 1',
            'additional_table.used_for_sort_by = 1'
        );

        if ($addRequiredCodes) {
            $conditions[] = $this->getConnection()->quoteInto('main_table.attribute_code IN (?)',
                array('status', 'visibility'));
        }

        $this->getSelect()->where(sprintf('(%s)', implode(' OR ', $conditions)));

        return $this;
    }

    
}
