<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

/**
 * Class MageWorkshop_Core_Model_Uninstall
 */
class MageWorkshop_Core_Model_Uninstall extends Mage_Core_Model_Abstract
{
    /**
     * @return string - empty string if the file was not found
     */
    public function checkPackageFile($moduleName)
    {
        // Find the package
        $packageFile = '';
        if ($moduleName) {
            $etcFiles = glob(getcwd() . DS . 'app' . DS .'code' . DS .'local' . DS .'MageWorkshop' . DS .$moduleName . DS . 'etc' . DS . '*.xml');
            foreach ($etcFiles as $v) {
                $name = explode(DS, $v);
                $checkName = substr($name[count($name) - 1], 0, -4);
                if (strpos($checkName, $moduleName) !== false) {
                    $packageFile = 'app' . DS .'code' . DS .'local' . DS .'MageWorkshop' . DS . $moduleName . DS . 'etc' . DS . $name[count($name) - 1];
                }
            }
        }
        return $packageFile;
    }

    public function processUninstallPackage($packageName)
    {
        $packageFile = Mage::getModel('drcore/uninstall')->checkPackageFile($packageName);
        if ($packageFile) {
            try {
                $package = new Mage_Connect_Package($packageFile);
                $contents = $package->getContents();

                $targetPath = rtrim(getcwd(), "\\/");
                foreach ($contents as $file) {
                    $fileName = basename($file);
                    $filePath = dirname($file);
                    $dest = $targetPath . DS . $filePath . DS . $fileName;
                    if (@file_exists($dest)) {
                        @unlink($dest);
                        $this->_removeEmptyDirectory(dirname($dest));
                    }
                }
//                if($packageName == 'Core') {
//                    $destDirs = array (
//                        'local' => $targetPath . DS . 'app' . DS .'code' . DS .'local' . DS .'MageWorkshop',
//                        'design' => $targetPath . DS . 'app' . DS .'design' . DS .'frontend' . DS .'base' . DS .'default' . DS .'template' . DS .'detailedreview',
//                        'js' => $targetPath . DS . 'js' . DS .'detailedreview',
//                        'media' => $targetPath . DS . 'media' . DS .'detailedreview',
//                        'skin_css' => $targetPath . DS . 'skin' . DS .'frontend' . DS .'base' . DS .'default' . DS .'css' . DS .'detailedreview',
//                        'skin_images' => $targetPath . DS . 'skin' . DS .'frontend' . DS .'base' . DS .'default' . DS .'images' . DS .'detailedreview'
//                    );
//                    foreach ($destDirs as $dir) {
//                        if (is_dir($dir)) {
//                            $this->_removeFullDirectory($dir);
//                        }
//                    }
//                }
                $destDir = $targetPath . DS . 'app' . DS .'code' . DS .'local' . DS .'MageWorkshop' . DS .$packageName;
                if(is_dir($destDir)) {
                    $this->_removeFullDirectory($destDir);
                }
                $downloaderCacheFile = $targetPath . DS . 'downloader' . DS . 'cache.cfg';
                @unlink($downloaderCacheFile);
            } catch (Exception $e) {
                $session = Mage::getSingleton('core/session');
                $session->addException($e, Mage::helper('drcore')->__('There was a problem with uninstalling.'));
                return false;
            }
        }
        return true;
    }

    /**
     * Remove empty directories recursively up
     *
     * @param string $dir
     * @param Mage_Connect_Ftp $ftp
     */
    private function _removeEmptyDirectory($dir, $ftp = null)
    {
        if ($ftp) {
            if (count($ftp->nlist($dir)) == 0) {
                if ($ftp->rmdir($dir)) {
                    $this->_removeEmptyDirectory(dirname($dir), $ftp);
                }
            }
        } else {
            if (@rmdir($dir)) {
                $this->_removeEmptyDirectory(dirname($dir), $ftp);
            }
        }
    }

    /**
     * Remove all directories recursively up
     *
     * @param string $dir
     */
    private function _removeFullDirectory($dir) {
            if ($objs = glob($dir."/*")) {
                foreach($objs as $obj) {
                    is_dir($obj) ? $this->_removeFullDirectory($obj) : @unlink($obj);
                }
            }
            @rmdir($dir);
    }

}
