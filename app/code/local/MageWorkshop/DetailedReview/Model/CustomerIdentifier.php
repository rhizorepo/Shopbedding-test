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
 * Class MageWorkshop_DetailedReview_Model_CustomerIdentifier
 *
 * @method string getHash()
 * @method MageWorkshop_DetailedReview_Model_CustomerIdentifier setHash(string $hash)
 * @method int getOrderId()
 * @method MageWorkshop_DetailedReview_Model_CustomerIdentifier setOrderId(string $orderId)
 * @method string getType()
 * @method MageWorkshop_DetailedReview_Model_CustomerIdentifier setType(string $type)
 * @method string|int|null getValue()
 * @method MageWorkshop_DetailedReview_Model_CustomerIdentifier setValue($value)
 */
class MageWorkshop_DetailedReview_Model_CustomerIdentifier extends Varien_Object
{
    const IDENTIFIER_TYPE_ID    = 'customer_id';
    const IDENTIFIER_TYPE_EMAIL = 'customer_email';
}
