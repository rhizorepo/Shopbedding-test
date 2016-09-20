<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRGeoIp
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DRGeoIp_Model_Observer
{
    /**
     * controller_action_predispatch_adminhtml_catalog_product_review_post - global event
     */
    public function  sendNewReviewEmailToAdmin($observer)
    {
        /**
         * @var MageWorkshop_DetailedReview_Model_Review $review
         */
        $review = $observer->getReview();
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig('drgeoip/settings/enable')) {
            return $review;
        }
        if (!Mage::helper('detailedreview')->canSendNewReviewEmail($storeId)) {
            return $review;
        }

        $storeEmailAddresses = Mage::getStoreConfig('trans_email');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        } elseif ($email = $review->getCustomerEmail()) {
            $customerEmail = $email;
        } else {
            $customerEmail = 'n/a';
        }
        $receiver = 'ident_' . Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_RECEIVER, $storeId);
        $recipientName = $storeEmailAddresses[$receiver]['name'];
        $recipientEmail = $storeEmailAddresses[$receiver]['email'];

        $remoteAddr = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR')
            ? Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR')
            : Mage::helper('core/http')->getRemoteAddr();

        $geoIp = new MageWorkshop_DRGeoIp_Model_GeoIp($remoteAddr);
        if ($review->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED) {
            $action = Mage::helper('drcore')->__('check review content');
        } else {
            $action = Mage::helper('drcore')->__('approve review');
        }
        $data = array(
            'sender' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_SENDER, $storeId),
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'copy_to_path' => MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_COPY_TO ,
            'copy_method' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_COPY_METHOD, $storeId),
            'template_id' => Mage::getStoreConfig('drgeoip/admin_email_notify/template', $storeId),
            'template_params' => array(
                'review'      => $review,
                'product'     => Mage::getModel('catalog/product')->load($review->getEntityPkValue()),
                'review_link' => Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product_review/edit/', array('id' => $review->getId())),
                'action' => $action,
                'geo_ip'            => $geoIp,
                'customer_email' => $customerEmail,
                'recipient_name' => $recipientName
            )

        );

        $mailersData = new MageWorkshop_DetailedReview_Model_Review_MailersData($data);
        $review->sendEmail($mailersData);
    }

    public function checkIfModuleEnabled($observer)
    {
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        if ($moduleContainer->getModule() == 'MageWorkshop_DRGeoIp') {
            $storeId = Mage::app()->getStore()->getId();
            $moduleContainer->setEnabled(Mage::getStoreConfig('drgeoip/settings/enable', $storeId));
        }
    }

    public function enableModule($observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_DRGeoIp') {
            $storeId = Mage::app()->getStore()->getId();
            if(Mage::getStoreConfig('drgeoip/settings/enable', $storeId)) {
                Mage::getModel('core/config')->saveConfig('drgeoip/settings/enable', 0);
                $moduleConfig->setEnabled('disabled');
            } else {
                Mage::getModel('core/config')->saveConfig('drgeoip/settings/enable', 1);
                $moduleConfig->setEnabled('enabled');
            }

        }
    }

    public function uninstallModule($observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_DRGeoIp') {
            $uninstaller = Mage::getModel('drcore/uninstall');
            if ($uninstaller->checkPackageFile('DRGeoIp')) {
                $moduleConfig->setPackageName('DRGeoIp');
            } else {
                $moduleConfig->setException(Mage::helper('drcore')->__('Cannot find package file for Detailed Review GeoIp plugin.'));
            }
        }
    }
}

