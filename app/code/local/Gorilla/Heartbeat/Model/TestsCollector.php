<?php
/**
 * Collector model. 
 * @package 
 */
class Gorilla_Heartbeat_Model_TestsCollector extends Mage_Core_Model_Abstract
{
    private $_start_time;
    private $_end_time;
    protected $_testsConfigNode = 'global/tests';
    protected $_tests = array();
    protected $_availableTests = array();
    
    private $_errors = array();
    private $_warnings = array();

    /**
     * Returns directory with test models
     * @return  string
     */
    private function _getTestsDir()
    {
        return Mage::getConfig()->getModuleDir(null, 'Gorilla_Heartbeat') . DS . 'Model' . DS . 'Tests';
    }

    /**
     * Collects test modeles
     * @return Varien_Data_Collection
     * @deprecated from 0.0.2
     */
    private function _getTestsCollection()
    {
        $handle = new Varien_Io_File();
        $handle->cd($this->_getTestsDir());
        $listOfFiles = $handle->ls('files_only');
        
        $collection = new Varien_Data_Collection();
        foreach ($listOfFiles as $file) {
            try {
                $modelName = strtolower(str_replace('.php', '', $file['text']));
                $testModel = Mage::getModel('heartbeat/tests_' . $modelName);
                
                if ($testModel->isEnabled()) {
                    $collection->addItem(
                            new Varien_Object(
                                array(
                                	'test_object' => $testModel,
                                )
                            )
                        );
                }
            } catch (Exception $e) {
                Mage::helper('heartbeat')->log('Cant load test ' . $modelName . '(' . $e->getMessage() . ')', null, 'gorilla_heartbeat.log');
            } 
        }
        
        return $collection;
    }
    
    /**
     * return true if all test passed
     * @return boolean
     */
    public function isAllPassed()
    {
        $allPassed = true;
        foreach ($this->getAvailableTests() as $testModel) {
            if ($testModel->getResult() == Gorilla_Heartbeat_Model_Tests_Abstract::SEVERITY_ERROR) {
                $allPassed = false;
                break;
            }
        }
        return $allPassed;
    }

    /**
     * Process tests
     * @return void
     */
    public function processTests()
    {
        /* reset errors */
        $this->_errors = array();
        $this->_warnings = array();

        /* start timer */
        $this->_start_time = $this->_getTime();

        foreach ($this->getAvailableTests() as $testModel) {
            $testModel->process();
        }

        /* end timer */
        $this->_end_time = $this->_getTime();

        /* process warnings */
        $this->_processWarnings();
    }

    /**
     * update warnings - only unique
     */
    private function _processWarnings()
    {
        $warnings = $this->getWarnings();
        if (count($warnings)) {
            try {
                /** @var Mage_Core_Model_Resource $resource */
                $resource = Mage::getModel('core/resource');
                $connection = $resource->getConnection('core_write');
                foreach ($warnings as $warning) {
                    $connection->insertOnDuplicate($resource->getTableName('heartbeat_warnings'), array('warning' => $warning), array('warning'));
                }
            } catch (Exception $e) {
                //
            }
        }
    }
    
    /**
     * Return tests passing time
     * @return float 
     */
    public function getPassingTime()
    {
        return round($this->_end_time * 1000 - $this->_start_time * 1000, 3) >= 0? round($this->_end_time * 1000 - $this->_start_time * 1000, 3) : 0;
    }
    
    /**
     * Return current time in seconds
     * @return float
     */
    private function _getTime()
    {
        /* detect the time */
        $timeNow = microtime();
        /* separates seconds and milliseconds in array */
        $arrayTime = explode(' ', $timeNow);
        /* we put together seconds and milliseconds */
        $timeReturn = floatval($arrayTime[1]) + floatval($arrayTime[0]);

        return $timeReturn;
    }
    
    public function getErrors()
    {
        if (!$this->_errors) {
            foreach ($this->getAvailableTests() as $testModel) {
                if ($testModel->hasErrors()) {
                    $this->_errors = array_merge($this->_errors, $testModel->getErrors());
                }
            }
        }
        return $this->_errors;
    }

    public function getWarnings()
    {
        if (!$this->_warnings) {
            foreach ($this->getAvailableTests() as $testModel) {
                if ($testModel->hasWarnings()) {
                    $this->_warnings = array_merge($this->_warnings, $testModel->getWarnings());
                }
            }
        }
        return $this->_warnings;
    }

    /**
     * Return all test entries from config file
     * Array has structure
     * 'code' => ('class' => string $class, 'label' => string $label)
     *
     * @return array
     */
    public function getAllTests()
    {
        if (empty($this->_tests)) {
            $testsConfig = Mage::getConfig()->getNode($this->_testsConfigNode);

            foreach ($testsConfig->children() as $testCode => $testConfig) {
                $class = $testConfig->getClassName();
                if (!empty($class)) {
                    $this->_tests[$testCode] = array('class' => $class, 'label' => (string) $testConfig->label);
                }
            }
        }
        return $this->_tests;
    }

    /**
     * Return available test models
     * Array has structure
     * 'code' => ('model' => Gorilla_Heartbeat_Model_Tests_Abstract $model)
     *
     * @return array
     */
    public function getAvailableTests()
    {
        $enabledTests = Mage::getStoreConfig('heartbeat/general/tests');
        $enabledTests = explode(',', $enabledTests);
        if (empty($this->_availableTests)) {
            $tests = $this->getAllTests();
            foreach ($tests as $code => $data) {
                if (!in_array($code, $enabledTests)) {
                    continue;
                }
                $this->_availableTests[$code] = Mage::getModel($data['class'])->setLabel($data['label']);
            }
        }
        return $this->_availableTests;
    }
}