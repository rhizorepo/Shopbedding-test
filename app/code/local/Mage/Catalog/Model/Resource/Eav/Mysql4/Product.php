<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Product entity resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Eav_Mysql4_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Abstract
{

    public function getParentProductIds($object)
    {
        $childId = $object->getId();

        $groupedProductsTable = $this->getTable('catalog/product_link');
        $groupedLinkTypeId = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;

        $configurableProductsTable = $this->getTable('catalog/product_super_link');

        $groupedSelect = $this->_getReadAdapter()->select()
            ->from(array('g'=>$groupedProductsTable), 'g.product_id')
            ->where("g.linked_product_id = ?", $childId)
            ->where("link_type_id = ?", $groupedLinkTypeId);

        $groupedIds = $this->_getReadAdapter()->fetchCol($groupedSelect);

        $configurableSelect = $this->_getReadAdapter()->select()
            ->from(array('c'=>$configurableProductsTable), 'c.parent_id')
            ->where("c.product_id = ?", $childId);

        $configurableIds = $this->_getReadAdapter()->fetchCol($configurableSelect);
        return array_merge($groupedIds, $configurableIds);
    }
}
