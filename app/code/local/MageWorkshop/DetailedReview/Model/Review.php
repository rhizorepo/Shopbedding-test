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

/**
 * Class MageWorkshop_DetailedReview_Model_Review
 *
 * @method int getAge()
 * @method Mage_Review_Model_Review setAge(int $age)
 * @method int getBodyType()
 * @method Mage_Review_Model_Review setBodyType(int $bodyType)
 * @method array getCons()
 * @method Mage_Review_Model_Review setCons($pros)
 * @method int getCustomerId()
 * @method Mage_Review_Model_Review setCustomerId(int $customerId)
 * @method string getDetail()
 * @method Mage_Review_Model_Review setDetail(string $detail)
 * @method bool getEmailSent()
 * @method Mage_Review_Model_Review setEmailSent(bool $emailSent)
 * @method string getGoodDetail()
 * @method Mage_Review_Model_Review setGoodDetail(string $goodDetail)
 * @method float getHeight()
 * @method Mage_Review_Model_Review setHeight(float $height)
 * @method string getImage()
 * @method Mage_Review_Model_Review setImage(string $image)
 * @method string getNickname()
 * @method Mage_Review_Model_Review setNickname(string $nickname)
 * @method string getLocation()
 * @method Mage_Review_Model_Review setLocation(string $location)
 * @method string getNoGoodDetail()
 * @method Mage_Review_Model_Review setNoGoodDetail(string $goodDetail)
 * @method array getPros()
 * @method Mage_Review_Model_Review setPros($pros)
 * @method string getRecommendTo()
 * @method Mage_Review_Model_Review setRecommendTo(string $recommendTo)
 * @method int getReviewId()
 * @method Mage_Review_Model_Review setReviewId(int $reviewId)
 * @method string getResponse()
 * @method Mage_Review_Model_Review setResponse(string $response)
 * @method string getSizing()
 * @method Mage_Review_Model_Review setSizing(string $sizing)
 * @method int getStoreId()
 * @method Mage_Review_Model_Review setStoreId(int $storeId)
 * @method array getStores()
 * @method Mage_Review_Model_Review setStores(array $storeIds)
 * @method string getTitle()
 * @method Mage_Review_Model_Review setTitle(string $title)
 * @method string getCustomerEmail()
 * @method Mage_Review_Model_Review setCustomerEmail(string $customerEmail)
 * @method string getRemoteAddr()
 * @method Mage_Review_Model_Review setRemoteAddr(string $ip)
 * @method string getVideo()
 * @method Mage_Review_Model_Review setVideo(string $video)
 * @method MageWorkshop_DetailedReview_Model_Review setStatusId(int $value)
 */
class MageWorkshop_DetailedReview_Model_Review extends Mage_Review_Model_Review
{
    const XML_PATH_EMAIL_TEMPLATE               = 'detailedreview/email_notify/template';
    const XML_PATH_EMAIL_RECEIVER               = 'detailedreview/email_notify/receiver';
    const XML_PATH_EMAIL_SENDER                 = 'detailedreview/email_notify/sender';
    const XML_PATH_EMAIL_COPY_TO                = 'detailedreview/email_notify/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'detailedreview/email_notify/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'detailedreview/email_notify/enabled';

    const XML_PATH_EMAIL_TEMPLATE_FOR_CUSTOMER          = 'detailedreview/email_notify_to_customer/template';
    const XML_PATH_EMAIL_SENDER_FOR_CUSTOMER            = 'detailedreview/email_notify_to_customer/sender';
    const XML_PATH_EMAIL_BLIND_COPY_TO_FOR_CUSTOMER     = 'detailedreview/email_notify_to_customer/blind_copy_to';
    const XML_PATH_EMAIL_ENABLED_FOR_CUSTOMER           = 'detailedreview/email_notify_to_customer/enabled';

    protected $_reviewsCountWithoutFilters = 0;

    protected $_reviewsCollection = null;

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($storeId = $this->getStoreId()) {
            return Mage::app()->getStore($storeId);
        }
        return Mage::app()->getStore();
    }

    /**
     * @return bool|string
     */
    public function getOwnership()
    {
        if ($dateOrder = $this->getData('bought_in')) {
            $dateOrder = getdate(strtotime($dateOrder));
            $currentDate = getdate(time());
            $helper = Mage::helper('detailedreview');
            if ($ownershipYears = $currentDate['year'] - $dateOrder['year']) {
                return $helper->__('more than ') . $ownershipYears . $helper->__(' year(s)');
            }
            if ($ownershipMonths = $currentDate['mon'] - $dateOrder['mon']) {
                return $helper->__('more than ') . $ownershipMonths . $helper->__(' month(s)');
            }
            $ownershipDays = $currentDate['mday'] - $dateOrder['mday'];
            return ($ownershipDays / 7 < 1)
                ? $helper->__('less than 1 week')
                : $helper->__('more than ') . round($ownershipDays / 7) . $helper->__(' month(s)');
        }
        return false;
    }

    /**
     * @return int
     */
    public function getHelpfulVotes()
    {
        return Mage::getModel('detailedreview/review_helpful')->getQtyHelpfulVotesForReview($this->getId());
    }

    /**
     * @return int
     */
    public function getAllVotes()
    {
        return Mage::getModel('detailedreview/review_helpful')->getQtyVotesForReview($this->getId());
    }

    /**
     * @return bool
     */
    public function checkGuestIsVoted()
    {
        if (!Mage::getSingleton('customer/session')->IsLoggedIn() && Mage::getStoreConfig('detailedreview/settings_customer/allow_guest_vote')) {
            $helpfulCollection = Mage::getModel('detailedreview/review_helpful')->getCollection()
                    ->addFieldToFilter('remote_addr', array('eq' => Mage::helper('core/http')->getRemoteAddr()))
                    ->addFieldToFilter('review_id', array('eq' => $this->getReviewId()));
            if(!$helpfulCollection->getSize()){
                return false;
            }
        }else{
            return false;
        }
        return true;
    }

    /**
     * @return array|bool
     */
    public function validate()
    {
        $validator = Mage::helper('detailedreview/validate');
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::validate();
        }
        $errors = array();
        $helper = Mage::helper('customer');

        foreach ($validator->getFieldsToValidate() as $field) {
            $rules = $validator->getValidationRules($field, $validator::VALIDATION_TYPE_BACKEND);
            $data = $this->getData($field);

            if (empty($data) && !$validator->isRequired($field)) {
                continue;
            }
            if (is_string($data) && !iconv_strlen($data, 'UTF-8')) {
                $errors[] = $validator->__('Encoding "%s" field is wrong.', $validator->getLabel($field));
                continue;
            }

            foreach ($rules as $key => $value) {
                $isValid = is_array($value)
                    ? Zend_Validate::is($data, $key, $value)
                    : Zend_Validate::is($data, $key);

                if (!$isValid) {
                    $errors[] = $helper->__($validator->getFieldError($field));
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @return Mage_Review_Model_Resource_Review_Product_Collection|MageWorkshop_DetailedReview_Model_Mysql4_Review_Product_Collection
     */
    public function getProductCollection()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::getProductCollection();
        }
        return Mage::getResourceModel('detailedreview/review_product_collection');
    }

    /**
     * @return $this
     */
    public function sendNewReviewEmailToAdmin()
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('detailedreview')->canSendNewReviewEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $storeEmailAddresses = Mage::getStoreConfig('trans_email');

        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);
        $recipientName = $storeEmailAddresses['ident_support']['name'];
        $recipientEmail = $storeEmailAddresses['ident_support']['email'];
        $emailInfo->addTo($recipientEmail, $recipientName);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $customerEmail = Mage::getSingleton('customer/session')->isLoggedIn()
            ? Mage::getSingleton('customer/session')->getCustomer()->getEmail()
            : 'n/a';

        $senderKey = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId);
        if ($this->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED) {
            $action = Mage::helper('drcore')->__('check review content');
        } else {
            $action = Mage::helper('drcore')->__('approve review');
        }
        $mailer->setSender($senderKey);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'review'      => $this,
                'product'     => Mage::getModel('catalog/product')->load($this->getEntityPkValue()),
                'review_link' => Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product_review/edit/', array('id' => $this->getId())),
                'action' => $action,
                'customer_email' => $customerEmail
            )
        );
        Mage::dispatchEvent('detailedreview_review_newreviewemail', array(
            'mailer' => $mailer
        ));
        $mailer->send();
        $this->setEmailSent(true);
        return $this;
    }

    /**
     * @return $this
     */
    public function sendNewReviewEmailToCustomer()
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('detailedreview')->canSendNewReviewEmailToCustomer($storeId)) {
            return $this;
        }

        $customerEmail = Mage::getSingleton('customer/session')->isLoggedIn()
            ? Mage::getSingleton('customer/session')->getCustomer()->getEmail()
            : $this->getCustomerEmail();

        if (!$customerEmail) {
            return $this;
        }

        $emailInfo = Mage::getModel('core/email_info');
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_BLIND_COPY_TO_FOR_CUSTOMER);
        $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_FOR_CUSTOMER, $storeId);
        $mailer = Mage::getModel('core/email_template_mailer');

        if ($copyTo) {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }

        $emailInfo->addTo($customerEmail, $this->getNickname());
        $mailer->addEmailInfo($emailInfo);

        $senderKey = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER_FOR_CUSTOMER, $storeId);
        $mailer->setSender($senderKey);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'is_approved' => $this->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED
            )
        );
        $mailer->send();
        $this->setEmailSent(true);
        return $this;
    }

    /**
     * @param MageWorkshop_DetailedReview_Model_Review_MailersData $mailersData
     * @return $this
     */
    public function sendEmail($mailersData)
    {
        $storeId = $this->getStore()->getId();
        $emailInfo = Mage::getModel('core/email_info');
        $mailer = Mage::getModel('core/email_template_mailer');

        $copyTo = array();
        if ($mailersData->getCopyToPath()) {
            $copyTo = $this->_getEmails($mailersData->getCopyToPath());
        }

        if ($copyTo && $mailersData->getCopyMethod() == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $mailersData->getCopyMethod() == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $emailInfo->addTo($mailersData->getRecipientEmail(), $mailersData->getRecipientName());
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender($mailersData->getSender());
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($mailersData->getTemplateId());
        $mailer->setTemplateParams($mailersData->getTemplateParams());
        $mailer->send();
        $this->setEmailSent(true);
        return $this;
    }

    /**
     * @param bool $processForce
     * @param int $range
     * @return Mage_Review_Model_Resource_Review_Collection|MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     */
    public function getReviewsCollection($processForce = false, $range = null)
    {
        if (is_null($this->_reviewsCollection) || $processForce) {
            $params = Mage::app()->getRequest()->getParams();
            $product = Mage::registry('product');
            /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $reviewsCollection */
            $reviewsCollection = Mage::getModel('review/review')->getCollection();
            $reviewsCollection
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', $product->getId());

            if (!$processForce) {
                $this->_reviewsCountWithoutFilters = $reviewsCollection->getSize();
                $reviewsCollection->resetTotalRecords();
            }

            $range = (isset($range) && $range) ? $range : ((isset($params['range'])) ? $params['range'] : 0);
            if ($range && $range != 999 && $range != 1) {
                $reviewsCollection->addDateRangeFilter($range);
            }
            if ($range == 1) {
                $reviewsCollection->addUserReviewFilter();
            }

            if (isset($params['keywords']) && $params['keywords']) {
                $reviewsCollection->addKeywordsFilter($params['keywords']);
            }
            if (isset($params['verified_buyers']) && (bool)$params['verified_buyers']) {
                $reviewsCollection->addVerifiedBuyersFilter();
            }
            if (isset($params['video']) && (bool)$params['video']) {
                $reviewsCollection->addVideoFilter();
            }
            if (isset($params['images']) && (bool)$params['images']) {
                $reviewsCollection->addImagesFilter();
            }
            if (isset($params['admin_response']) && (bool)$params['admin_response']) {
                $reviewsCollection->addManyResponseFilter();
            }
            if (isset($params['highest_contributors']) && (bool)$params['highest_contributors']) {
                $reviewsCollection->addHighestContributorFilter();
            }
            $reviewsCollection->setCustomOrder(Mage::getSingleton('detailedreview/review_sorting')->getCurrentSorting());
            $reviewsCollection->addOwnershipInfo();

            if ($processForce) {
                return $reviewsCollection;
            }
            $this->_reviewsCollection = $reviewsCollection;
        }
        Mage::dispatchEvent('detailedreview_reviews_collection', array(
            'collection' => $this->_reviewsCollection
        ));
        return $this->_reviewsCollection;
    }

    /**
     * @return int
     */
    public function getReviewsCountWithoutFilters()
    {
        return isset($this->_reviewsCountWithoutFilters) ? $this->_reviewsCountWithoutFilters : 0;
    }

    /**
     * @param $configPath
     * @return array|bool
     */
    protected function _getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
}
