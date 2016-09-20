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
require_once Mage::getModuleDir('controllers', 'Mage_Review') . DS . 'ProductController.php';

class MageWorkshop_DetailedReview_ProductController extends Mage_Review_ProductController
{
    /**
     * @var array
     */
    protected $_availableFields = array(
        'customer_email',
        'title',
        'video',
        'image',
        'detail',
        'good_detail',
        'no_good_detail',
        'nickname',
        'location',
        'age',
        'height',
        'response',
        'sizing',
        'body_type',
        'pros',
        'cons',
        'recommend_to'
    );

    /**
     * @return $this|Exception|void
     */
    public function postAction()
    {
        $helper = Mage::helper('detailedreview');
        $ajaxSubmit = Mage::getStoreConfig('detailedreview/settings/submit_review_ajax');
        $responseJson = array('success' => false);
        $helperJson = Mage::helper('core');
        /* @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::postAction();
            return $this;
        }
        if (Mage::getStoreConfig('detailedreview/settings/show_honeypot')) {
            if ($this->getRequest()->getParam('middlename', null)) {
                $session->addError($helper->__('Something went wrong. Please try again'));
                $this->_redirectReferer();
            }
        }
        if (Mage::getStoreConfig('detailedreview/settings_customer/write_review_once')) {
            $data = Mage::helper('detailedreview')->getCustomerData();
            $result = array();
            if ($data && $data['type'] && $data['value']) {
                $result = Mage::helper('detailedreview')->getReviewsPerProductByCustomer($data, $this->getRequest()->getParam('id'));
            }
            if (!empty($result)) {
                if ($ajaxSubmit) {
                    $responseJson['html'] = false;
                    $responseJson['redirect'] = $this->_getRefererUrl();
                    $responseJson['messages'][] = $helper->__('Product already reviewed by You.');
                    $this->_wrapMessages($responseJson);
                    $this->getResponse()->setBody($helperJson->jsonEncode($responseJson));
                } else {
                    $session->addError($helper->__('Product already reviewed by You'));
                    $this->_redirectReferer();
                }
                return $this;
            }
        }
        if (Mage::getStoreConfig('detailedreview/settings_customer/only_verified_buyer')) {
            $data = Mage::helper('detailedreview')->getCustomerData();
            $result = array();
            if ($data && $data['type'] && $data['value']) {
                $result = Mage::helper('detailedreview')->getOrderedProductsByCustomer($data);
            }
            if (!in_array($this->getRequest()->getParam('id'), $result)) {
                if ($ajaxSubmit) {
                    $responseJson['html'] = false;
                    $responseJson['redirect'] = $this->_getRefererUrl();
                    $responseJson['messages'][] = $helper->__('Only verified buyer can write review');
                    $this->_wrapMessages($responseJson);
                    $this->getResponse()->setBody($helperJson->jsonEncode($responseJson));
                } else {
                    $session->addError($helper->__('Only verified buyer can write review'));
                    $this->_redirectReferer();
                }
                return $this;
            }
        }

        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $captchaIsValid = true;
            if (Mage::getStoreConfig('detailedreview/captcha/enabled')) {
                $validateCaptcha = $session->getCaptchaIsValid();
                if (!$validateCaptcha) {
                    $session->addError($helper->__('You have entered wrong captcha.'));
                    $captchaIsValid = false;
                }
            }

            if (Mage::helper('detailedreview')->checkFieldAvailable('user_pros_and_cons', 'form')) {
                $types = array(
                    'user_pros' => 'pros',
                    'user_cons' => 'cons'
                );
                foreach ($types as $type => $value) {
                    if (isset($data[$type])) {
                        $storeId = array(Mage::app()->getStore()->getId());
                        $userProsCons = explode(',', $data[$type]);
                        foreach ($userProsCons as $item) {
                            $item = trim(htmlspecialchars($item));
                            if ($item != '') {
                                if ($value == 'pros') {
                                    $entityType = MageWorkshop_DetailedReview_Model_Source_EntityType::PROS;
                                } else {
                                    $entityType = MageWorkshop_DetailedReview_Model_Source_EntityType::CONS;
                                }
                                $prosConsCollection = Mage::getModel('detailedreview/review_proscons')->getCollection()
                                    ->setType($entityType)
                                    ->addFieldToFilter('name',array('eq' => $item));
                                /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $prosConsItem */
                                $prosConsItem = $prosConsCollection->getFirstItem();
                                if ($prosConsItem->getId()){
                                    if (!isset($data[$value]) || !is_array($data[$value])) {
                                        $data[$value] = array();
                                    }
                                    $data[$value][] = $prosConsItem->getId();
                                } else {
                                    $prosConsItem->setEntityType($entityType)
                                        ->setStoreIds($storeId)
                                        ->setName($item)
                                        ->setStatus(MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_DISABLED)
                                        ->setWroteBy(MageWorkshop_DetailedReview_Model_Source_Common_Wroteby::CUSTOMER);
                                    $prosConsItem->save();
                                    if (!isset($data[$value]) || !is_array($data[$value])) {
                                        $data[$value] = array();
                                    }
                                    $data[$value][] = $prosConsItem->getEntityId();
                                }
                            }
                        }
                    }
                }
            }

            // Check if customer write reviews without approving
            $autoApproveFlag = $helper->getAutoApproveFlag();

            /* @var MageWorkshop_DetailedReview_Model_Review $review */
            $review = Mage::getModel('review/review')->setData($this->_cropReviewData($data));

            $files = $helper->uploadImages();
            if (!empty($files['images'])) {
                $review->setData('image', implode(',', $files['images']));
            }

            $validate = $review->validate();
            if ($validate === true && $files['success'] && $captchaIsValid) {
                Mage::dispatchEvent('detailedreview_product_review_post', array(
                    'review' => $review,
                    'form_data' => $data
                ));
                try {
                    $customerIdentifier = Mage::getModel('drreminder/customerIdentifier');
                    $customerIdentifier->setData(json_decode(Mage::getModel('core/cookie')->get('customerIdentifier'), true));
                    if ($customerIdentifier) {
                        $type = $customerIdentifier->getType();
                        $value = $customerIdentifier->getValue();
                    }
                    if (isset($value) && isset($type) && $type == 'customer_email') {
                        $review->setCustomerEmail($value);
                    }
                    if (isset($value) && isset($type) && $type == 'customer_id') {
                        $customerId = $value;
                    }
                    $customerId = isset($customerId) ? $customerId : Mage::getSingleton('customer/session')->getCustomerId();
                    if ($customerId) {
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                        $review->setCustomerEmail($customer->getEmail());
                    }
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId())
                            ->setStatusId($autoApproveFlag ? Mage_Review_Model_Review::STATUS_APPROVED : Mage_Review_Model_Review::STATUS_PENDING)
                            ->setCustomerId((int) $customerId)
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->setStores(array(Mage::app()->getStore()->getId()))
                            ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }
                    Mage::dispatchEvent('detailedreview_send_new_review_email_to_admin', array(
                        'review' => $review
                    ));
                    Mage::dispatchEvent('detailedreview_send_new_review_email_to_customer', array(
                        'review' => $review
                    ));
                    $review->aggregate();
                    if ($ajaxSubmit) {
                        $responseJson['success'] = true;
                        if ($autoApproveFlag) {
                            $responseJson['messages'][] = $helper->__('Your review has been added.');
                        } else {
                            $responseJson['messages'][] = $helper->__('Your review has been accepted for moderation.');
                        }
                    } else {
                        if($autoApproveFlag) {
                            $session->addSuccess($helper->__('Your review has been added.'));
                        } else {
                            $session->addSuccess($helper->__('Your review has been accepted for moderation.'));
                        }
                    }
                } catch (Exception $e) {
                    if ($ajaxSubmit) {
                        $responseJson['type'] = 'error';
                        $responseJson['content'] = '<p>' . $helper->__('Unable to post the review.') . '</p>';
                        $this->getResponse()->setBody($helperJson->jsonEncode($responseJson));
                        return $e;
                    } else {
                        $session->setFormData($data);
                        $session->addError($helper->__('Unable to post the review.'));
                    }
                }
            } else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $responseJson['messages'][] = $errorMessage;
                        if (!$ajaxSubmit) {
                            $session->addError($errorMessage);
                        }
                    }
                } else {
                    if ($ajaxSubmit) {
                        $responseJson['type'] = 'error';
                        $responseJson['messages'][] = $helper->__('Unable to post the review.');
                    } else {
                        $session->addError($helper->__('Unable to post the review.'));
                    }
                }
                if (!$files['success']) {
                    $responseJson['type'] = 'notice';
                    foreach ($files['errors'] as $imageName => $errorMessages) {
                        foreach($errorMessages as $message) {
                            $responseJson['messages'][] = $this->__('Image \'%s\' has the following problem: ', $imageName) . $message;
                            if (!$ajaxSubmit) {
                                $session->addError($message);
                            }
                        }
                    }
                }
            }
            if ($ajaxSubmit) {
                $responseJson['html'] = false;
                if ($responseJson['success'] && $autoApproveFlag) {
                    $this->loadLayout();
                    $block = $this->getLayout()->getBlock('reviews_wrapper');
                    if ($html = $block->getChildHtml('review-wrapper')) {
                        $responseJson['html'] = $this->_escapeTags($html);
                    }
                }
                $responseJson['redirect'] = $this->_getRefererUrl();
                $this->_wrapMessages($responseJson);
                $this->getResponse()
                    ->setBody($this->_escapeTags($helperJson->jsonEncode($responseJson)));
            } else {
                if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
                    $this->_redirectUrl($redirectUrl);
                    return $this;
                }
                $referrerUrl = $this->_getRefererUrl();
                if ( preg_match('/.*\&show_popup=1.*/', $referrerUrl) ) {
                    $this->_redirectUrl(preg_replace('/(.*)\&show_popup=1(.*)/', '$1$2', $referrerUrl));
                    return $this;
                }
                $this->_redirectReferer();
            }
        }
    }

    /**
     * @param string $string
     * @return mixed
     */
    protected function _escapeTags($string)
    {
        return str_replace('<', '[[', $string);
    }

    /**
     * validate captcha
     */
    public function checkCaptchaAction()
    {
        $params = $this->getRequest()->getParams();
        $privateKey = Mage::getStoreConfig('detailedreview/captcha/private_key');
        $reCaptcha = Mage::getModel('detailedreview/reCaptchaWrapper_reCaptcha', array('secret' => $privateKey));

        $captchaIsValid = false;
        if (isset($params['g-recaptcha-response'])) {
            $resp = $reCaptcha->verifyResponse(
                Mage::app()->getRequest()->getServer('REMOTE_ADDR'),
                $params['g-recaptcha-response']
            );
            if (isset($resp) && $resp->success) {
                $captchaIsValid = true;
            }
        }

        Mage::getSingleton('core/session')->setCaptchaIsValid($captchaIsValid);
        $this->getResponse()->setBody($captchaIsValid ? 'valid' : 'invalid');
    }

    /**
     * Show list of product's reviews
     */
    public function listAction()
    {
        if(!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::listAction();
        } else {
            if ($product = $this->_initProduct()) {
                Mage::register('productId', $product->getId());
                $this->getResponse()->setRedirect($product->getProductUrl());
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }

    /**
     * Show details of one review
     */
    public function viewAction()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::viewAction();
        } else {
            $review = $this->_loadReview((int) $this->getRequest()->getParam('id'));
            if (!$review) {
                $this->_forward('noroute');
                return;
            }

            $product = $this->_loadProduct($review->getEntityPkValue());
            if (!$product) {
                $this->_forward('noroute');
                return;
            }
            $this->getResponse()->setRedirect($product->getProductUrl());

            $this->loadLayout();
            $this->_initLayoutMessages('review/session');
            $this->_initLayoutMessages('catalog/session');
            $this->renderLayout();
        }
    }

    public function getReviewsByAjaxAction()
    {
        $responseData = array(
            'html' => '',
            'reviewsCount' => array()
        );
        try {
            if (!$productId = (int) $this->getRequest()->getParam('product_id')) {
                Mage::throwException(Mage::helper('detailedreview')->__('Unable to load review list. Please, contact support is this issue remains.'));
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            Mage::register('product', $product);
            Mage::register('current_product', $product);

            $layout = $this->getLayout();
            $layout->getUpdate()->load(strtolower($this->getFullActionName()));
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
            $responseData['html'] = $this->renderLayout()->getResponse()->getBody();

            /** @var MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed $reviewDetailsBlock */
            $reviewDetailsBlock = $this->getLayout()->createBlock('detailedreview/rating_entity_detailed');
            foreach ($reviewDetailsBlock->getAvailableDateRanges() as $key => $val) {
                $responseData['reviewsCount'][$key] = $reviewDetailsBlock->getQtyByRange($key);
            }
            $responseData['countReviewsWithRating'] = $reviewDetailsBlock->calculateSummary()->getCountReviewsWithRating();
            $qtyMarks = $reviewDetailsBlock->getQtyMarks();
            for ($key=5;$key>0;$key--) {
                if (array_key_exists($key, $qtyMarks)) {
                    $responseData['qtyMarks'][$key] = $qtyMarks[$key];
                } else {
                    $responseData['qtyMarks'][$key] = 0;
                }
            }
            $reviewSizing = Mage::getSingleton('detailedreview/review_sizing');
            $sizing = $reviewDetailsBlock->getAverageSizing();
            $responseData['averageSizing']['optionWidth'] = $reviewSizing->getOptionWidth($sizing);
            $responseData['averageSizing']['indent'] = $reviewSizing->getIndent($sizing);
            $responseData['averageSizing']['optionValue'] = $reviewSizing->getOptionValue($sizing);
        } catch (Exception $e) {
            Mage::logException($e);
            $messageBlock = $this->getLayout()->createBlock('core/messages');
            $responseData['html'] = $messageBlock->addError($e->getMessage())->toHtml();
        }
        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse();
        $response->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $response->setBody(Mage::helper('core')->jsonEncode($responseData));
    }

    public function getShortLinkAction()
    {
        $bitlyResponse = array();
        $longUrl = urlencode($this->getRequest()->getParam('url'));
        $bitly_login = Mage::getStoreConfig('detailedreview/social_share_optios/bitly_login');
        $bitly_apikey = Mage::getStoreConfig('detailedreview/social_share_optios/bitly_api_key');
        if ($bitly_login && $bitly_apikey) {
            $bitlyResponse = json_decode(file_get_contents("http://api.bit.ly/v3/shorten?login={$bitly_login}&apiKey={$bitly_apikey}&longUrl={$longUrl}&format=json"));
        } else {
            $bitlyResponse['message'] = $this->__('Service Temporarily Unavailable');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($bitlyResponse));
    }

    /**
     * @param array $responseJson
     * @return $this
     */
    protected function _wrapMessages(&$responseJson) {
        $responseJson['content'] = '';
        foreach($responseJson['messages'] as $message) {
            $responseJson['content'] .= '<p>' . $message . '</p>';
        }
        $responseJson['messages'] = $responseJson['content'];
        unset($responseJson['content']);
        return $this;
    }

    public function checkWriteOnceAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $result = array();
        if (Mage::getStoreConfig('detailedreview/settings_customer/write_review_once') && $productId) {
            $data = Mage::helper('detailedreview')->getCustomerData();
            if ($data && $data['type'] && $data['value']) {
                $result = Mage::helper('detailedreview')->getReviewsPerProductByCustomer($data, $productId);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function checkBuyerProductsAction()
    {
        $result = array();
        if (Mage::getStoreConfig('detailedreview/settings_customer/only_verified_buyer')) {
            $data = Mage::helper('detailedreview')->getCustomerData();
            if ($data && $data['type'] && $data['value']) {
                $result = Mage::helper('detailedreview')->getOrderedProductsByCustomer($data);
            } else {
                $result['status'] = 'error';
//                $result['message'] = Mage::helper('detailedreview')->__('Only verified buyer can write review');
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Crops POST values
     * @param array $reviewData
     * @return array
     */
    protected function _cropReviewData(array $reviewData)
    {
        $croppedValues = array();
        $allowedKeys = array_fill_keys($this->_availableFields, true);

        foreach ($reviewData as $key => $value) {
            if (isset($allowedKeys[$key])) {
                $croppedValues[$key] = $value;
            }
        }

        return $croppedValues;
    }
}
