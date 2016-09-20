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
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Shopbedding BYOS Controller
 * 
 */
class Shopbedding_Byos_AddController extends Mage_Core_Controller_Front_Action {

    protected $storeId = null;

    /**
     * Add all items from byos to shopping cart
     *
     */
    public function allcartAction() {
        $this->storeId = Mage::app()->getStore()->getId();
        $response = array("error" => 1, "message" => "");

        //Make sure the request is POST
        if ($this->getRequest()->isPost()) {

            // get POST data and parse it
            $post = $this->getRequest()->getPost();
            $itemsString = $post['setItems'];
            $collection = array();
            foreach (explode(",", $itemsString) as $itemStr) {
                $prod = explode("|", $itemStr);
                if (array_key_exists($prod[0], $collection)) {
                    $collection[$prod[0]] += $prod[1];
                } else {
                    $collection[$prod[0]] = $prod[1];
                }
            }

            // setup some more variables
            $messages = array();
            $addedItems = array();
            $notAdded = array();
            $notSalable = array();
            $hasOptions = array();
            $isGrouped = array();
            $cart = Mage::getSingleton('checkout/cart');

            // add each item to cart
            foreach ($collection as $itemId => $qty) {
                $product = Mage::getModel('catalog/product')
                                ->setStoreId($this->storeId)
                                ->load($itemId);
                try {
                    if ($this->addToCart($cart, $product, $qty)) {
                        $addedItems[] = $product;
                    }
                } catch (Mage_Core_Exception $e) {
                    if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
//                        $notSalable[] = $product;
                        $notAdded[] = $product;
                    } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
//                        $hasOptions[] = $product;
                        $notAdded[] = $product;
                    } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_IS_GROUPED_PRODUCT) {
//                        $isGrouped[] = $product;
                        $notAdded[] = $product;
                    } else {
//                        $messages[] = $e->getMessage();
                        $notAdded[] = $product;
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
//                    $messages[] = Mage::helper('wishlist')->__('Cannot add the item to shopping cart.');
                    $notAdded[] = $product;
                }
            }


            // redirection after add
            if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
                $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
            } else {
                $redirectUrl = $this->_getRefererUrl();
            }

//            if ($notSalable) {
//                $products = array();
//                foreach ($notSalable as $item) {
//                    $products[] = '"' . $item->getProduct()->getName() . '"';
//                }
//                $messages[] = Mage::helper('wishlist')->__('Unable to add the following product(s) to shopping cart: %s.', join(', ', $products));
//            }
//            if ($isGrouped) {
//                $products = array();
//                foreach ($isGrouped as $item) {
//                    $products[] = '"' . $item->getProduct()->getName() . '"';
//                }
//                $messages[] = Mage::helper('wishlist')->__('Product(s) %s are grouped. Each of them can be added to cart separately only.', join(', ', $products));
//            }
//            if ($hasOptions) {
//                $products = array();
//                foreach ($hasOptions as $item) {
//                    $products[] = '"' . $item->getProduct()->getName() . '"';
//                }
//                $messages[] = Mage::helper('wishlist')->__('Product(s) %s have required options. Each of them can be added to cart separately only.', join(', ', $products));
//            }

            if ($addedItems) {
                $products = array();
                foreach ($addedItems as $product) {
                    $products[] = '"' . $product->getName() . '"';
                }

                // save cart and collect totals
                $cart->save()->getQuote()->collectTotals();

                $response["error"] = 0;
                $response["message"] = "Items added to cart successfully.";

                /**
                 * @TODO: take care of partial adds...
                 */
                if (!empty($notAdded)) {
                    continue;
                }

                die(json_encode($response));
            }
        } else {
            $response["error"] = 1;
            $reaponse["message"] = "Error in request";
            die(json_encode($response));
        }
    }

    /**
     * Add an item to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled //or unvisible products
     *
     */
    protected function addToCart(Mage_Checkout_Model_Cart $cart, $product, $qty) {

        if (Mage_Catalog_Model_Product_Type::TYPE_GROUPED == $product->getTypeId()) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_IS_GROUPED_PRODUCT);
        }

        $product->setQty($qty);

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

//        if (!$product->isVisibleInSiteVisibility()) {
//            if ($product->getStoreId() == $this->storeId) {
//                return false;
//            }
//            $urlData = Mage::getResourceSingleton('catalog/url')
//                ->getRewriteByProductStore(array($product->getId() => $this->storeId));
//            if (!isset($urlData[$product->getId()])) {
//                return false;
//            }
//            $product->setUrlDataObject(new Varien_Object($urlData));
//            $visibility = $product->getUrlDataObject()->getVisibility();
//            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
//                return false;
//            }
//        }

        if (!$product->isSalable()) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_NOT_SALABLE);
        }

        if ($product->getTypeInstance(true)->hasRequiredOptions($product)) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS);
        }


        $eventArgs = array(
            'product' => $product,
            'qty' => $qty,
            'additional_ids' => array(),
            'request' => $this->getRequest(),
            'response' => $this->getResponse(),
        );
        Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);
        $cart->addProduct($product, $qty);
        Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);
//        $cart->save();
//        Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$product));


        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($this->storeId);
        }

        return true;
    }

}

