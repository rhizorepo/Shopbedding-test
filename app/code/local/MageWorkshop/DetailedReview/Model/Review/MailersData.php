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
 * Class MageWorkshop_DetailedReview_Model_Review_SendEmail
 *
 * @method string getSender()
 * @method string setSender($sender)
 * @method string getRecipientEmail()
 * @method string setRecipientEmail($recipientEmail)
 * @method string getRecipientName()
 * @method string setRecipientName($recipientName)
 * @method string getCopyToPath()
 * @method string setCopyToPath($copyToPath)
 * @method string getCopyMethod()
 * @method string setCopyMethod($copyMethod)
 * @method string getTemplateId()
 * @method string setTemplateId($template)
 * @method array getTemplateParams()
 * @method array setTemplateParams($data)
 */
class MageWorkshop_DetailedReview_Model_Review_MailersData extends Varien_Object
{

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->setData($data);
        // TODO
        // $this->_validate();
    }

    protected function _validate()
    {}
}
