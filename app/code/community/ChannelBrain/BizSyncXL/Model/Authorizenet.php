<?php
/**
 * 
 *
 * @author Gary MacDougall
 * @version $Id$
 * @copyright FreeportWeb, Inc., 23 February, 2012
 * @package ChannelBrain_BizSyncXL
 **/

/**
 * Define DocBlock
 **/

class ChannelBrain_BizSyncXL_Model_Authorizenet extends Mage_Paygate_Model_Authorizenet
{
    /**
     * @todo uncomment the following if you want to refund the payment, or make a patial payment
     */
    //protected $_canCapturePartial       = false;
    //protected $_canRefund               = false;
    protected $_canSaveCc = true;
    protected $_canUseInternal = true;

  
    /**
     * It sets card`s data into additional information of payment model
     * AuthorizeNet has added additional_information field in sale_flat_order_payment table
     * where they savve credit card info, and disallow to save the card in other fields,
     * This method is temprory and we need to fetch the card info from additional_information
     * field.
     * @param Mage_Paygate_Model_Authorizenet_Result $response
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Varien_Object
     */
    protected function _registerCard(Varien_Object $response, Mage_Sales_Model_Order_Payment $payment)
    {
	//Mage::log("In ChannelBrain_BizSyncXL_Model_Authorizenet::_registerCard");

	// 2012nov20 PJQ - set the AVS code and Approval code into the base payment data
	// trying to save them in the card data was causing issues
	$payment->setCcAvsStatus($response->getAvsResultCode());
	$payment->setCcApproval($response->getApprovalCode());

	// call the parent class to do the regular processing...
	return parent::_registerCard($response, $payment);

/*
        $cardsStorage = $this->getCardsStorage($payment);
       	$card = $cardsStorage->registerCard();
        $card
		->setRequestedAmount($response->getRequestedAmount())
		->setBalanceOnCard($response->getBalanceOnCard())
		->setLastTransId($response->getTransactionId())
		->setProcessedAmount($response->getAmount())
		->setCcType($payment->getCcType())
		->setCcOwner($payment->getCcOwner())
		->setCcLast4($payment->getCcLast4())
		->setCcExpMonth($payment->getCcExpMonth())
		->setCcExpYear($payment->getCcExpYear())
		->setCcSsIssue($payment->getCcSsIssue())
		->setCcSsStartMonth($payment->getCcSsStartMonth())
		->setCcSsStartYear($payment->getCcSsStartYear())
		->setCcAvsStatus($response->getAvsResultCode())
		->setApprovalCode($response->getApprovalCode())
// saving the CcCID is not always going to work depending
// on the options the cart has setup.
//		->setCcCid($payment->getCcCid())
		->setCcNumber($payment->getCcNumber());

        $cardsStorage->updateCard($card);
        //below is the only reason to override this method,
        //$this->_clearAssignedData($payment);
        //parent::_clearAssignedData($payment);
        return $card;
*/
    }

}
 
?>
