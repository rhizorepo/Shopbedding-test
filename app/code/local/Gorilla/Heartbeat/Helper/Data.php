<?php
/**
 * Data helper
 * @package 
 */
class Gorilla_Heartbeat_Helper_Data extends Mage_Core_Helper_Abstract
{   
    public function log($message, $level = null, $file = '')
    {
        $level  = is_null($level) ? Zend_Log::DEBUG : $level;
        $file = empty($file) ? 'system.log' : $file;

        try {
            $logDir  = Mage::getBaseDir('var') . DS . 'log';
            $logFile = $logDir . DS . $file;

            if (!is_dir($logDir)) {
                mkdir($logDir);
                chmod($logDir, 0777);
            }

            if (!file_exists($logFile)) {
                file_put_contents($logFile, '');
                chmod($logFile, 0777);
            }

            $format = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
            $formatter = new Zend_Log_Formatter_Simple($format);

            $writer = new Zend_Log_Writer_Stream($logFile);

            $writer->setFormatter($formatter);
            $loggers[$file] = new Zend_Log($writer);

            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }

            $loggers[$file]->log($message, $level);
        }
        catch (Exception $e) {
        }
    }
}