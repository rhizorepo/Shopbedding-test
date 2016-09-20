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
 * @package     Mage_Review
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

//Zend_Debug::dump(Mage::getBaseDir('base'));
//Zend_Debug::dump(get_include_path());
//exit();
/**
 * Review controller
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Review/controllers/ProductController.php';
require_once Mage::getBaseDir('base').'/lib/recaptcha/recaptchalib.php';
class Shopbedding_ReviewCaptcha_ProductController extends Mage_Review_ProductController
{

    public function preDispatch()
    {
        parent::preDispatch();
        $this->getRequest()->setRouteName('review');
        // here check also for $this->getRequest->getActionName()
        // if this is your own action, make sure to do setRouteName('test') 
        // in order to use your own layout
    }

    /**
     * Submit new review action
     *
     */
    public function postAction()
    {
        $data = $this->getRequest()->getPost();
        
        //Mage::log(print_r($data, true), null, 'recaptchalib.log');
        $privatekey = "6LeCwcUSAAAAABB8ykTc1xww6dFNsDS-e6gSgexF";
        $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $data["recaptcha_challenge_field"],
                                        $data["recaptcha_response_field"]);

        if (!$resp->is_valid) {
            Mage::getModel('review/review')->setData($data);
            Mage::getSingleton('review/session')->setFormData($data);
            Mage::getSingleton('core/session')->addError($this->__('The captcha wasn\'t entered correctly. Please try it again.'));
            
            return $this->_redirectUrl($this->_getRefererUrl().'#review-form')->_redirectReferer();
        }
        
        // Set this cookie so the global message can get past the full page cache
        Mage::getModel('core/cookie')->set(Enterprise_PageCache_Model_Cookie::COOKIE_MESSAGE, true);
        parent::postAction();
    }
}

