<?php

require_once('Mage/ProductAlert/controllers/AddController.php');

class Shopbedding_ProductAlert_AddController extends Mage_ProductAlert_AddController {

    public function preDispatch()
    {
        //parent::parent::preDispatch();

        /*        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                    $this->setFlag('', 'no-dispatch', true);
                    if(!Mage::getSingleton('customer/session')->getBeforeUrl()) {
                        Mage::getSingleton('customer/session')->setBeforeUrl($this->_getRefererUrl());
                    }
                } */
    }

    public function stockAction()
    {
	$this->getLayout()->setArea($this->_currentArea);

        //
        if (!$this->getFlag('', self::FLAG_NO_CHECK_INSTALLATION)) {
            if (!Mage::isInstalled()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                $this->_redirect('install');
                return;
            }
        }

        // Prohibit disabled store actions
        if (Mage::isInstalled() && !Mage::app()->getStore()->getIsActive()) {
            Mage::app()->throwStoreException();
        }

        if ($this->_rewrite()) {
            return;
        }

        if (!$this->getFlag('', self::FLAG_NO_START_SESSION)) {
            $checkCookie = in_array($this->getRequest()->getActionName(), $this->_cookieCheckActions)
                && !$this->getRequest()->getParam('nocookie', false);
            $cookies = Mage::getSingleton('core/cookie')->get();

            $session = Mage::getSingleton('core/session', array('name' => $this->_sessionNamespace))->start();

            if (empty($cookies)) {
                if ($session->getCookieShouldBeReceived()) {
                    $this->setFlag('', self::FLAG_NO_COOKIES_REDIRECT, true);
                    $session->unsCookieShouldBeReceived();
                    $session->setSkipSessionIdFlag(true);
                } elseif ($checkCookie) {
                    if (isset($_GET[$session->getSessionIdQueryParam()]) && Mage::app()->getUseSessionInUrl()
                        && $this->_sessionNamespace != Mage_Adminhtml_Controller_Action::SESSION_NAMESPACE
                    ) {
                        $session->setCookieShouldBeReceived(true);
                    } else {
                        $this->setFlag('', self::FLAG_NO_COOKIES_REDIRECT, true);
                    }
                }
            }
        }

        Mage::app()->loadArea($this->getLayout()->getArea());

        if ($this->getFlag('', self::FLAG_NO_COOKIES_REDIRECT)
            && Mage::getStoreConfig('web/browser_capabilities/cookies')
        ) {
            $this->_forward('noCookies', 'index', 'core');
            return;
        }

        if ($this->getFlag('', self::FLAG_NO_PRE_DISPATCH)) {
            return;
        }
        $cs = Mage::getSingleton('customer/session');
        if ( Mage::getSingleton('customer/session')->isLoggedIn() ) {

            $session = Mage::getSingleton('catalog/session');
            /* @var $session Mage_Catalog_Model_Session */
            $backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
            $productId  = (int) $this->getRequest()->getParam('product_id');
            // if (!$backUrl || !$productId) {
            //$this->_redirect('/');
            //return ;
            // }

            // Mage::log("adding update email for user id ".Mage::getSingleton('customer/session')->getId()."-- for product id ".$productId);

            if ( !$productId )
            {
                echo "Invalid product -- Please contact support.";
                return;
            }


            /* Irrelevant
            if ( !$backUrl )
            {
                echo "Invalid URL -- Please contact support.";
                return;
            }
*/

            if (!$product = Mage::getModel('catalog/product')->load($productId)) {
                /* @var $product Mage_Catalog_Model_Product */
                //$session->addError($this->__('Not enough parameters.'));
                //$this->_redirectUrl($backUrl);
                //return ;

                echo "Invalid Request -- Please contact support.";
                return;
            }

            try {
                $model = Mage::getModel('productalert/stock')
                    ->setCustomerId(Mage::getSingleton('customer/session')->getId())
                    ->setProductId($product->getId())
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $model->save();
                //   $session->addSuccess($this->__('Alert subscription has been saved.'));
            }
            catch (Exception $e) {
                // $session->addException($e, $this->__('Unable to update the alert subscription.'));

                echo "Unable to save alert subscription -- Please contact support.";
                return;
            }
            // $this->_redirectReferer();

            echo "You have signed up to receive an email alert when this product is back in stock.";
            return;
        } else {
            echo "You must be logged in to receive product update emails.";
            return;
        }
    }
}
