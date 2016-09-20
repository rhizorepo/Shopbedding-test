<?php

class SoftwareTec_Clickdesk_Adminhtml_DashController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

        
        if(isset($_GET['cdwidgetid']))
                $cdwidgetid =  $_GET['cdwidgetid'];
        else
                $cdwidgetid =  "";
			
        if($cdwidgetid != "")
        {
                 $this->saveWidgetId($cdwidgetid);
        }

        $this->loadLayout()->renderLayout();
    }

 
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
 
            $this->saveWidgetId($post["clickdesk"]["widgetid"]);
 
 
            
            $message = $this->__('Your form has been submitted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }

    public function saveWidgetId($wigetid)
    {


                $db = Mage::getSingleton('core/resource')->getConnection('core_read');
                $result = $db->query("SELECT * FROM clickdesk LIMIT 1");
                if($result) {
        
                   if($row = $result->fetch())
                   {
                                      $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
                                      $conn->query("update clickdesk set widgetid='".addslashes($wigetid)."'");
                   }else
                   {

                                    $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
                                    $conn->query("insert into clickdesk values('','".addslashes($wigetid)."')");
 
                   }
                }

    }
 
}