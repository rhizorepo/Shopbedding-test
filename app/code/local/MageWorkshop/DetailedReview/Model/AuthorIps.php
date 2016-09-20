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
 * Class MageWorkshop_DetailedReview_Model_AuthorIps
 *
 * @method string getRemoteAddr()
 * @method MageWorkshop_DetailedReview_Model_AuthorIps setRemoteAddr(string $remoteAddr)
 * @method int getCustomerId()
 * @method MageWorkshop_DetailedReview_Model_AuthorIps setCustomerId(int $customerId)
 * @method string getExpirationTime()
 * @method MageWorkshop_DetailedReview_Model_AuthorIps setExpirationTime(int $expirationTime)
 */
class MageWorkshop_DetailedReview_Model_AuthorIps extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $this->_init('detailedreview/authorIps');
    }

    public function clearOld() {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_AuthorIps_Collection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(
            'expiration_time',
            array(
                'to' => Mage::getSingleton('core/date')->gmtDate(),
                'datetime' => true
            )
        );
        /** @var MageWorkshop_DetailedReview_Model_AuthorIps $item */
        foreach ($collection as $item) {
            if ($customerId = $item->getCustomerId()) {
                /** @var Mage_Customer_Model_Customer $customer */
                $customer = Mage::getModel('customer/customer')->load($customerId);
                if ($customer->getId()) {
                    Mage::dispatchEvent('detailedreview_authorips_clearold', array(
                        'customer' => $customer
                    ));
                    $customer->setIsBannedWriteReview(0)->save();
                }
            }
            $item->delete();
        }
    }
}
