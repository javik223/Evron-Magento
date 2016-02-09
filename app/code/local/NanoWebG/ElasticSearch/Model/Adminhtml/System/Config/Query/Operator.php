<?php
/**
 * Query available operators.
 *
 * @category    NanoWebG
 * @package     NanoWebG_ElasticSearch
 * @version     1.0.3
 * @copyright   
 */
class NanoWebG_ElasticSearch_Model_Adminhtml_System_Config_Query_Operator
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'OR', 'label' => 'OR'),
            array('value' => 'AND', 'label' => 'AND'),
        );
    }
}