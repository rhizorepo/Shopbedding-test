<?php
/**
 * Class Shopbedding_UpdateFeed_Adminhtml_FileController
 */
class Shopbedding_UpdateFeed_Adminhtml_FileController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Download feed file action
     */
    public function downloadAction()
    {
        $type = $this->getRequest()->getParam('type');
        if (!($filePath = Mage::helper('shopbedding_updatefeed')->getFeedFilePath($type))) {
            $this->_redirect('adminhtml/system_config/edit/section/shopbedding_update_feed');
        }

        try {
            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true )
                ->setHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0',true)
                ->setHeader('Content-type', 'application/force-download' )
                ->setHeader('Content-Length', filesize($filePath))
                ->setHeader('Content-Disposition', 'inline' . '; filename=' . basename($filePath));
            $this->getResponse()->clearBody();
            $this->getResponse()->sendHeaders();
            readfile($filePath);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}