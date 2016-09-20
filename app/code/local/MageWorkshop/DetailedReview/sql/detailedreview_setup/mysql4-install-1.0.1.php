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

/** @var MageWorkshop_DetailedReview_Model_Mysql4_Setup $installer */
$installer = $this;
$installer->startSetup();
$reviewHelpfulTable = $installer->getTable('review_helpful');
$reviewDetailTable = $installer->getTable('review/review_detail');
$connection = $installer->getConnection();


    $installer->addAttribute('catalog_product', 'popularity_by_sells', array(
        'group' => 'Popularity',
        'type' => 'int',
        'backend' => '',
        'frontend_class' => 'validate-digits',
        'label' => 'Bestselling',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => false,
        'required' => false,
        'apply_to' => '',
        'is_configurable' => false,
        'note' => '',
        'used_in_product_listing' => true,
        'used_for_sort_by' => true
    ));

    $installer->addAttribute('catalog_product', 'popularity_by_reviews', array(
        'group' => 'Popularity',
        'type' => 'int',
        'backend' => '',
        'frontend_class' => 'validate-digits',
        'label' => 'Most Reviewed',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => false,
        'required' => false,
        'apply_to' => '',
        'is_configurable' => false,
        'note' => '',
        'used_in_product_listing' => true,
        'used_for_sort_by' => true
    ));

    $installer->addAttribute('catalog_product', 'popularity_by_rating', array(
        'group' => 'Popularity',
        'type' => 'int',
        'backend' => '',
        'frontend_class' => 'validate-digits',
        'label' => 'Highly Rated',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => false,
        'required' => false,
        'apply_to' => '',
        'is_configurable' => false,
        'note' => '',
        'used_in_product_listing' => true,
        'used_for_sort_by' => true
    ));

    if ($connection->isTableExists($reviewHelpfulTable) != true) {
        $table = $connection
            ->newTable($reviewHelpfulTable)
            ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable' => false,
                'primary' => true,
                'identity' => true
            ), 'Id')
            ->addColumn('review_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable' => false
            ), 'Review Id')
            ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable' => false
            ), 'Customer Id')
            ->addColumn('is_helpful', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
                'nullable' => false
            ), 'Is helpful')
            ->setComment('Review Helpful Table');
        $table->setOption('type', 'MyISAM');
        $table->setOption('charset', 'utf8');

        $connection->createTable($table);
    }

    $connection->addColumn($reviewDetailTable, 'good_detail', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Good Detail'
        )
    );
    $connection->addColumn($reviewDetailTable, 'no_good_detail', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'No Good Detail'
        )
    );
    $connection->addColumn($reviewDetailTable, 'response', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Response'
        )
    );
    $connection->addColumn($reviewDetailTable, 'image', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Image'
        )
    );
    $connection->addColumn($reviewDetailTable, 'video', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'length' => 255,
            'comment' => 'Video'
        )
    );

$installer->endSetup();
