<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 9:53 SA
 */
class Magestore_Webpos_Model_Role extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('webpos/role');
    }
    public function toOptionArray(){
        $options = array ('' => '---Select Role---');
        $roleCollection = $this->getCollection ()->addFieldToFilter('active',1);
        foreach ( $roleCollection as $role ) {
                $key = $role->getId();
                $value = $role->getDisplayName();
                $options [$key] = $value;
        }

        return $options;
    }
}