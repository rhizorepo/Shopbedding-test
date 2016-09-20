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

class MageWorkshop_DRReminder_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param $order Mage_Sales_Model_Order
     * @return bool
     */
    public function createReviewReminder($order)
    {
        $allowedStatuses = explode(',', Mage::getStoreConfig('drreminder/settings/remind_choice_status'));
        if (in_array($order->getStatus(), $allowedStatuses)) {
            $remindersCollection = Mage::getModel('drreminder/reminder')->getCollection()
                ->addFieldToFilter('order_id', array('eq' => $order->getId()));
            if (!$remindersCollection->getSize()) {
                $productCollection = Mage::helper('drreminder')->getProductsByOrders($order->getId());
                if ($productCollection->count()) {
                    $delay = trim(Mage::getStoreConfig('drreminder/settings/remind_delay_period'));
                    $re = "/^(?:(\\d+)d\\s*)?(?:(\\d+)h\\s*)?(?:(\\d+)m\\s?)?(?:(\\d+)s?)?$/";

                    preg_match($re, strtolower($delay), $matches);
                    $delay = $matches[1]*86400+$matches[2]*3600+$matches[3]*60+$matches[4];
                    /** @var MageWorkshop_DRReminder_Model_Reminder $reminder */
                    $reminder = Mage::getModel('drreminder/reminder');
                    $reminder
                        ->setCustomerId($order->getCustomerId())
                        ->setCustomerName($order->getCustomerFirstname().' '.$order->getCustomerLastname())
                        ->setEmail($order->getCustomerEmail())
                        ->setOrderId($order->getId())
                        ->setIncrementId($order->getIncrementId())
                        ->setCreatingDate(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
                        ->setExpirationDate(date("Y-m-d H:i:s", (Mage::getModel('core/date')->timestamp(time())) + $delay))
                        ->setStoreId($order->getStoreId());
                    Mage::dispatchEvent('drreminder_reminder_create', array(
                        'reminder' => $reminder,
                        'order'    => $order
                    ));
                    $reminder->save();
                    if (!$delay && Mage::getStoreConfig('drreminder/settings/remind_send_email')) {
                        /** @var MageWorkshop_DRReminder_Block_Adminhtml_ItemsForReminder|MageWorkshop_DRReminder_Block_ItemsForReminder $productsForReminderBlock */
                        $productsForReminderBlock = Mage::getModel('drreminder/reminder')->prepareReminderForSending(
                            $reminder, 'drreminder/adminhtml_itemsForReminder'
                        );
                        if ($productsForReminderBlock) {
                            Mage::getModel('drreminder/reminder')->sendReminderEmail($reminder, $productsForReminderBlock);
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $customerId
     * @param $orderId
     * @return Mage_Sales_Model_Resource_Order_Item_Collection
     */
    public function getReminderItems($customerId, $orderId)
    {
        $customerOrders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id',array('eq' => $customerId));
        $orderIds = array();
        foreach ($customerOrders as $customerOrder) {
            $orderIds[] = $customerOrder->getId();
            if ($customerOrder->getId() == $orderId){
                $productIds = array();
                $currentItems = $customerOrder->getAllItems();
                foreach ($currentItems as $current) {
                    $productIds[] = $current->getProductId();
                }
            }
        }
        $customerItems = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id',array('in' => $orderIds))
            ->addFieldToFilter('product_id',array('in' => $productIds));
        return $customerItems;
    }

    /**
     * @param $items
     * @return array
     */
    public function getItemsWithSentReminders($items)
    {
        $sent = array();
        foreach ($items as $item) {
            if ($item->getReminder()) {
                $sent[] =  $item->getProductId();
            }
        }
        $unique = array_unique($sent);
        return $unique;
    }

    public function getCustomerIdentifier()
    {
        // http://example.com/drreminder/reminder/products/order/fdsfgkjest89j9034airk03a/ order
        $identifier = (string) Mage::app()->getRequest()->getParam('order');
        if (isset($this->_customerIdentifier) && $this->_customerIdentifier && $this->_customerIdentifier->getHash() == $identifier) {
            return $this->_customerIdentifier;
        }

        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->getSelect()
            ->having('MD5(CONCAT(entity_id, created_at)) = ?', $identifier);

        /** @var Mage_Sales_Model_Order $order */
        $order = $orderCollection->getFirstItem();

        if ($order->getId()) {
            /** @var MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier */
            $customerIdentifier = Mage::getModel('drreminder/customerIdentifier');
            if ($order->getCustomerId()) {
                $data = array(
                    'type'  => $customerIdentifier::IDENTIFIER_TYPE_ID,
                    'value' => $order->getCustomerId()
                );
            } else {
                $data = array(
                    'type'  => $customerIdentifier::IDENTIFIER_TYPE_EMAIL,
                    'value' => $order->getCustomerEmail()
                );
            }
            $data['order_id'] = $order->getId();
            $data['hash'] = $identifier;
            $customerIdentifier->setData($data);

            $this->_customerIdentifier = $customerIdentifier;
            Mage::getSingleton('core/cookie')->set('customerIdentifier', json_encode($customerIdentifier->getData()), 60*60*24*10);
            Mage::getSingleton('core/cookie')->set('store', $order->getStore()->getCode(), 60*60*24*10);

            if ((int)Mage::getStoreConfig('drreminder/settings/remind_enable') && (int)Mage::getStoreConfig('drreminder/settings/remind_redirect_to_product')) {
                $items = $order->getAllVisibleItems();
                if (count($items) < 2) {
                    $productUrl = $items[0]->getProduct()->getProductUrl();
                    Mage::app()->getResponse()->setRedirect($productUrl . '#review-form');
                }
            }
            return $customerIdentifier;
        } else {
            return false;
        }
    }

    /**
     * @param $orderIds
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsByOrders($orderIds, $withoutRemindersOnly = true)
    {
        $productIds = array();

        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('entity_id', array('in' => $orderIds))
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()));
        $allowedStatuses = explode(',', Mage::getStoreConfig('drreminder/settings/remind_choice_status'));
        $orderCollection->addFieldToFilter('status', array('in' => $allowedStatuses));

        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection as $order) {
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                if ($withoutRemindersOnly && $item->getReminder()) {
                    continue;
                }
                $productIds[] = $item->getProductId();
            }
        }

        array_unique($productIds);
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()))
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
        $productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
        return $productCollection;
    }

}
