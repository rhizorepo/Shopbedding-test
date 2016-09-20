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

class MageWorkshop_DetailedReview_Model_Review_JsConfig extends Mage_Core_Model_Abstract
{

    public function __construct()
    {
        $this->_init('detailedreview/review_jsConfig');
    }

    public function getJsonConfig()
    {
        if (!($imagesMaxCount = (int)Mage::getStoreConfig('detailedreview/image_options/images_max_count'))) {
            $imagesMaxCount = 1;
        }
        $versionDR = (array)Mage::getConfig()->getNode()->modules->MageWorkshop_DetailedReview->version;
        $helper = Mage::helper('detailedreview');
        $resizeAverage = 32;
        $resizeSeparate = 20;
        $activeRatingImage = $this->getActiveRatingImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'detailedreview/');
        $unactiveRatingImage = $this->getUnactiveRatingImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'detailedreview/');
        $configJson = array (
            "magnificConfig" => array(
                "type" =>'image',
                "gallery" => array(
                    "enabled" => 'true',
                    "navigateByImgClick" => 'true',
                    "preload" => '[0]')
            ),
            "isShowPopup" => (int)Mage::getStoreConfig('detailedreview/settings/review_form_popup'),
            "isCustomerLoggedIn" => Mage::helper('customer')->isLoggedIn(),
            "isGuestAllowToVote" => (int)Mage::getStoreConfig('detailedreview/settings_customer/allow_guest_vote'),
            "isGuestAllowToWrite" => Mage::helper('review')->getIsGuestAllowToWrite(),
            "onlyVerifiedBuyer" => (int)Mage::getStoreConfig('detailedreview/settings_customer/only_verified_buyer'),
            "productIdsAllowReviewUrl" => Mage::getUrl('detailedreview/product/checkbuyerproducts'),
            'productId' => Mage::registry('current_product')->getId(),
            "imageMaxCount" => $imagesMaxCount,
            "isAjaxSubmit" => (int)Mage::getStoreConfig('detailedreview/settings/submit_review_ajax'),
            "autoApproveFlag" => Mage::helper('detailedreview')->getAutoApproveFlag(),
            "checkLoginUrl" => Mage::getUrl('detailedreview/index/checklogin'),
            "isStatusApproved" => Mage_Review_Model_Review::STATUS_APPROVED,
            "isCaptchaEnabled" => (int)Mage::getStoreConfig('detailedreview/captcha/enabled'),
            "checkCaptchaUrl" => Mage::getUrl('detailedreview/product/checkCaptcha'),
            "captchaApiKey" => Mage::getStoreConfig('detailedreview/captcha/public_key'),
            "checkRegistrationUrl" => Mage::getUrl('detailedreview/index/checkregistrate'),
            "reviewPlaceholder" => '.reviews-placeholder',
            "reviewPlaceholderDR" => '.reviews-placeholder-dr',
            "reviewSubmitButton" => '#review-form .buttons-set button.button:submit',
            "reviewEasyTab" => '#product_tabs_review_tabbed_contents',
            "reviewsBlock" => '.reviews-wrapper',
            "reviewFormButton" => '#review-form button.button',
            "reviewSpinner" => 'review-add-spinner',
            "captchaError" => '.captcha-error',
            "currentImageCount" => 1,
            "dialogClass" => '',
            "dataLoginForm" => '',
            "loginForm" => '#login-form',
            "moreImagesLink" => '#add-more-images',
            "removeImageLink" => '.remove-img',
            "reviewDialog" => '.review-dialog',
            "reviewForm" => '#review-form',
            "reviewVoteRating" => '.review-vote-rating',
            "dateFilter" => '.review-date-filters',
            "dateFilterSpan" => '.top-title',
            "reviewTop" => '.review-top',
            "customerReviews" => '#customer-reviews',
            "backButton" => '#buttonBack',
            "reviewSorts" => '.review-sorts',
            "sortsSpan" => '.top-dropdown-sorts a',
            "sortsLink" => '.sortsLink',
            "dateFilterLink" => '.top-dropdown',
            "openedList" => '.openedList',
            "prosCheckboxes" => '.pros input[type="checkbox"]',
            "consCheckboxes" => '.cons input[type="checkbox"]',
            "isSeparatePage" => (int)Mage::getStoreConfig('detailedreview/settings/review_form_separate'),
            "productPage" => Mage::registry('current_product')->getProductUrl(),
            "separatePage" => Mage::getUrl("detailedreview/index/submitpage/", array('product' => Mage::registry('current_product')->getId())),
            "writeReviewOnce" => (int)Mage::getStoreConfig('detailedreview/settings_customer/write_review_once'),
            "checkWriteReviewOnce" => Mage::getUrl('detailedreview/product/checkwriteonce'),
            "versionDR" => $versionDR[0],
            "activeRatingImage" => $activeRatingImage,
            "unActiveRatingImage" => $unactiveRatingImage,
            "activeImageAverage" => $helper->getResizedImage($activeRatingImage, $resizeAverage),
            "unActiveImageAverage" => $helper->getResizedImage($unactiveRatingImage, $resizeAverage),
            "activeImageSeparate" => $helper->getResizedImage($activeRatingImage, $resizeSeparate),
            "unActiveImageSeparate" => $helper->getResizedImage($unactiveRatingImage, $resizeSeparate),
            "overallRatingItem" => '.overall-raiting ul li',
            "separateRatingStar" => '.separate-rating-star',
            "messages" => array(
                "captchaError" => $helper->__("You have entered wrong captcha."),
                "someError" => $helper->__("Some error has been occurred."),
                "easyTabAlert" => $helper->__("Please, disable \"product's review tab\" in \"EasyTab\" extension options if you want \"Detailed Review\" extension to work correctly with custom reviews block placeholder."),
                "chooseFile" => $helper->__("Choose file"),
                "maxUploadNotify" => $helper->__("You can upload not more than %s images", $imagesMaxCount),
                "onlyVerifiedBuyer" => $helper->__("Only verified buyer can write review"),
                "alreadyReviewed" => $helper->__("Product already reviewed by You"),
            )
        );

        Mage::dispatchEvent('detailedreview_js_config', $configJson);
        return Mage::helper('core')->jsonEncode($configJson);
    }

    public function getActiveRatingImage($url)
    {
        if (!($activeRatingImage = Mage::getStoreConfig('detailedreview/rating_image/active')) || !(getimagesize($url.$activeRatingImage))) {
            $activeRatingImage = 'default/active-star-rwd.png';
        }
        return $url . $activeRatingImage;
    }

    public function getUnactiveRatingImage($url)
    {
        if (!($unactiveRatingImage = Mage::getStoreConfig('detailedreview/rating_image/unactive')) || !(getimagesize($url.$unactiveRatingImage))) {
            $unactiveRatingImage = 'default/unactive-star-rwd.png';
        }
        return $url . $unactiveRatingImage;
    }

}
