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
class MageWorkshop_DetailedReview_Model_Mysql4_Review_Helpful_Collection extends Varien_Data_Collection_Db
{
    /**
     * @inherit
     */
    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');
        parent::__construct($resources->getConnection('detailedreview_read'));
        $this->_select->from(array('main_table' => $resources->getTableName('detailedreview/review_helpful')));
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFilter(
            'customer',
            $this->getConnection()->quoteInto('main_table.customer_id=?', (int) $customerId),
            'string'
        );
        return $this;
    }

    /**
     * @param string $remoteAddress
     * @return $this
     */
    public function addRemoteAddressFilter($remoteAddress)
    {
        $this->addFilter('main_table.remote_addr', $remoteAddress);
        return $this;
    }

    /**
     * @param int $reviewId
     * @return $this
     */
    public function addReviewFilter($reviewId)
    {
        $this->addFilter(
            'customer',
            $this->getConnection()->quoteInto('main_table.review_id=?', (int) $reviewId),
            'string'
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function addHelpfulFilter()
    {
        $this->addFilter(
            'customer',
            $this->getConnection()->quoteInto('main_table.is_helpful=?', 1),
            'string'
        );
        return $this;
    }
}
