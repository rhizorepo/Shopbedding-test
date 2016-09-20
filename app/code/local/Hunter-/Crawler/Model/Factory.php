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
class Hunter_Crawler_Model_Factory
{
    public function load($entityType, $pageUrl)
    {
        $collection = new Varien_Data_Collection_Db();
        switch ($entityType) {
            case Mage_Catalog_Model_Category::ENTITY:
                $collection = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect(array('url_path', 'name'))
                    ->addAttributeToFilter('url_path', $pageUrl);
                break;
            case Mage_Catalog_Model_Product::ENTITY:
                $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(array('url_path', 'name'))
                    ->addAttributeToFilter('url_path', $pageUrl);
                break;
            case 'cms_page':
                $collection = Mage::getModel('cms/page')->getCollection()
                    ->addFieldToSelect('page_id', 'entity_id')
                    ->addFieldToSelect('identifier', 'url_path')
                    ->addFieldToSelect('title', 'name')
                    ->addFieldToFilter('identifier', $pageUrl);
                break;
        }

        $entity = $collection->getFirstItem();
        if (!$entity->getUrlPath()) {
            /** @var Enterprise_UrlRewrite_Model_Url_Rewrite $rewritesCollection */
            $rewritesModel = $this->_getUrlRewriteModel();
            $rewriteItems = $rewritesModel->getCollection()
                ->addFieldToFilter('request_path', $pageUrl);
            foreach ($rewriteItems as $rewriteItem) {
                $targetPath = explode('/', $rewriteItem->getTargetPath());
                $targetEntityType = isset($targetPath[0]) && isset($targetPath[1])
                    ? $targetPath[0] . '_' . $targetPath[1]
                    : '';
                if ($targetEntityType === $entityType && isset($targetPath[4])) {
                    $targetEntityId = $targetPath[4];
                    if (Mage_Catalog_Model_Category::ENTITY === $entityType) {
                        $entity = Mage::getModel('catalog/category')->load($targetEntityId);
                    } elseif (Mage_Catalog_Model_Product::ENTITY === $entityType) {
                        $entity = Mage::getModel('catalog/product')->load($targetEntityId);
                    }
                    break;
                }
            }
        }

        return $entity;
    }
	
    protected function _getUrlRewriteModel() {
		$model 			= null;
		$isVersionEE13 	= ('true' == (string) Mage::getConfig()->getNode('modules/Enterprise_UrlRewrite/active'));
		
		if($isVersionEE13) {
			$model = Mage::getModel('enterprise_urlrewrite/url_rewrite');
		} else {
			$model = Mage::getModel('core/url_rewrite');
		}
		
		return $model;
    }
	
}
