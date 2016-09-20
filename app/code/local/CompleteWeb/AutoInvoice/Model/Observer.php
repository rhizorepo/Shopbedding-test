<?php
class CompleteWeb_AutoInvoice_Model_Observer
{
	public function GenerateInvoice(Varien_Event_Observer $observer)
	{
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder();
		
		$payment_method = $order->getPayment()->getMethod();
		if($order->canInvoice()){
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
			if (!$invoice->getTotalQty()){
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
			}
			$invoice->register();
			if($payment_method == 'authorizenet' || $payment_method == 'paypal_express'){
				$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			}
			
			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder());
			$transactionSave->save();

			$invoice->sendEmail(true);
			$order->setData('state', "complete");

			$order->setStatus("complete");
			$history = $order->addStatusHistoryComment('Order was set to Complete by our automation tool.', false);
			$history->setIsCustomerNotified(true);
			$order->save();
			if($payment_method == 'authorizenet' || $payment_method == 'paypal_express'){
				$invoice->capture()->save(); 
			}else{
				$invoice->save();
			}
			Mage::log("Invoice created for order id :".$order->getIncrementId() , null, 'autoinvoice.log');
		} else {
			Mage::log("Can not create invoice for order id :".$order->getIncrementId() , null, 'autoinvoice.log');
		}
	}
}
?>