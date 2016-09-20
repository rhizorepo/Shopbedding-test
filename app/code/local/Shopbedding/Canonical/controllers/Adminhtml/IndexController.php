<?php

class Shopbedding_Canonical_Adminhtml_IndexController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('system/shop_canonical')
			->_addBreadcrumb(Mage::helper('shop_canonical')->__('Canonical Rules'), Mage::helper('shop_canonical')->__('Canonical Rules'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
        $this->_initAction()
            ->renderLayout();
	}
 
	public function newAction() {
        $this->_initAction()
            ->renderLayout();
	}
 
	public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try{
                $id = $this->getRequest()->getParam('id');
                $model = Mage::getModel('shop_canonical/rule');
                $model->setData($data)->setId($id);
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('shop_canonical')->__('Rule was successfully saved'));
            }catch (Exception $e){
                Mage::log($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shop_canonical')->__($e->getMessage()));
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
		if( ($id = $this->getRequest()->getParam('id')) > 0 ) {
			try {
				$model = Mage::getModel('shop_canonical/rule');
				$model->setId($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('shop_canonical')->__('Rule was successfully deleted'));
			} catch (Exception $e) {
                Mage::log($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shop_canonical')->__($e->getMessage()));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('shop_canonical');
        if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shop_canonical')->__('Please select Rule(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $rule = Mage::getModel('shop_canonical/rule')->load($id);
                    $rule->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('shop_canonical')->__('Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}