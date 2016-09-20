<?php
/**
 * PHPExcel library
 *
 * http://phpexcel.codeplex.com/
 */
require_once(Mage::getBaseDir('lib') . '/PHPExcel/PHPExcel.php');
require_once(Mage::getBaseDir('lib') . '/PHPExcel/IOFactory.php');

/**
 * Update Feed model
 *
 * Class Shopbedding_UpdateFeed_Model_Update
 */
class Shopbedding_UpdateFeed_Model_Update extends Mage_Core_Model_Abstract
{
    /**
     * XLSX document
     */
    const DOCUMENT_FORMAT_EXCEL = 'Excel2007';

    /**
     * CSV document
     */
    const DOCUMENT_FORMAT_CSV = 'CSV';

    /**
     * Document status column index
     */
    const DOCUMENT_STATUS_COLUMN = 30;

    /**
     * Document column names
     *
     * @var array
     */
    protected $documentColumns = array();

    /**
     * Do feed file update
     *
     * @param bool $manualRun
     * @return bool
     */
    public function doUpdate($manualRun = false)
    {
        if (!Mage::getStoreConfigFlag('shopbedding_update_feed/settings/findfeed_update/runUpdate') && !$manualRun) {
            return false;
        }

        if ($this->_updateFeedFile()) {
            return true;
        }
        return false;
    }

    /**
     * Update feed file method
     */
    protected function _updateFeedFile()
    {
        try {
            if (!($file = Mage::helper('shopbedding_updatefeed')->getFeedFilePath())) {
                return false;
            }

            // increase memory (3000 products takes 230 mb)
            ini_set('memory_limit','700M');

            /** @var PHPExcel_Reader_Excel2007 $excel */
            $excel = PHPExcel_IOFactory::createReader(self::DOCUMENT_FORMAT_EXCEL);
            $excel = $excel->load($file);
            $excel->setActiveSheetIndex(0);

            $highestRow         = $excel->getActiveSheet()->getHighestRow();
            $highestColumn      = $excel->getActiveSheet()->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

            // retrieve names of worksheet columns
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $value = $excel->getActiveSheet()->getCellByColumnAndRow($col, 1)->getValue();
                $this->documentColumns[$col] = trim($value);
            }

            $skuColumn   = $this->_getColumnIndexByName('Model');
            $priceColumn = $this->_getColumnIndexByName('Current Price');
            $stockColumn = $this->_getColumnIndexByName('InStock');

            $skuIds = array();
            for ($row = 2; $row <= $highestRow; ++$row)
            {
                 // Get all products sku from document
                 $skuIds[] = "'" . trim($excel->getActiveSheet()->getCellByColumnAndRow($skuColumn, $row)->getValue()) . "'";
            }

            $skuIds = implode(', ', $skuIds);
            $query  = $this->_getProductsQuery($skuIds);
            $productsCollection = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);

            // Create products arrays with index by sku
            $products = array();
            foreach ($productsCollection as $productData) {
                $products[trim(strtolower($productData['sku']))] = $productData;
            }
            unset($productsCollection);

            // Update data in xlsx document:
            for ($row = 2; $row <= $highestRow; ++$row)
            {
                $productSku = trim(strtolower($excel->getActiveSheet()->getCellByColumnAndRow($skuColumn, $row)->getValue()));
                // Case when product not exists in database
                if (!array_key_exists($productSku, $products)) {
                    // mark the product as NOT FOUND
                    $excel->getActiveSheet()->setCellValueByColumnAndRow(self::DOCUMENT_STATUS_COLUMN, $row, 'NOT_FOUND');
                    continue;
                }

                $status = trim($excel->getActiveSheet()->getCellByColumnAndRow(self::DOCUMENT_STATUS_COLUMN, $row)->getValue());
                // if product marked as NOT_FOUND but exists in DB
                if (strpos($status, 'NOT_FOUND') !== false) {
                    $excel->getActiveSheet()->setCellValueByColumnAndRow(self::DOCUMENT_STATUS_COLUMN, $row, '');
                }

                $oldProductPrice = $excel->getActiveSheet()->getCellByColumnAndRow($priceColumn, $row)->getValue();
                $oldProductStockStatus = $excel->getActiveSheet()->getCellByColumnAndRow($stockColumn, $row)->getValue();
                $newProductSpecialPrice = Mage::helper('core')->currency($products[$productSku]['special_price'], false, false);

                if ($oldProductPrice != $products[$productSku]['special_price']) {
                    $excel->getActiveSheet()->setCellValueByColumnAndRow($priceColumn, $row, $newProductSpecialPrice);
                }

                if ($oldProductStockStatus != $products[$productSku]['in_stock']) {
                    $excel->getActiveSheet()->setCellValueByColumnAndRow($stockColumn, $row, $products[$productSku]['in_stock']);
                }
            }

            // creating xsls file
            $objWriter = PHPExcel_IOFactory::createWriter($excel, self::DOCUMENT_FORMAT_EXCEL);
            $objWriter->save($file);

            // creating csv file
            $objWriter = PHPExcel_IOFactory::createWriter($excel, self::DOCUMENT_FORMAT_CSV);
            $objWriter->save(str_replace('.xlsx', '.csv', $file));
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Get column index by name
     *
     * @param $name
     * @return mixed
     */
    protected function _getColumnIndexByName($name)
    {
        if (count($this->documentColumns)) {
            return array_search($name, $this->documentColumns);
        }
    }

    /**
     * Get products by sku ids
     *
     * @param $skuIds
     * @return string
     */
    protected function _getProductsQuery($skuIds)
    {
        /** @var Mage_Core_Model_Resource_Setup $resource */
        $resource = $resource = Mage::getSingleton('core/resource');

        $query = <<<QUERY
            SELECT `e`.*, `at_qty`.`qty`,
                          `at_status`.`value` AS `status`,
                          `at_visibility`.`value` AS `visibility`,
                          `at_special_price`.`value` AS `special_price`,
                          `cataloginventory_stock_item`.`is_in_stock` AS `in_stock`
            FROM `{$resource->getTableName('catalog_product_entity')}` AS `e`
            LEFT JOIN `{$resource->getTableName('cataloginventory_stock_item')}` AS `at_qty`
                ON (at_qty.`product_id`=e.entity_id)
                    AND (at_qty.stock_id=1)
            LEFT JOIN `{$resource->getTableName('catalog_product_entity_decimal')}` AS `at_special_price`
                ON (`at_special_price`.`entity_id` = `e`.`entity_id`)
                    AND (`at_special_price`.`attribute_id` = '65')
                    AND (`at_special_price`.`store_id` = 0)
            INNER JOIN `{$resource->getTableName('catalog_product_entity_int')}` AS `at_status`
                ON (`at_status`.`entity_id` = `e`.`entity_id`)
                    AND (`at_status`.`attribute_id` = '84') AND (`at_status`.`store_id` = 0)
            INNER JOIN `{$resource->getTableName('cataloginventory_stock_item')}`
                ON e.entity_id = {$resource->getTableName('cataloginventory_stock_item')}.product_id
            INNER JOIN `{$resource->getTableName('catalog_product_entity_int')}` AS `at_visibility`
                ON (`at_visibility`.`entity_id` = `e`.`entity_id`)
                    AND (`at_visibility`.`attribute_id` = '91')
                    AND (`at_visibility`.`store_id` = 0)
            WHERE (e.sku IN({$skuIds}))
QUERY;

        return  $query;
    }
}