<?php
/**
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Hunter
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */

class Hunter_Crawler_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        /** @var Hunter_Crawler_Helper_Data $helper */
        $helper = Mage::helper('hunter_crawler');
        if ($helper->isLockedCron()) {
            Mage::getSingleton('core/session')->addNotice(
                Mage::helper('hunter_crawler')->__('FPC refresh cron job is locked.')
            );
        }
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('hunter_crawler/queue'));
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = '_export.csv';
        $content = $this->getLayout()->createBlock('hunter_crawler/queue_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportExcelAction()
    {
        $fileName = '_export.xml';
        $content = $this->getLayout()->createBlock('hunter_crawler/queue_grid')->getExcel();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select (s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('hunter_crawler/queue')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('hunter_crawler')->__('An error occurred while mass deleting items. Please review log and try again.')
                );
                Mage::logException($e);
                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massRunRefreshAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select (s).'));
        } else {
            try {
                $response = new Varien_Object();

                $maxQty = Mage::getStoreConfig('hunter_fpc_crawler/general_settings/manual_processing_items_count');
                if (count($ids) <= $maxQty) {
                    Mage::dispatchEvent('hunter_crawler_queue_run_cleaning', array('ids' => $ids, 'response' => $response));

                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been processed.', count($ids))
                    );
                    $this->_getSession()->addSuccess(
                        $this->__('%d record(s) have been processed successfully.', $response->getSuccess())
                    );
                } else {
                    $this->_getSession()->addError(
                        $this->__('Maximum quantity page URLs for refresh is %d. Please readjust selection and try again.', $maxQty)
                    );
                }

                $errors = $response->getErrors();
                if (!empty($errors)) {
                    $this->_getSession()->addError(
                        $this->__('%d record(s) have been processed unsuccessfully.', $response->getErrors())
                    );
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('hunter_crawler')->__('An error occurred while mass processing items. Please review log and try again.')
                );
                Mage::logException($e);
                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('hunter_crawler/queue');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('hunter_crawler')->__('This entity no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('current_model', $model);

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('hunter_crawler/queue_edit'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('hunter_crawler/queue');
            $url = trim($this->getRequest()->getParam('page_key') , '/');
            $data['page_key'] = $url;
            $entity = Mage::getModel('hunter_crawler/factory')->load(
                $this->getRequest()->getParam('entity_type'),
                $url
            );

            if (!$entity->getUrlPath()) {
                $this->_getSession()->addError(
                    Mage::helper('hunter_crawler')->__('No found any item by this URL.')
                );
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit');
                return;
            }

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->_getSession()->addError(
                        Mage::helper('hunter_crawler')->__('This no longer exists.')
                    );
                    $this->_redirect('*/*/index');
                    return;
                }
            }

            // save model
            try {
                $model->addData($data);
                $this->_getSession()->setFormData($data);
                if (!$model->getDateAdd()) {
                    $model->setDateAdd(date('Y-m-d H:i:s', time()));
                }
                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    Mage::helper('hunter_crawler')->__('The URL has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            } catch (Zend_Db_Statement_Exception $e) {
                // if entity duplicate
                if ('23000' == $e->getCode()) {
                    $this->_getSession()->addError(Mage::helper('hunter_crawler')->__('The entity already exists with this URL'));
                    $redirectBack = true;
                }
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('hunter_crawler')->__('Unable to save the .'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('hunter_crawler/queue');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('hunter_crawler')->__('Unable to find a entity to delete.'));
                }
                $model->delete();
                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('hunter_crawler')->__('The entities has been deleted.')
                );
                // go to grid
                $this->_redirect('*/*/index');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('hunter_crawler')->__('An error occurred while deleting  data. Please review log and try again.')
                );
                Mage::logException($e);
            }
            // redirect to edit form
            $this->_redirect('*/*/edit', array('id' => $id));
            return;
        }
        // display error message
        $this->_getSession()->addError(
            Mage::helper('hunter_crawler')->__('Unable to find a entity to delete.')
        );
        // go to grid
        $this->_redirect('*/*/index');
    }

    /**
     * Stop FPC refreshing
     *
     * @return null
     */
    public function lockRefreshAction()
    {
        /** @var Hunter_Crawler_Helper_Data $helper */
        $helper = Mage::helper('hunter_crawler');
        $helper->lockCron();

        // display success message
        $this->_getSession()->addSuccess(
            Mage::helper('hunter_crawler')->__('FPC refresh cron job has been locked.')
        );
        // go to grid
        $this->_redirect('*/*/index');
    }

    /**
     * Unlock FPC refreshing
     *
     * @return null
     */
    public function unlockRefreshAction()
    {
        /** @var Hunter_Crawler_Helper_Data $helper */
        $helper = Mage::helper('hunter_crawler');
        $helper->unlockCron();

        Mage::getSingleton('core/session')->getMessages(true);

        // display success message
        $this->_getSession()->addSuccess(
            Mage::helper('hunter_crawler')->__('FPC refresh cron job has been unlocked.')
        );
        // go to grid
        $this->_redirect('*/*/index');
    }
}