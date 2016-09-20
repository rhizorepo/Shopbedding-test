<?php
/**
 * Class Shopbedding_UpdateFeed_Helper_Data
 */
class Shopbedding_UpdateFeed_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Data Feed File path
     */
    const XML_PATH_SETTINGS_FILEPATH = 'var/productfeed';

    /**
     * Data Feed File name (XLSX)
     */
    const XML_PATH_SETTINGS_FILENAME_XLSX = 'cse-feed-new.xlsx';

    /**
     * Data Feed File name (CSV)
     */
    const XML_PATH_SETTINGS_FILENAME_CSV = 'cse-feed-new.csv';

    /**
     * Get file path
     *
     * @param string $type
     * @return bool|string
     */
    public function getFeedFilePath($type = 'xlsx')
    {
        $fileDir  = self::XML_PATH_SETTINGS_FILEPATH;
        $fileName = $type == 'xlsx' ? self::XML_PATH_SETTINGS_FILENAME_XLSX : self::XML_PATH_SETTINGS_FILENAME_CSV;
        $file = Mage::getBaseDir() . '/' . $fileDir . DS . $fileName;

        if (!file_exists($file)) {
            Mage::log("File $file not exists");
            Mage::getSingleton('core/session')->addError("File $file not exists");
            foreach (debug_backtrace() as $key => $info) {
                Mage::Log("#" . $key .
                    " Called " .
                    $info['function'] .
                    " in " .
                    $info['file'] .
                    " on line " .
                    $info['line']);
            }

            return false;
        }

        return $file;
    }

    /**
     * Get dir for data feed file upload
     *
     * @return string
     */
    public function getFeedFileDir()
    {
        $fileDir   = self::XML_PATH_SETTINGS_FILEPATH;
        $uploadDir = Mage::getBaseDir() . '/' . $fileDir . DS;

        if (!is_dir($uploadDir)) {
            Mage::log("Upload dir: $uploadDir not exists");
            Mage::getSingleton('core/session')->addError("Upload dir: $uploadDir not exists");

            return false;
        }

        if (!is_writable($uploadDir)) {
            Mage::log("Upload dir: $uploadDir is not writable");
            Mage::getSingleton('core/session')->addError("Upload dir: $uploadDir is not writable");

            return false;
        }

        return $uploadDir;
    }
}