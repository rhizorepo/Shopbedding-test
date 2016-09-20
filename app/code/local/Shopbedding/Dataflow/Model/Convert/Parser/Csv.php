<?php
class Shopbedding_Dataflow_Model_Convert_Parser_Csv extends Mage_Dataflow_Model_Convert_Parser_Csv
{
    protected $_fields;

    protected $_mapfields = array();

    public function parse()
    {
        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $adapterName   = $this->getVar('adapter', null);
        $adapterMethod = $this->getVar('method', 'saveRow');

        if (!$adapterName || !$adapterMethod) {
            $message = Mage::helper('dataflow')->__('Please declare "adapter" and "method" nodes first.');
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        try {
            $adapter = Mage::getModel($adapterName);
        }
        catch (Exception $e) {
            $message = Mage::helper('dataflow')->__('Declared adapter %s was not found.', $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        if (!is_callable(array($adapter, $adapterMethod))) {
            $message = Mage::helper('dataflow')->__('Method "%s" not defined in adapter %s.', $adapterMethod, $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        if (Mage::app()->getRequest()->getParam('files')) {
            $file = Mage::app()->getConfig()->getTempVarDir().'/import/'
                . urldecode(Mage::app()->getRequest()->getParam('files'));
            $this->_copy($file);
        }

        $batchIoAdapter->open(false);

        $isFieldNames = $this->getVar('fieldnames', '') == 'true' ? true : false;
        if (!$isFieldNames && is_array($this->getVar('map'))) {
            $fieldNames = $this->getVar('map');
        }
        else {
            $fieldNames = array();
            foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
                $fieldNames[$v] = $v;
            }
        }

        $countRows = 0;
        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }

            $itemData = array();
            $countRows ++; $i = 0;
            foreach ($fieldNames as $field) {
                $itemData[$field] = isset($csvData[$i]) ? $csvData[$i] : null;
                $i ++;
            }

            $batchImportModel = $this->getBatchImportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($itemData)
                ->setStatus(1)
                ->save();
        }

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

        //$adapter->$adapterMethod();

        return $this;

//        // fix for field mapping
//        if ($mapfields = $this->getProfile()->getDataflowProfile()) {
//            $this->_mapfields = array_values($mapfields['gui_data']['map'][$mapfields['entity_type']]['db']);
//        } // end
//
//        if (!$this->getVar('fieldnames') && !$this->_mapfields) {
//            $this->addException('Please define field mapping', Mage_Dataflow_Model_Convert_Exception::FATAL);
//            return;
//        }
//
//        if ($this->getVar('adapter') && $this->getVar('method')) {
//            $adapter = Mage::getModel($this->getVar('adapter'));
//        }
//
//        $i = 0;
//        while (($line = fgetcsv($fh, null, $fDel, $fEnc)) !== FALSE) {
//            $row = $this->parseRow($i, $line);
//
//            if (!$this->getVar('fieldnames') && $i == 0 && $row) {
//                $i = 1;
//            }
//
//            if ($row) {
//                $loadMethod = $this->getVar('method');
//                $adapter->$loadMethod(compact('i', 'row'));
//            }
//            $i++;
//        }
//
//        return $this;
    }

    public function parseRow($i, $line)
    {
        if (sizeof($line) == 1) return false;

        if (0==$i) {
            if ($this->getVar('fieldnames')) {
                $this->_fields = $line;
                return;
            } else {
                foreach ($line as $j=>$f) {
                    $this->_fields[$j] = $this->_mapfields[$j];
                }
            }
        }

        $resultRow = array();

        foreach ($this->_fields as $j=>$f) {
            $resultRow[$f] = isset($line[$j]) ? $line[$j] : '';
        }

        return $resultRow;
    }

    /**
     * Read data collection and write to temporary file
     *
     * @return Mage_Dataflow_Model_Convert_Parser_Csv
     */
    public function unparse()
    {
        $batchExport = $this->getBatchExportModel()
            ->setBatchId($this->getBatchModel()->getId());
        $fieldList = $this->getBatchModel()->getFieldList();
        $batchExportIds = $batchExport->getIdCollection();
		//echo "<pre>"; print_r($batchExportIds); exit;
        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();

        if (!$batchExportIds) {
            $io->write("");
            $io->close();
            return $this;
        }

        if ($this->getVar('fieldnames')) {
            $csvData = $this->getCsvString($fieldList);
            $io->write($csvData);
        }

        foreach ($batchExportIds as $batchExportId) {
            $csvData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

			//echo "<pre>"; print_r($row); exit;
			$site_url =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			$product_image_url = $site_url . 'media/catalog/product';
			if(isset($row['Sku'])) { // will change this to db keys
			   $product_sku = $row['Sku'];
			 } elseif(isset($row['sku'])) {
			   $product_sku = $row['sku'];
			 }elseif(isset($row['SKU'])) {
			   $product_sku = $row['SKU'];
			 }else{
			     if(isset($row['Model'])) { // will change this to db keys
			     $product_sku = $row['Model'];
				 } elseif(isset($row['model'])) {
				   $product_sku = $row['model'];
				 } elseif(isset($row['MODEL'])) {
				   $product_sku = $row['MODEL'];
				 } else {
				   $product_sku = '';
				 }
			 }

			$product_id = Mage::getModel("catalog/product")->getIdBySku( $product_sku );
			//echo $product_id . '<hr>';
			$_product = Mage::getModel('catalog/product')->load($product_id);
			$productType=$_product->getTypeID();
			if($productType != 'configurable') {
				$parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product_id);
				if (isset($parentId[0])) {
					$product = Mage::getModel('catalog/product')->load($parentId[0]);
				}
			}

			if($productType == 'configurable') {
				$parentId = Mage::getSingleton('catalog/product_type_grouped')->getParentIdsByChild($product_id);
				if (isset($parentId[0])) {
					$product = Mage::getModel('catalog/product')->load($parentId[0]);
				}
			}

			//echo $productType."<br/>";
			//echo "<pre>";print_r($fieldList);
            foreach ($fieldList as $field) {
               // echo $field . "<HR>";


				if($field == 'ReferenceImageURL') { $row[$field] = $product_image_url . $row[$field]; }
				else if($field == 'Condition') { $row[$field] = isset($row[$field]) ? $row[$field] : 'New'; }
				else if($field == 'ActionURL') {


					if($productType != 'configurable') {
						$row[$field] = $site_url . $product->getUrlKey() .'.html';
					} else {
						$row[$field] = $site_url . $row[$field] .'.html';
					}
				}
				else if($field == 'ParentSKU' || $field == 'ParentSku') {

					if($productType != 'configurable' and @$parentId[0] !="" ) {

						$row[$field] = $product->getSku();
					}
					else {
						$row[$field] = $product_sku;//$row['Sku'];
					}


				}
				$csvData[] = isset($row[$field]) ? $row[$field] : '';
            }

		//echo "<Pre>"; print_r($csvData); exit;

            $csvData = $this->getCsvString($csvData);
            $io->write($csvData);
        }
		//exit;

        $io->close();

        return $this;
    }

    public function unparseRow($args)
    {
        $i = $args['i'];
        $row = $args['row'];

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        $fEsc = $this->getVar('escape', '\\');
        $lDel = "\r\n";

        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $line = array();
        foreach ($this->_fields as $f) {
            $v = isset($row[$f]) ? str_replace(array('"', '\\'), array($fEnc.'"', $fEsc.'\\'), $row[$f]) : '';
            $line[] = $fEnc.$v.$fEnc;
        }

        return join($fDel, $line);
    }

    /**
     * Retrieve csv string from array
     *
     * @param array $fields
     * @return sting
     */
    public function getCsvString($fields = array()) {
        $delimiter  = $this->getVar('delimiter', ',');
        $enclosure  = $this->getVar('enclose', '');
        $escapeChar = $this->getVar('escape', '\\');

        if ($delimiter == '\t') {
            $delimiter = "\t";
        }

        $str = '';

        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false ||
                empty($enclosure) ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i=0;$i<$len;$i++) {
                    if ($value[$i] == $escapeChar) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                        $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2.$delimiter;
            } else {
                $str .= $enclosure.$value.$enclosure.$delimiter;
            }
        }
        return substr($str, 0, -1) . "\n";
    }
}
