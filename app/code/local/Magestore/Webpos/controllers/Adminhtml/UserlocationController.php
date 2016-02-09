<?php
/**
 * Created by PhpStorm.
 * User: Quoc Viet
 * Date: 07/07/2015
 * Time: 10:19 SA
 */
class Magestore_Webpos_Adminhtml_UserlocationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        /* Edit by Dante - Trinh Huy Hoang - 28/08/2015 */
        if (Mage::helper('webpos')->isInventoryWebPOS11Active()) {
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/inp_warehouse/"));
        }
        /* End edit by Dante */
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $locationId     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('webpos/userlocation')->load($locationId);

        if ($model->getLocationId() || $locationId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('userlocation_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('webpos/manage/webpos_user_location');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Location Manager'),
                Mage::helper('adminhtml')->__('Location Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Location News'),
                Mage::helper('adminhtml')->__('Location News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('webpos/adminhtml_userlocation_edit'))
                ->_addLeft($this->getLayout()->createBlock('webpos/adminhtml_userlocation_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('webpos')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_redirect('*/*/edit');
    }

    /**
     * save item action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();


        if ($data = $this->getRequest()->getPost()) {




            $model = Mage::getModel('webpos/userlocation');

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                $model->save();
                $locationId=$model->getLocationId();
                if (isset($data['userlocation_user'])) {
                    $userArray = array();
                    parse_str(($data['userlocation_user']), $userArray);
                    $userArray = array_keys($userArray);
                    $userCollection=Mage::getModel('webpos/user')->getCollection()
                        ->addFieldToFilter('location_id',$this->getRequest()->getParam('id'));
                    foreach ($userCollection as $user) {
                        $userId=$user->getUserId();
                        if ($userId && !in_array($userId,$userArray)) {
                            $user->setLocationId(0);
                            $user->save();
                        }
                    }
                    foreach ($userArray as $user) {
                        if(is_numeric($user)){
                            $userModel = Mage::getModel('webpos/user')->load($user);
                            $userModel->setLocationId($locationId);
                            $userModel->save();
                        }
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('webpos')->__('Location was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('webpos')->__('Unable to find role to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('webpos/userlocation');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction()
    {
        $locationIds = $this->getRequest()->getParam('webpos');
        if (!is_array($locationIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select role(s)'));
        } else {
            try {
                foreach ($locationIds as $locationId) {
                    $agent = Mage::getModel('webpos/userlocation')->load($locationId);
                    $agent->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted',
                        count($locationIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */


    /**
     * export grid item to CSV type
     */
    public function exportCsvAction()
    {
        $fileName   = 'userlocation.csv';
        $content    = $this->getLayout()
            ->createBlock('webpos/adminhtml_userlocation_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction()
    {
        $fileName   = 'userlocation.xml';
        $content    = $this->getLayout()
            ->createBlock('webpos/adminhtml_userlocation_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function userAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('userlocation.edit.tab.user')
            ->setUsers($this->getRequest()->getPost('ouser', null));
        $this->renderLayout();
    }
    public function usergridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('userlocation.edit.tab.user')
            ->setUsers($this->getRequest()->getPost('ouser', null));
        $this->renderLayout();
    }
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/webpos/manage_webpos_user_location');
    }

}