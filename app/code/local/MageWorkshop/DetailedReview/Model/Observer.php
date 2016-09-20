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
class MageWorkshop_DetailedReview_Model_Observer
{

    /**
     * controller_action_predispatch_adminhtml_catalog_product_review_save - global event
     * controller_action_predispatch_adminhtml_catalog_product_review_post - global event
     */
    public function adminReviewSave()
    {
        $files = Mage::helper('detailedreview')->uploadImages();
        $session = Mage::getSingleton('core/session');
        if(!empty($files['images'])){
            Mage::app()->getRequest()->setPost('image', implode(",", $files['images']));
        } else {
            Mage::app()->getRequest()->setPost('image', null);
        }
        if (!empty($files['errors'])) {
            foreach ($files['errors'] as $imageName => $errorMessages) {
                foreach($errorMessages as $message) {
                    $session->addError(Mage::helper('detailedreview')->__('Image \'%s\' has the following problem: ', $imageName) . $message);
                }
            }
        }
    }

    /**
     * controller_action_postdispatch_adminhtml_system_config_save - global event
     */
    public function configSave()
    {
        $groups = Mage::app()->getRequest()->getParam('groups');
        $enableFlag = Mage::getStoreConfig('detailedreview/settings/enable_flag');
        if (isset($groups['settings']['fields']['enable']['value'])) {
            $enable = $groups['settings']['fields']['enable']['value'];
            if ($enable !== $enableFlag) {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable_flag', $enable);
            }
        }
        if (isset($groups['modules_disable_output']['fields']['Mage_Review']['value'])) {
            $mageReview = $groups['modules_disable_output']['fields']['Mage_Review']['value'];
            if ($mageReview == 1) {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable', 0);
            } else {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable', Mage::getStoreConfig('detailedreview/settings/enable_flag'));
            }
        }
    }

    public function categorySave(Varien_Event_Observer $observer)
    {
        $usePostDataConfig = $observer->getRequest()->getPost('use_config');
        if (in_array('default_sort_by', $usePostDataConfig)) {
            $store =  Mage::app()->getStore();
            $defaultSortConfig = Mage::getStoreConfig(Mage_Catalog_Model_Config::XML_PATH_LIST_DEFAULT_SORT_BY, $store);
            $availableSort = $observer->getCategory()->getAvailableSortBy();

            if (!in_array($defaultSortConfig, $availableSort)) {
                array_push($availableSort, $defaultSortConfig);
                $observer->getCategory()->setAvailableSortBy($availableSort);
            }
        }
    }

    /**
     * Handler for controller_action_predispatch event
     */
    public function checkLicense() {
        $storeLink = Mage::getStoreConfig('detailedreview/store_link');
        $supportLink = Mage::getStoreConfig('detailedreview/support_link');
        if (!Mage::helper('detailedreview')->checkLicenseKey() && Mage::getStoreConfig('detailedreview/settings/enable')) {
            $errorMessage = "
                It looks like Detailed Review extension is not licensed.
                You could buy extension via <a href=\"%s\">Magento Connect</a>.
                If you believe you are getting this message by mistake please <a href=\"%s\">contact support</a>";
            Mage::getSingleton('core/session')->addError(sprintf($errorMessage, $storeLink, $supportLink));
            $messages = Mage::getSingleton('core/session')->getMessages()->getItems();
            Mage::getSingleton('core/session')->getMessages(true);
            Mage::getSingleton('core/session')->addUniqueMessages($messages);
        }
    }

    public function checkObserverKey()
    {
        $store = Mage::app()->getStore();
        if ($store->isAdmin()) {
            $secure = $store->isAdminUrlSecure();
        } else {
            $secure = $store->isFrontUrlSecure() && Mage::app()->getRequest()->isSecure();
        }
        if (Mage::app()->getRequest()->getParam('store') && Mage::getModel('core/store')->load(Mage::app()->getRequest()->getParam('store'), 'code')) {
            $store = Mage::getModel('core/store')->load(Mage::app()->getRequest()->getParam('store'), 'code');
        }
        $serverHost = Mage::getSingleton('core/url')->parseUrl($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure));
        $serverHost = str_replace('www.', '', $serverHost['host']);
        return md5('checkobserver' . $serverHost . date('z'));
    }
    public function catalogProductViewPredispatch()
    {
        if ($fragment = Mage::app()->getRequest()->getParam('_escaped_fragment_')) {
            if ($productId  = Mage::app()->getRequest()->getParam('id')) {
                $product = Mage::getModel('catalog/product')->load($productId);
                $productUrl = $product->getProductUrl();
                header("location:$productUrl?$fragment", 1, 301);
                exit;
            }
        }
    }

    public function checkIfModuleEnabled(Varien_Event_Observer $observer)
    {
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        if ($moduleContainer->getModule() == 'MageWorkshop_DetailedReview') {
            $storeId = Mage::app()->getStore()->getId();
            $moduleContainer->setEnabled(Mage::getStoreConfig('detailedreview/settings/enable', $storeId));
        }
    }

    public function enableModule(Varien_Event_Observer $observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_DetailedReview') {
            $storeId = Mage::app()->getStore()->getId();
            if(Mage::getStoreConfig('detailedreview/settings/enable', $storeId)) {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable', 0);
                $moduleConfig->setEnabled('disabled');
            } else {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable', 1);
                $moduleConfig->setEnabled('enabled');
            }

        }
    }

    public function uninstallModule(Varien_Event_Observer $observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_DetailedReview') {
            $uninstaller = Mage::getModel('drcore/uninstall');
            if ($uninstaller->checkPackageFile('DetailedReview')) {
                try {
                    Mage::getModel('detailedreview/uninstall')->clearDatabaseInformation();
                    $moduleConfig->setModuleName('MageWorkshop_DRGeoIp');
                    Mage::dispatchEvent('mageworkshop_module_uninstall', array('module_config' => $moduleConfig));
                    if ($dependent = $moduleConfig->getPackageName()) {
                        $moduleConfig->setDependentPackage($dependent);
                        $moduleConfig->setParentPackage('DetailedReview');
                    }
                    $moduleConfig->setPackageName('DetailedReview');

                } catch (Mage_Core_Exception $e) {
                    $moduleConfig->setException($e->getMessage());
                } catch (Exception $e) {
                    $moduleConfig->setException(Mage::helper('detailedreview')->__('There was a problem with uninstalling.'));
                }
            } else {
                $moduleConfig->setException(Mage::helper('detailedreview')->__('Cannot find package file for DetailedReview plugin.'));
            }
        }
    }

    public function sendNewReviewEmailToAdmin(Varien_Event_Observer $observer)
    {
        /**
         * @var MageWorkshop_DetailedReview_Model_Review $review
         */
        $review = $observer->getReview();
        $storeId = Mage::app()->getStore()->getId();
        if (Mage::getStoreConfig('drgeoip/settings/enable')) {
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
            'template_id' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_TEMPLATE, $storeId),
            'template_params' => array(
                'review'      => $review,
                'product'     => Mage::getModel('catalog/product')->load($review->getEntityPkValue()),
                'review_link' => Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product_review/edit/', array('id' => $review->getId())),
                'action' => $action,
                'customer_email' => $customerEmail,
                'recipient_name' => $recipientName
            )

        );

        /** @var MageWorkshop_DetailedReview_Model_Review_MailersData $mailersData */
        $mailersData = Mage::getModel('detailedreview/review_mailersData')->setData($data);

        $review->sendEmail($mailersData);
    }

    public function sendNewReviewEmailToCustomer(Varien_Event_Observer $observer)
    {
        /**
         * @var MageWorkshop_DetailedReview_Model_Review $review
         */
        $review = $observer->getReview();
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::helper('detailedreview')->canSendNewReviewEmailToCustomer($storeId)) {
            return $review;
        }

        $customerEmail = Mage::getSingleton('customer/session')->isLoggedIn()
            ? Mage::getSingleton('customer/session')->getCustomer()->getEmail()
            : $review->getCustomerEmail();

        if (!$customerEmail) {
            return $review;
        }

        $data = array(
            'sender' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_SENDER_FOR_CUSTOMER, $storeId),
            'recipient_name' => $review->getNickname(),
            'recipient_email' => $customerEmail,
            'copy_to_path' => MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_BLIND_COPY_TO_FOR_CUSTOMER ,
            'copy_method' => 'bcc',
            'template_id' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_TEMPLATE_FOR_CUSTOMER, $storeId),
            'template_params' => array(
                'is_approved' => $review->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED,
                'customer_name' => $review->getNickname()
            )

        );

        /** @var MageWorkshop_DetailedReview_Model_Review_MailersData $mailersData */
        $mailersData = Mage::getModel('detailedreview/review_mailersData')->setData($data);
        $review->sendEmail($mailersData);
    }

    public function addPurchase(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        if ($order->getId() && $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
            Mage::getModel('detailedreview/purchase')->updateData($order->getId());
        }
    }
}
