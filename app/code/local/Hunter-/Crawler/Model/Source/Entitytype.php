<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category    Hunter
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */
 
class Hunter_Crawler_Model_Source_Entitytype
{
    public static function toOptionArray()
    {
        return array(
            Mage_Catalog_Model_Category::ENTITY => 'Category',
            Mage_Catalog_Model_Product::ENTITY  => 'Product',
            'cms_page'                          => 'CMS Page'
        );
    }
}
