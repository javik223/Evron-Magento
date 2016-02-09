<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 1:17 CH
 */
class Magestore_Webpos_Block_Adminhtml_Role_Renderer_Permission extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $permissionIds = $row->getPermissionIds();

        $permissionArray = explode(',',$permissionIds);
        $permissionSource  = Mage::getSingleton('webpos/source_adminhtml_permission')->getOptionArray();
        $permissionText=array();
        foreach ($permissionArray as $permission) {
            if (isset($permissionSource[$permission])) {
                $permissionText[]=$permissionSource[$permission];
            }
        }
        $result=implode(', ',$permissionText);
        echo $result;


    }
}