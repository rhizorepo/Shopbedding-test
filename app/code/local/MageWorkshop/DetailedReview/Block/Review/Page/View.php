<?php
/**
 * MageWorkshop
 * Copyright (C) 2016  MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Block_Review_Page_View extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if (Mage::getStoreConfig('detailedreview/settings/review_form_separate')) {
            return parent::_toHtml();
        }
    }

    public function getProductData()
    {
        if (!($product = Mage::registry('current_product'))) {
            $productId = (int) Mage::app()->getRequest()->getParam('product');
            if ($productId) {
                $product = Mage::getModel('catalog/product')->load($productId);
                Mage::register('current_product', $product);
            }
        }
        return $product;
    }

    public function getReviewsPerProductByCustomer($productId)
    {
        if (Mage::getStoreConfig('detailedreview/settings_customer/write_review_once') && $productId) {
            $data = Mage::helper('detailedreview')->getCustomerData();
            $reviewIds = array();
            if ($data && $data['type'] && $data['value']) {
                $reviewIds = Mage::helper('detailedreview')->getReviewsPerProductByCustomer($data, $productId);
            }
            if (!empty($reviewIds)) {
                return true;
            }
        }
        return false;
    }

}
