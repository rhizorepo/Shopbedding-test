<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DetailedReview_Model_Uninstall extends Mage_Core_Model_Abstract
{
    protected $attributesCode = array(
        'is_banned_write_review' => array(
            'entity_type_code' => 'customer'
        ),
        'review_fields_available' => array(
            'entity_type_code' => 'catalog_category'
        ),
        'use_parent_review_settings' => array(
            'entity_type_code' => 'catalog_category'
        ),
        'use_parent_proscons_settings' => array(
            'entity_type_code' => 'catalog_category'
        ),
        'pros' => array(
            'entity_type_code' => 'catalog_category'
        ),
        'cons' => array(
            'entity_type_code' => 'catalog_category'
        ),
        'popularity_by_sells' => array(
            'entity_type_code' => 'catalog_product'
        ),
        'popularity_by_reviews' => array(
            'entity_type_code' => 'catalog_product'
        ),
        'popularity_by_rating' => array(
            'entity_type_code' => 'catalog_product'
        )
    );

    public function clearDatabaseInformation()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

        $setup->startSetup();

        $coreResource = Mage::getSingleton('core/resource');

        $reviewHelpfulTable = $coreResource->getTableName('detailedreview/review_helpful');
        $authorIpsTable = $coreResource->getTableName('detailedreview/author_ips');
        $prosCons = $coreResource->getTableName('detailedreview/review_proscons');
        $prosConsStore = $coreResource->getTableName('detailedreview/review_proscons_store');
        $reviewDetailTable = $coreResource->getTableName('review/review_detail');
        $reviewDetailColumns = array('remote_addr','sizing','body_type','location','age','height','good_detail','no_good_detail','response','image','video','pros','cons','recommend_to','customer_email');
        $coreResourceTable = $coreResource->getTableName('core/resource');

        $sql  = "DROP TABLE IF EXISTS `$reviewHelpfulTable`;";
        $sql .= "DROP TABLE IF EXISTS `$authorIpsTable`;";
        $sql .= "DROP TABLE IF EXISTS `$prosCons`;";
        $sql .= "DROP TABLE IF EXISTS `$prosConsStore`;";

        $setup->run($sql);

        foreach ($reviewDetailColumns as $column) {
            if ($coreResource->getConnection('core_write')->tableColumnExists($reviewDetailTable,$column)) {
                $coreResource->getConnection('core_write')->dropColumn($reviewDetailTable,$column);
            }
        }

        $setup->deleteTableRow($coreResourceTable,'code','detailedreview_setup');

        $customerEavAttribute = $coreResource->getTableName('customer/eav_attribute');
        $catalogEavAttribute = $coreResource->getTableName('catalog/eav_attribute');

        foreach ($this->attributesCode as $attributeCode => $attribute) {
            $entityTypeCode = $attribute['entity_type_code'];
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($entityTypeCode, $attributeCode);
            $backendTable = $attribute->getBackendTable();
            if ($attributeId = $attribute->getId()) {
                $attribute->delete();
                $eavAttribute = ($entityTypeCode == 'customer') ? $customerEavAttribute : $catalogEavAttribute;
                $setup->deleteTableRow($eavAttribute,'attribute_id',$attributeId);
                $setup->deleteTableRow($backendTable,'attribute_id',$attributeId);
            }
        }

        $setup->endSetup();
    }

}
