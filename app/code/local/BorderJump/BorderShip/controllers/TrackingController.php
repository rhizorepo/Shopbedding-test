<?php

include_once("Mage/Shipping/controllers/TrackingController.php");
class BorderJump_BorderShip_TrackingController extends Mage_Shipping_TrackingController
{
    protected $_usedModuleName = 'adminhtml';
    protected $_currentArea = 'adminhtml';
    protected $_sessionNamespace = 'adminhtml';
    
    public function popupAction()
    {
        $shippingInfoModel = Mage::getModel('shipping/info')->loadByHash($this->getRequest()->getParam('hash'));
        Mage::register('current_shipping_info', $shippingInfoModel);
        if (count($shippingInfoModel->getTrackingInfo()) == 0) {
            $this->norouteAction();
            return;
        }
        
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('bordership/tracking_popupadmin','shipping.tracking.popupadmin');
        $block->setTemplate('shipping/tracking/popup.phtml');
        $this->getLayout()->getBlock('root')->setTemplate('page/popup.phtml');
        $this->getLayout()->getBlock('content')->append($block);
        
        $this->renderLayout();
    }
}
