<?php

class Ebizmarts_Mailchimp_Block_Adminhtml_Newsletter_Subscriber extends Mage_Adminhtml_Block_Newsletter_Subscriber
{

	public function __construct()
	{
		$this->setTemplate('newsletter/subscriber/list_mailchimp.phtml');
	}

	public function getMailChimpSyn()
	{
		return $this->getUrl('mailchimp/index/index');
	}

}

?>
