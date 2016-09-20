<?php
/**
 * flat table test model. 
 * @package
 * @deprecated after 0.0.2
 */
class Gorilla_Heartbeat_Model_Tests_Flattable 
    extends Mage_Core_Model_Abstract 
    implements Gorilla_Heartbeat_Model_TestsInterface
{
    /**
     * Returns true if test enabled
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isEnabled()
    {
        return false;
        /* this test is deprecated */

        /* if flat catalog is active */
        if (Mage::getStoreConfig('catalog/frontend/flat_catalog_product')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns true if test passed
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isPassed()
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $flatTables = $read->query('SHOW TABLES LIKE "catalog_product_flat_%"')->fetchAll();
        
        $flatProductIds = array();
        foreach ($flatTables as $flatTable) {
            $items = $read->query('SELECT `entity_id` FROM `' . array_pop($flatTable) . '`')->fetchAll();
            
            foreach ($items as $item) {
                $flatProductIds[$item['entity_id']] = $item['entity_id'];
            }
        }
        
        $productsCountArray = $read->query("
            SELECT COUNT(DISTINCT `cpe`.`entity_id`)  FROM `catalog_product_entity` AS `cpe`, `catalog_product_entity_int` AS `cpei`, `eav_attribute` AS `ea`, `eav_entity_type` AS `eet`
            WHERE `eet`.`entity_type_code` = 'catalog_product'
            AND `eet`.`entity_type_id` = `ea`.`entity_type_id`
            AND `ea`.`attribute_code` = 'status'
            AND `cpei`.`attribute_id` = `ea`.`attribute_id`
            AND `cpei`.`entity_id` = `cpe`.`entity_id`
            AND `cpei`.`value` = '1'")
                        ->fetch();
        $productsCount = array_pop($productsCountArray);
        
        if ($productsCount == count($flatProductIds)) {
            return true;
        }

        Mage::helper('heartbeat')->log('Flattable Test Error. Products count in catalog doesn\'t match products count in the flat table(s).', null, 'gorilla_heartbeat.log');
        return false;
    }
    
    /**
     * Returns result of the rest
     * @return mixed
     */
    public function getResult()
    {
        return true;
    }

    public function getRecommendations() {
        return 'Please try to make reindex.';
    }

    public function process(){}
}