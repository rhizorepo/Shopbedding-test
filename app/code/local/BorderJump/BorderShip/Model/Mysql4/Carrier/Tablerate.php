<?php

class BorderJump_BorderShip_Model_Mysql4_Carrier_Tablerate extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_importErrors        = array();
    protected $_importUniqueHash    = array();
    protected $_importedRows        = 0;
    
    protected function _construct()
    {
        $this->_init('bordership/carrier_tablerate', 'id');
    }
    
    public function getRate($weight)
    {
        $adapter = $this->_getReadAdapter();
        
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where("weight <= ?", $weight)
            ->order("weight DESC")
            ->limit(1);

        $result = $adapter->fetchRow($select);
        return $result['price'];
    }
    
    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['bordership']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['bordership']['fields']['import']['value'];
        $website = Mage::app()->getWebsite($object->getScopeId());
        
        $this->_importWebsiteId     = (int)$website->getId();
        $this->_importErrors = array();
        
        $io     = new Varien_Io_File();
        $info   = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
        
        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < 2) {
            $io->streamClose();
            Mage::throwException(Mage::helper('bordership')->__('Invalid Table Rates File Format'));
        }
        
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        
        try {
            $rowNumber  = 1;
            $importData = array();

            // delete old data by website and condition name
            $condition = array(
                'website_id = ?'     => $this->_importWebsiteId,
            );
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('bordership')->__('An error occurred while import table rates.'));
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = Mage::helper('bordership')->__('%1$d records have been imported. See the following list of errors for each record that has not been imported: %2$s',
                $this->_importedRows, implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        }

        return $this;
    }
    
    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (float)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }
    
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 2) {
            $this->_importErrors[] = Mage::helper('bordership')->__('Invalid Table Rates format in the Row #%s',
                $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }
        
        // validate weight
        $weight = $this->_parseDecimalValue($row[0]);
        if ($weight === false) {
            $this->_importErrors[] = Mage::helper('bordership')->__('Invalid weight "%s" in the Row #%s.',
               $row[0], $rowNumber);
            return false;
        }
        
        // validate price
        $price = $this->_parseDecimalValue($row[1]);
        if ($price === false) {
            $this->_importErrors[] = Mage::helper('bordership')->__('Invalid Shipping Price "%s" in the Row #%s.',
                $row[1], $rowNumber);
            return false;
        }

        // protect from duplicate
        $hash = sprintf("%F", $weight);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('bordership')->__('Duplicate Row #%s (Weight "%s").',
                $rowNumber, $weight);
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return array($this->_importWebsiteId, $weight, $price);
    }

    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array('website_id', 'weight', 'price');
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }
        return $this;
    }
}
