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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Sales Order Email items default renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Block_Order_Email_Items_Order_Default extends Mage_Core_Block_Template
{
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

    public function getItemOptions()
    {
//        $result = array();
//        if ($options = $this->getItem()->getProductOptions()) {
//            if (isset($options['options'])) {
//                $result = array_merge($result, $options['options']);
//            }
//            if (isset($options['additional_options'])) {
//                $result = array_merge($result, $options['additional_options']);
//            }
//            if (isset($options['attributes_info'])) {
//                $result = array_merge($result, $options['attributes_info']);
//            }
//        }
//
//        return $result;

        $_product = Mage::getModel('catalog/product')->load($this->getItem()->getProductId());
        $_prodRes = $_product->getResource();
        $_prodOptions = array();
        $_prodOptions['Size']        = $_prodRes->getAttribute('size')->getFrontend()->getValue($_product);
        $_prodOptions['Color']       = $_prodRes->getAttribute('color')->getFrontend()->getValue($_product);
        $_prodOptions['Depth']       = $_prodRes->getAttribute('depth')->getFrontend()->getValue($_product);
        $_prodOptions['Drop Length'] = $_prodRes->getAttribute('drop_length')->getFrontend()->getValue($_product);
        $_options = array();
        foreach($_prodOptions as $label => $option) {
            if ($option != "No") {
                $_options[] = array("label"=>$label, "value"=>$option);
            }
        }
        return $_options;
    }

    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf('%d', $value['qty']) . ' x ' . $this->htmlEscape($value['title']) . " " . $this->getItem()->getOrder()->formatPrice($value['price']);
        } else {
            return $this->htmlEscape($value);
        }
    }

    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku'))
            return $item->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }

    /**
     * Return product additional information block
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }
}
