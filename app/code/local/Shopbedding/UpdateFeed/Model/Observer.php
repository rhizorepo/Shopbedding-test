<?php
/**
 * Class Shopbedding_UpdateFeed_Model_Observer
 */
class Shopbedding_UpdateFeed_Model_Observer
{
    /**
     * Save system config event 
     *
     * @param Varien_Object $observer
     */
    public function saveSystemConfig($observer)
    {
        $store   = $observer->getStore();
        $website = $observer->getWebsite();
        $groups['settings']['fields']['cron_schedule']['value'] = $this->_getSchedule();

        Mage::getModel('adminhtml/config_data')
                ->setSection('shopbedding_update_feed')
                ->setWebsite($website)
                ->setStore($store)
                ->setGroups($groups)
                ->save();

        // upload feed file:
        if (isset($_FILES['upload_file']) AND (file_exists($_FILES['upload_file']['tmp_name']))) {
            $this->_uploadFile('upload_file');
        }
    }

    /**
     * Transform system settings option to cron schedule string
     * 
     * @return string
     */
    protected function _getSchedule()
    {
        $data = Mage::app()->getRequest()->getPost('groups');

        $day = !empty($data['settings']['fields']['cron_day']['value'])?
            $data['settings']['fields']['cron_day']['value']:
            0;

        $hours = !empty($data['settings']['fields']['cron_hours']['value'])?
            $data['settings']['fields']['cron_hours']['value']:
            0;

        $minutes = !empty($data['settings']['fields']['cron_minutes']['value'])?
            $data['settings']['fields']['cron_minutes']['value']:
            0;
        
        $schedule = "$minutes $hours * * $day";

        return $schedule;
    }

    /**
     * Upload feed file
     *
     * @param $fileInputName
     * @return bool
     */
    protected function _uploadFile($fileInputName)
    {
        try {
            $uploader = new Varien_File_Uploader($fileInputName);
            $uploader->setAllowedExtensions(array('xlsx'));
            $uploader->setAllowRenameFiles(false);
            if (!($path = Mage::helper('shopbedding_updatefeed')->getFeedFileDir())) {
                $dir = Mage::getBaseDir() . '/' . Shopbedding_UpdateFeed_Helper_Data::XML_PATH_SETTINGS_FILEPATH;
                // create upload folder if not exist
                if (mkdir($dir, 0777)) {
                    Mage::getSingleton('core/session')->addSuccess("Upload dir: $dir created");
                }
                $path = Mage::helper('shopbedding_updatefeed')->getFeedFileDir();
            }
            if ($uploader->save($path, $_FILES[$fileInputName]['name'])) {
                Mage::getSingleton('core/session')->addSuccess('File successfully downloaded');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }
}
