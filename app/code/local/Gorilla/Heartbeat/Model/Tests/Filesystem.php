<?php
/**
 * filesystem test model. 
 * @package 
 */
class Gorilla_Heartbeat_Model_Tests_Filesystem 
    extends Gorilla_Heartbeat_Model_Tests_Abstract
    implements Gorilla_Heartbeat_Model_TestsInterface
{
    const MODE_WRITE = 'write';
    const MODE_READ  = 'read';

    /**
     * Returns true if test enabled
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isEnabled()
    {
        return true;
    }
    
    /**
     * Returns true if test passed
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isPassed()
    {
        $file = new Varien_Io_File();
        $filename = Mage::getBaseDir('var') . DS . 'test.txt';
        $dirname = Mage::getBaseDir('var');

        if (file_exists($filename)) {
            if (!is_writeable($filename)) {
                Mage::helper('heartbeat')->log('Filesystem Test Error. Files in var directory are no writable.', null, 'gorilla_heartbeat.log');
                return false;
            }
        }

        if (!is_writable($dirname)) {
            Mage::helper('heartbeat')->log('Filesystem Test Error. Var directory is no writable.', null, 'gorilla_heartbeat.log');
            return false;
        }

        $file->open($dirname);
        $file->write($filename, 'testcontent');
        $fileContent = $file->read($filename);

        if ($fileContent == 'testcontent') {
            return true;
        }

        Mage::helper('heartbeat')->log('Filesystem Test Error. Cant read or write to the test file in var directory.', null, 'gorilla_heartbeat.log');
        return false;
    }

    public function process()
    {
        // filesystem
        $config = Mage::getSingleton('install/config')->getPathForCheck();

        if (isset($config['writeable'])) {
            foreach ($config['writeable'] as $item) {
                $recursive = isset($item['recursive']) ? $item['recursive'] : false;
                $existence = isset($item['existence']) ? $item['existence'] : false;
                $this->_checkPath($item['path'], $recursive, $existence, 'write');
            }
        }

        // php extensions
        $config = Mage::getSingleton('install/config')->getExtensionsForCheck();
        foreach ($config as $extension => $info) {
            if (!empty($info) && is_array($info)) {
                $this->_checkExtension($info);
            }
            else {
                $this->_checkExtension($extension);
            }
        }
    }

    public function getRecommendations() {
        return 'Cant write file to the var folder. Not enough rights or hard drive is full.';
    }

    protected function _checkExtension($extension)
    {
        if (is_array($extension)) {
            $oneLoaded = false;
            foreach ($extension as $item) {
                if (extension_loaded($item)) {
                    $oneLoaded = true;
                }
            }

            if (!$oneLoaded) {
                $this->addWarning(
                    Mage::helper('heartbeat')->__('One of PHP Extensions "%s" must be loaded.', implode(',', $extension))
                );
                Mage::helper('heartbeat')->log(
                    Mage::helper('heartbeat')->__('One of PHP Extensions "%s" must be loaded.', implode(',', $extension)),
                    null,
                    $this->_warningLog
                );
                return false;
            }
        }
        elseif (!extension_loaded($extension)) {
            $this->addWarning(
                Mage::helper('install')->__('PHP extension "%s" must be loaded.', $extension)
            );
            Mage::helper('heartbeat')->log(
                Mage::helper('install')->__('PHP extension "%s" must be loaded.', $extension),
                null,
                $this->_warningLog
            );
            return false;
        }
        else {
            //
        }
        return true;
    }

    /**
     * Check file system path
     *
     * @param   string $path
     * @param   bool $recursive
     * @param   bool $existence
     * @param   string $mode
     * @return  bool
     */
    protected function _checkPath($path, $recursive, $existence, $mode)
    {
        $res = true;
        $fullPath = dirname(Mage::getRoot()) . $path;
        if ($mode == self::MODE_WRITE) {
            $setError = false;
            if ($existence) {
                if ((is_dir($fullPath) && !is_dir_writeable($fullPath)) || !is_writable($fullPath)) {
                    $setError = true;
                }
            }
            else {
                if (file_exists($fullPath) && !is_writable($fullPath)) {
                    $setError = true;
                }
            }

            if ($setError) {
                $this->addError(
                    Mage::helper('install')->__('Path "%s" must be writable.', $fullPath)
                );
                $res = false;
            }
        }

        if ($recursive && is_dir($fullPath)) {
            foreach (new DirectoryIterator($fullPath) as $file) {
                if (!$file->isDot() && $file->getFilename() != '.svn' && $file->getFilename() != '.htaccess') {
                    $res = $res && $this->_checkPath($path . DS . $file->getFilename(), $recursive, $existence, $mode);
                }
            }
        }
        return $res;
    }
}