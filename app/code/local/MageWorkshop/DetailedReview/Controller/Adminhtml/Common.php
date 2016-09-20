<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Controller_Adminhtml_Common extends Mage_Adminhtml_Controller_Action
{
    /** @var string $_entityType */
    protected $_entityType;

    /** @var string $_entityName */
    protected $_entityName;

    /** @var string $_className */
    protected $_className;

    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_' . $this->_className))
            ->renderLayout();
        return $this;
    }

    /**
     * Manage Pros or Cons
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage %s', $this->_entityName));
        $this->_initAction();
    }

    /**
     * Mass update Pros or Cons status
     */
    public function massUpdateStatusAction()
    {
        $entityIds = $this->getRequest()->getParam('review_proscons');
        if (!is_array($entityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select %s(s).', $this->_className));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            /* @var Mage_Adminhtml_Model_Session $session */
            try {
                $status = (int) $this->getRequest()->getParam('update_status');
                foreach ($entityIds as $entityId) {
                    /* @var MageWorkshop_DetailedReview_Model_Review_Proscons $model */
                    $model = Mage::getModel('detailedreview/review_proscons')->load($entityId);
                    $model->setStatus($status)
                          ->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($entityIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, 'An error has been occurred');
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected %s(s).', $this->_className));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    /**
     * Mass delete Pros or Cons
     */
    public function massDeleteAction()
    {
        $entityIds = $this->getRequest()->getParam('review_proscons');
        if (!is_array($entityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select %s(s).', $this->_className));
        } else {
            try {
                foreach ($entityIds as $entityId) {
                    $model = Mage::getModel("detailedreview/review_proscons")->load($entityId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($entityIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    /**
     * Save Pros or Cons
     */
    public function saveAction()
    {
        $entityId = (int) $this->getRequest()->getParam('entity_id', false);
        if ($data = $this->getRequest()->getPost()) {
            $entity = Mage::getModel('detailedreview/review_proscons');
            if (!isset($data['entity_type']) || empty($data['entity_type'])){
                $data['entity_type'] = $this->_entityType;
            }
            try {
                if ($entityId) {
                    $entity->load($entityId);
                    $entity->addData($data);
                } else {
                    $entity->setData($data);
                }
                Mage::dispatchEvent('detailedreview_adminhtml_common_before_save', array(
                    'data'  => $data,
                    'request'   => $this->getRequest()
                ));
                $entity->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The cons has been saved.'));

                /* Check if Save and Continue */
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('entity_id' => $entityId, '_current' => true));
                    return;
                }

                $this->getResponse()->setRedirect($this->getUrl('*/*/'));

                return;
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }

    /**
     * Add Pros or Cons status
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Pros or Cons status
     */
    public function editAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Review %s', $this->_entityName));

        $entityId = (int) $this->getRequest()->getParam('entity_id');
        $entity = Mage::getModel("detailedreview/review_proscons")->load($entityId);

        if ($entity->getId() || $entityId == 0) {
            $this->_title($this->__('%s %s', $this->_entityName, $entityId ? '#'.$entityId : ''));

            Mage::register('proscons_data', $entity);

            $this->loadLayout();
            $this->_setActiveMenu('catalog/review');
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('%s Manager', $this->_entityName),
                Mage::helper('adminhtml')->__('%s Manager', $this->_entityName),
                $this->getUrl('*/*/')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Edit %s', $this->_entityName),
                Mage::helper('adminhtml')->__('Edit %s', $this->_entityName)
            );

            $this->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_' . $this->_className . '_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('detailedreview')->__('The %s does not exist.', $this->_entityName));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Delete Pros or Cons status
     */
    public function deleteAction()
    {
        if ($entityId = (int) $this->getRequest()->getParam('entity_id')) {
            try {
                Mage::getModel('detailedreview/review_proscons')->load($entityId)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('detailedreview')->__('The %s has been deleted.', $this->_entityName)
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
}
