<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Adminhtml_TillController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webpos/manage_webpos_till')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('POS Till Manager'),
                Mage::helper('adminhtml')->__('POS Till Manager')
            );
        return $this;
    }
 
    /**
     * index action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction()
    {
        $tillId     = $this->getRequest()->getParam('id');
        $model = Mage::getModel('webpos/till')->load($tillId);
        if ($model->getId() || $tillId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('till_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('webpos/manage_webpos_till');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('POS Till Manager'),
                Mage::helper('adminhtml')->__('POS Till Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('POS Till News'),
                Mage::helper('adminhtml')->__('POS Till News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('webpos/adminhtml_till_edit'))
                ->_addLeft($this->getLayout()->createBlock('webpos/adminhtml_till_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('webpos')->__('Till does not exist')
            );
            $this->_redirect('*/*/');
        }
    }
 
    public function newAction()
    {
        $this->_forward('edit');
    }
 
    /**
     * save item action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('webpos/till');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('webpos')->__('Till was successfully saved')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setUserData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('webpos')->__('Unable to find till to save')
        );
        $this->_redirect('*/*/');
    }
 
    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('webpos/till');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Till was successfully deleted')
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
    public function massDeleteAction() {
        $tillIds = $this->getRequest()->getParam('till');
        if (!is_array($tillIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select till(s)'));
        } else {
            try {
                foreach ($tillIds as $tillId) {
                    $till = Mage::getModel('webpos/till')->load($tillId);
                    $till->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($tillIds))
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

    public function massStatusAction()
    {
        $tillIds = $this->getRequest()->getParam('till');
        if (!is_array($tillIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select till(s)'));
        } else {
            try {
                foreach ($tillIds as $tillId) {
                    Mage::getSingleton('webpos/till')
                        ->load($tillId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($tillIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    /**
     * export grid item to CSV type
     */
    public function exportCsvAction()
    {
        $fileName   = 'pos_till.csv';
        $content    = $this->getLayout()
                           ->createBlock('webpos/adminhtml_till_grid')
                           ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction()
    {
        $fileName   = 'pos_till.csv';
        $content    = $this->getLayout()
                           ->createBlock('webpos/adminhtml_till_grid')
                           ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/webpos/manage_webpos_permission/manage_webpos_till');
    }
}