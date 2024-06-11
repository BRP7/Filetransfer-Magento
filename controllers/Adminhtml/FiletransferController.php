<?php

class Ccc_Filetransfer_Adminhtml_FiletransferController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ccc_filetransfer/filetransfer');
        return $this;
    }


    public function indexAction()
    {
        $this->_title($this->__('Manage Configuration'));
        $this->_initAction();
        $this->renderLayout();

    }
    public function viewAction()
    {
        $this->_title($this->__('Manage Ftp File'));
        $this->_initAction();
        $this->renderLayout();

    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {

        $this->_title($this->__('File Configuration'))->_title($this->__('File Configuration'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ccc_filetransfer/configuration');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ccc_filetransfer')->__('This ccc_filetransfer no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getTitle() : $this->__('New ccc filetransfer'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {

            $model->setData($data);
        }

        Mage::register('configuration_data', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('ccc_filetransfer')->__('Edit ccc filetransfer') : Mage::helper('ccc_filetransfer')->__('New ccc filetransfer'), $id ? Mage::helper('ccc_filetransfer')->__('Edit ccc_filetransfer') : Mage::helper('ccc_filetransfer')->__('New ccc_filetransfer'));
        $this->renderLayout();
    }


    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('ccc_filetransfer/configuration');
            var_dump($model);
            if ($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
            }
            $model->setData($data);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ccc_filetransfer')->__('The ccc_filetransfer has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('ccc_filetransfer')->__('An error occurred while saving the ccc_filetransfer.')
                );
            }
            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->_redirect('*/*/');
    }
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $title = "";
            try {
                $model = Mage::getModel('ccc_filetransfer/configuration');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ccc_filetransfer')->__('The page has been deleted.')
                );
                Mage::dispatchEvent('adminhtml_cmspage_on_delete', array('title' => $title, 'status' => 'success'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::dispatchEvent('adminhtml_cmspage_on_delete', array('title' => $title, 'status' => 'fail'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ccc_filetransfer')->__('Unable to find a page to delete.'));
        $this->_redirect('*/*/');
    }
    public function massDeleteAction()
    {
        $locationcheckIds = $this->getRequest()->getParam('id');
        if (!is_array($locationcheckIds)) {
            $this->_getSession()->addError($this->__('Please select ccc_filetransfer(s).'));
        } else {
            if (!empty($locationcheckIds)) {
                try {
                    foreach ($locationcheckIds as $locationcheckId) {
                        $location = Mage::getSingleton('ccc_filetransfer/filetransferconfiguration')->load($locationcheckId);
                        $location->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($locationcheckIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $locationcheckIds = $this->getRequest()->getParam('id');
        $isActive = $this->getRequest()->getParam('is_active');

        if (!is_array($locationcheckIds)) {
            $locationcheckIds = array($locationcheckIds);
        }

        try {
            foreach ($locationcheckIds as $locationcheckId) {
                $Location = Mage::getModel('ccc_filetransfer/configuration')->load($locationcheckId);
                if ($Location->getIsActive() != $isActive) {
                    $Location->setIsActive($isActive)->save();
                }
            }
            if ($isActive == 1) {
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been Yes.', count($locationcheckIds))
                );
            } else {
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been No.', count($locationcheckIds))
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }






    


  
   
}
