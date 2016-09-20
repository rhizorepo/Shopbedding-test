<?php
abstract class Gorilla_Heartbeat_Model_Tests_Abstract extends Mage_Core_Model_Abstract
{
    const SEVERITY_SUCCESS   = 'success';
    const SEVERITY_ERROR     = 'error';
    const SEVERITY_WARNING   = 'warning';

    protected $_errors = array();
    protected $_warnings = array();
    protected $_warningLog = 'gorilla_heartbeat_warning.log';
    protected $_errorLog = 'gorilla_heartbeat_error.log';

    public function addError($message)
    {
        array_push($this->_errors, $message);
        return $this;
    }

    public function hasErrors()
    {
        return count($this->_errors) > 0;
    }

    public function addWarning($message)
    {
        array_push($this->_warnings, $message);
        return $this;
    }

    public function hasWarnings()
    {
        return count($this->_warnings) > 0;
    }

    /**
     * Returns result of the test
     * @return mixed
     */
    public function getResult()
    {
        if ($this->hasErrors()) {
            return self::SEVERITY_ERROR;
        }
        if ($this->hasWarnings()) {
            return self::SEVERITY_WARNING;
        }
        return self::SEVERITY_SUCCESS;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getWarnings()
    {
        return $this->_warnings;
    }

}