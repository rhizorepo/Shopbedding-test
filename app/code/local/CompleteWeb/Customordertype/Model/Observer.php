<?php 
class CompleteWeb_Customordertype_Model_Observer 
{
    public function saveCustomData($observer)
    {
		$event = $observer->getEvent();
        $order = $event->getOrder();
        $quote = $event->getSession()->getQuote();
		$fieldVal = Mage::app()->getFrontController()->getRequest()->getParams();
		$order_type = (int)$fieldVal['order_type'];
        $quote->setData('order_type', $order_type);
        return $this;
    }
}
?>