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

class Hunter_Crawler_Adminhtml_ResultController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('hunter_crawler/result'));
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
                    $model = Mage::getSingleton('hunter_crawler/result')->load($id);
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

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('hunter_crawler/queue');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('hunter_crawler')->__('Unable to find a  to delete.'));
                }
                $model->delete();
                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('hunter_crawler')->__('The  has been deleted.')
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
            Mage::helper('hunter_crawler')->__('Unable to find a to delete.')
        );
        // go to grid
        $this->_redirect('*/*/index');
    }
}