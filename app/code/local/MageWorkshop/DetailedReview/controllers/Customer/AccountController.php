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
require_once Mage::getModuleDir('controllers', 'Mage_Customer') . DS . 'AccountController.php';

class MageWorkshop_DetailedReview_Customer_AccountController extends Mage_Customer_AccountController
{
    /**
     * Login post action
     */
    public function loginPostAction()
    {
     
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        $session = $this->_getSession();

        if ($this->_getSession()->isLoggedIn()) {
            if ($this->getRequest()->getPost('detailedreview_login')) {
                $session->setBeforeAuthUrl(Mage::getBaseUrl());
                $this->_loginPostRedirect();
                return;
            }
            $this->_redirect('*/*/');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);

                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                            break;
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, 'exception.log'); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard') || (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard') && $this->getRequest()->getPost('detailedreview_login'))) {
                    $referrer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referrer) {
                        $referrer = Mage::helper('core')->urlDecode($referrer);
                        if ($this->_isUrlInternal($referrer)) {
                            $session->setBeforeAuthUrl($referrer);
                        }
                    }
                } elseif ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } elseif ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }

        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }
}
