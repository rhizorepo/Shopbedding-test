<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

/**
 * Class MageWorkshop_DRReminder_Model_Reminder

 * @method int getCustomerId()
 * @method MageWorkshop_DRReminder_Model_Reminder setCustomerId(string $customerId)
 * @method string getCustomerName()
 * @method MageWorkshop_DRReminder_Model_Reminder setCustomerName(string $customerName)
 * @method string getEmail()
 * @method MageWorkshop_DRReminder_Model_Reminder setEmail(string $customerName)
 * @method int getOrderId()
 * @method MageWorkshop_DRReminder_Model_Reminder setOrderId(string $orderId)
 * @method int getIncrementId()
 * @method MageWorkshop_DRReminder_Model_Reminder setIncrementId(string $incrementId)
 * @method getCreatingDate()
 * @method MageWorkshop_DRReminder_Model_Reminder setCreatingDate($creatingDate)
 * @method getExpirationDate()
 * @method MageWorkshop_DRReminder_Model_Reminder setExpirationDate($expirationDate)
 * @method getSentAt()
 * @method MageWorkshop_DRReminder_Model_Reminder setSentAt($sentAt)
 * @method string getStatus()
 * @method MageWorkshop_DRReminder_Model_Reminder setStatus(string $status)
 *
 */
class MageWorkshop_DRReminder_Model_Reminder extends Mage_Core_Model_Abstract
{
    protected $_reminder = null;

    public  function _construct()
    {
        $this->_init('drreminder/reminder');
    }

    public function initRemindersSending()
    {
        if (
            Mage::getStoreConfig('drreminder/settings/remind_enable')
            && Mage::getStoreConfig('drreminder/settings/remind_send_email')
        ) {
            $reminders = Mage::getModel('drreminder/reminder')->getCollection()
                ->addFieldToFilter('status', array('eq' => MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_PENDING));

            foreach ($reminders as $reminder) {
                $beforetime = strtotime($reminder->getExpirationDate()) - Mage::getModel('core/date')->timestamp(time());

                // @TODO check how this works with reminders that were not sent in the last 24 hours (for example, cron failed)
                if (($beforetime != 0) && ($beforetime / 3600 <= 24)) {
                    /** @var MageWorkshop_DRReminder_Block_Adminhtml_ItemsForReminder|MageWorkshop_DRReminder_Block_ItemsForReminder $productsForReminderBlock */
                    $productsForReminderBlock = Mage::getModel('drreminder/reminder')->prepareReminderForSending(
                        $reminder, 'drreminder/adminhtml_itemsForReminder'
                    );
                    if ($productsForReminderBlock) {
                        $this->sendReminderEmail($reminder, $productsForReminderBlock);
                    }
                }
            }
        }
    }

    public function prepareReminderForSending($reminder, $block)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::helper('drreminder')->getProductsByOrders($reminder->getOrderId());
        if ($productCollection->count()) {
            $block = Mage::app()->getLayout()->createBlock($block);
            $block->setReminder($reminder)
                ->setProducts($productCollection);
            return $block;
        }
        return false;
    }

    public function sendReminderEmail($reminder, Mage_Core_Block_Abstract $productsForReminderBlock)
    {
        $emailTemplate  = Mage::getModel('core/email_template');
        $mailer = Mage::getModel('core/email_template_mailer');
        $storeId = $reminder->getStoreId();
        $senderKey = Mage::getStoreConfig('drreminder/settings/remind_email_sender', $storeId);
        $templateId = Mage::getStoreConfig('drreminder/settings/remind_email_template', $storeId);
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($reminder->getEmail(), $reminder->getCustomerName());
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender($senderKey);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'customer_name'      => $reminder->getCustomerName(),
                'products_for_review'     => $productsForReminderBlock->toHtml(),
                'store_name' => Mage::app()->getStore($storeId)->getFrontendName()
            )
        );
        Mage::dispatchEvent('drreminder_review_reminder', array(
            'mailer' => $mailer
        ));
        $mailer->send();
//
        $this->markReminderAsSent($reminder);

    }

    public function markReminderAsSent($reminder)
    {
        $reminderModel = Mage::getModel('drreminder/reminder')->load($reminder->getId());
        $reminderModel
            ->setSentAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
            ->setStatus(MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_SENT)
            ->save();
        $reminderItems = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $reminder->getOrderId()));
        foreach ($reminderItems as $item) {
            $item->setReminder(1);
            $item->save();
        }
    }

}
