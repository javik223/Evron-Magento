<?php
class Magestore_Webpos_Controller_Action extends Mage_Core_Controller_Front_Action{
    public function preDispatch(){
        //vietdq prevent access without login
        parent::preDispatch();
        $action=$this->getFullActionName();
        $controller = $this->getRequest()->getControllerName();
        $action= $this->getRequest()->getActionName();
        if (!($controller=='index' && ($action=='loginPost'||$action=='logoutPost'
            ||$action=='index'))

        ) {
            $session = Mage::getSingleton('webpos/session');
            $userId = $session->getId();
            if (isset($userId) && ($userId > 0)) {
                return $this;
            } else {
                die();
            }
        }
        return $this;

    }
}

?>