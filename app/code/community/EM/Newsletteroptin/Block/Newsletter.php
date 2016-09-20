<?php
class EM_Newsletteroptin_Block_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('newsletteroptin/newsletter.phtml');
    }

    public function isChecked()
    {
        return (bool) $this->getCheckout()->getCustomerIsSubscribed();
    }
}