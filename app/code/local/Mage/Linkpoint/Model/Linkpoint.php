<?php
/**
* Magento
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* Authorize (PREAUTH)
*	Authorize (PREAUTH) is the normal transaction method for ShopSite. When a customer clicks "Submit This Order," ShopSite sends the transaction to First Data Global for authorization, the transaction is authorized, and ShopSite is notified of the authorization. A "hold" for the amount of the purchase is placed on the customer's credit card, but the funds are not "captured" until the merchant goes to the Orders screen in ShopSite and clicks Bill Orders.
* Authorize and Capture (SALE)
*	Authorize and Capture (SALE) authorizes a transaction and captures funds all at once. ShopSite sends a transaction to First Data Global for approval, the transaction is approved, ShopSite is notified of the approval, and the transaction automatically settles at the end of the business day without any further action by the merchant.
* POST AUTH
*	A POSTAUTH transaction is used to capture funds authorized previously using an PREAUTH transaction.
*
* @category   Mage
* @package    Mage_Paygate
* @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*
* @author     Sreeprakash.N. <sree@schogini.com>
* @copyright  Copyright (c) 2008 Schogini Systems (http://schogini.in)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Mage_Linkpoint_Model_Linkpoint extends Mage_Payment_Model_Method_Cc
{

	const CGI_URL = 'https://staging.linkpt.net:1129/LSGSXML';

	const REQUEST_METHOD_CC     = 'CC';
	const REQUEST_METHOD_ECHECK = 'ECHECK';

	const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
	const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
	const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
	const REQUEST_TYPE_CREDIT       = 'CREDIT';
	const REQUEST_TYPE_VOID         = 'VOID';
	const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

	const ECHECK_ACCT_TYPE_CHECKING = 'CHECKING';
	const ECHECK_ACCT_TYPE_BUSINESS = 'BUSINESSCHECKING';
	const ECHECK_ACCT_TYPE_SAVINGS  = 'SAVINGS';

	const ECHECK_TRANS_TYPE_CCD = 'CCD';
	const ECHECK_TRANS_TYPE_PPD = 'PPD';
	const ECHECK_TRANS_TYPE_TEL = 'TEL';
	const ECHECK_TRANS_TYPE_WEB = 'WEB';

	const RESPONSE_DELIM_CHAR = ',';

	const RESPONSE_CODE_APPROVED = 1;
	const RESPONSE_CODE_DECLINED = 2;
	const RESPONSE_CODE_ERROR    = 3;
	const RESPONSE_CODE_HELD     = 4;

	protected $_code  = 'linkpoint';

	protected $_isGateway               = true;
	protected $_canAuthorize            = true;
	protected $_canCapture              = true;
	protected $_canCapturePartial       = false;
	protected $_canRefund               = true;
	protected $_canVoid                 = true;
	protected $_canUseInternal          = true;
	protected $_canUseCheckout          = true;
	protected $_canUseForMultishipping  = true;
	protected $_canSaveCc 				= true; // Gayatri (16/Jun/2010): set this to true so that we can capture payments when generating invoices.

	protected $_authorize	= '';

	/**
	* Send authorize request to gateway
	*
	* @param   Varien_Object $payment
	* @param   decimal $amount
	* @return  Mage_Linkpoint_Model_Linkpoint
	*/


	public function authorize(Varien_Object $payment, $amount)
	{
		$error = false;
		$this->logit('authorize start', array());
		//$this->logit('authorize start', get_class($payment));//Mage_Uos_Model_Order_Payment
		$this->logit('authorize start', get_class_methods(get_class($payment)));
		$this->logit('authorize get_class_vars', get_class_vars(get_class($payment)));

		if ($amount>0) {
			$payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
			$payment->setAmount($amount);

			$this->logit('Calling _buildRequest', array());
			$request = $this->_buildRequest($payment);
			$this->logit('buildrequest call returned', $request);

			$result  = $this->_postRequest($request);
			$this->logit('postRequest call returned', $result);
			
			$payment->setCcApproval($result->getApprovalCode())
			->setLastTransId($result->getTransactionId())
			->setCcTransId($result->getTransactionId())
			->setCcAvsStatus($result->getAvsResultCode())
			->setCcCidStatus($result->getCardCodeResponseCode());


			$code = $result->getResponseReasonCode();
			$text = $result->getResponseReasonText();

			switch ($result->getResponseCode()) {
				case self::RESPONSE_CODE_APPROVED:
					$payment->setStatus(self::STATUS_APPROVED);
					// added by Gayatri 10/Jun/2010
					if( !$order = $payment->getOrder() )
					{
						$order = $payment->getQuote();
					}
					$order->addStatusToHistory(
						$order->getStatus(),
						urldecode($result->getResponseReasonText()) . ' at FirstData',
						$result->getResponseReasonText() . ' from FirstData'
					);
					// end added by Gayatri 10/Jun/2010
					break;
					
				case self::RESPONSE_CODE_DECLINED:
					$error = Mage::helper('paygate')->__('Payment authorization transaction has been declined. ' . "\n$text");
					break;
					
				default:
					$error = Mage::helper('paygate')->__('Payment authorization error. ' . "\n$text");
					break;
			}
		} else {
			$error = Mage::helper('paygate')->__('Invalid amount for authorization.');
		}

		if ($error !== false) {
			Mage::throwException($error);
		}
    
		return $this;
	}

	public function capture(Varien_Object $payment, $amount)
	{
		$this->logit('capture amount', $amount);
		$error = false;
		
		if ($payment->getCcTransId()) {
			$this->logit('capture cc transid DO ONLY CAPTURE FOR THIS TRANSID', $payment->getCcTransId());
			$payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE); // Sree do only capture
		} else {
			$payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);    // Sree do full SALE
			$this->logit('capture NO cc transid SALE ', '');
		}
		
		$payment->setAmount($amount);
		
		$request = $this->_buildRequest($payment);
		$result  = $this->_postRequest($request);
		if ($result->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
			$payment->setStatus(self::STATUS_APPROVED);
			$payment->setCcTransId($result->getTransactionId());
			$payment->setLastTransId($result->getTransactionId());
			// added by Gayatri 10/Jun/2010
			if( !$order = $payment->getOrder() )
			{
				$order = $payment->getQuote();
			}
			$order->addStatusToHistory(
				$order->getStatus(),
				urldecode($result->getResponseReasonText()) . ' at FirstData',
				$result->getResponseReasonText() . ' from FirstData'
			);
			// end added by Gayatri 10/Jun/2010			
		} else {
			if ($result->getResponseReasonText()) {
				$error = $result->getResponseReasonText();
			} else {
				$error = Mage::helper('paygate')->__('Error in capturing the payment');
			}
			if( !$order = $payment->getOrder() )
			{
				$order = $payment->getQuote();
			}
			$order->addStatusToHistory(
				$order->getStatus(),
				urldecode($error) . ' at FirstData',
				$error . ' from FirstData'
			);			
		}

		if ($error !== false) {
			Mage::throwException($error);
		}

		return $this;
	}

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canVoid(Varien_Object $payment)
    {
		return $this->_canVoid;
    }
	public function void(Varien_Object $payment)
	{
		$error = false;
		if ($payment->getVoidTransactionId() && $payment->getAmount() > 0) {
			$payment->setAnetTransType(self::REQUEST_TYPE_VOID);
			$request 	 = $this->_buildRequest($payment);
			$sch_orderid = $payment->getOrder()->getIncrementId() . '-' . number_format($payment->getAmount(), 2, '.', '');
			$request->setXTransId($payment->getVoidTransactionId() . '--' . $sch_orderid);
			
			$result = $this->_postRequest($request);
			if ($result->getResponseCode()==self::RESPONSE_CODE_APPROVED) {
				$payment->setStatus(self::STATUS_VOID);
			} else {
				$errorMsg = $result->getResponseReasonText();
				$error = true;
			}

		} else {
			$errorMsg = Mage::helper('paygate')->__('Error in voiding the payment');
			$error = true;
		}
		
		if ($error !== false) {
			Mage::throwException($errorMsg);
		}
		return $this;	
	}

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
		return $this->_canRefund;
    }
	public function refund(Varien_Object $payment, $amount)
	{
		$error = false;
		//if ($payment->getRefundTransactionId() && $amount>0) {
		if ((($this->getConfigData('test') && $payment->getRefundTransactionId() == 0) || $payment->getRefundTransactionId()) && $amount>0) {
			$payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
			$request = $this->_buildRequest($payment);
			//$request->setXTransId($payment->getRefundTransactionId());
			$sch_orderid = $payment->getOrder()->getIncrementId() . '-' . number_format($payment->getAmount(), 2, '.', '');
			$request->setXTransId($payment->getRefundTransactionId() . '--' . $sch_orderid);
			$request->setXAmount($amount);
			$result = $this->_postRequest($request);
			if ($result->getResponseCode()==self::RESPONSE_CODE_APPROVED) {
				$payment->setStatus(self::STATUS_SUCCESS);
			} else {
				$errorMsg = $result->getResponseReasonText();
				$error = true;
			}

		} else {
			$errorMsg = Mage::helper('paygate')->__('Error in refunding the payment');
			$error = true;
		}

		if ($error !== false) {
			Mage::throwException($errorMsg);
		}
		return $this;
	}

	/**
	* Prepare request to gateway
	*
	* @link   http://www.authorize.net/support/AIM_guide.pdf
	* @param  Mage_Sales_Model_Document $order
	* @return unknown
	*/
	protected function _buildRequest(Varien_Object $payment)
	{
		$this->logit('Inside _buildRequest calling getOrder', array());
		$this->logit('Inside _buildRequest AAA', get_class($payment));
		$this->logit('Inside _buildRequest AAA', get_class_methods(get_class($payment)));

		$order = $payment->getOrder();
		
		$this->logit('Inside _buildRequest called getOrder', array());
		$this->logit('Inside _buildRequest BBB', get_class($order));
		$this->logit('Inside _buildRequest BBB', get_class_methods(get_class($order)));

		if (!$payment->getAnetTransMethod()) {
			$payment->setAnetTransMethod(self::REQUEST_METHOD_CC);
		}

		$this->logit('Inside _buildRequest A1', array());

		$request = Mage::getModel('linkpoint/linkpoint_request')
		->setXVersion(3.1)
		->setXDelimData('True')
		->setXDelimChar(self::RESPONSE_DELIM_CHAR)
		->setXRelayResponse('False');

		$this->logit('Inside _buildRequest A2', get_class_methods(get_class($request)));
		$request->setXTestRequest($this->getConfigData('test') ? 'TRUE' : 'FALSE');

		$this->logit('Inside _buildRequest A3', array());

		$request->setXLogin($this->getConfigData('login'))
		->setXTranKey($this->getConfigData('trans_key'))
		->setXType($payment->getAnetTransType())
		->setXMethod($payment->getAnetTransMethod());

		if ($payment->getAmount()) {
			$request->setXAmount($payment->getAmount(),2);
			$request->setXCurrencyCode($order->getBaseCurrencyCode());
			
		}
		
		$this->logit('Inside _buildRequest A4', array());

		switch ($payment->getAnetTransType()) {
			case self::REQUEST_TYPE_CREDIT:
			case self::REQUEST_TYPE_VOID:
			case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
				$request->setXTransId($payment->getCcTransId());
				$request->setXCardNum($payment->getCcNumber())
					->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth(), $payment->getCcExpYear()))
					->setXCardCode($payment->getCcCid())
					->setXCardName($payment->getCcOwner())    //SreeAdded
					;				
				break;

			case self::REQUEST_TYPE_CAPTURE_ONLY:
				$request->setXAuthCode($payment->getCcAuthCode());
				break;
		}

		$this->logit('Inside _buildRequest A5', array());
		$this->logit('Inside _buildRequest A2', get_class($order));                   
		$this->logit('Inside _buildRequest A2', get_class_methods(get_class($order)));

		if (!empty($order)) {
			$this->logit('ORDER NOT EMPTY AND ORDER FREIGHT AMOUNT', array($order->getShippingAmount()));
			$this->logit('ORDER NOT EMPTY AND ORDER TAX AMOUNT', array($order->getTaxAmount()));
			$this->logit('ORDER NOT EMPTY AND ORDER SUBTOTAL AMOUNT', array($order->getSubtotal()));
			$freight = $order->getShippingAmount();
			$tax = $order->getTaxAmount();
			$subtotal = $order->getSubtotal();
			
			$request->setXInvoiceNum($order->getIncrementId());

			$billing = $order->getBillingAddress();
			$this->logit('Inside _buildRequest CCC order->getBillingAddress', get_class($billing));
			$this->logit('Inside _buildRequest CCC order->getBillingAddress', get_class_methods(get_class($billing)));
			if (!empty($billing)) {

				$email = $billing->getEmail();
				if(!$email)$email = $order->getBillingAddress()->getEmail();
				if(!$email)$email = $order->getCustomerEmail();

				$request->setXFirstName($billing->getFirstname())
				->setXLastName($billing->getLastname())
				->setXCompany($billing->getCompany())
				->setXAddress($billing->getStreet(1))
				->setXCity($billing->getCity())
				->setXState($billing->getRegion())
				->setXZip($billing->getPostcode())
				->setXCountry($billing->getCountry())
				->setXPhone($billing->getTelephone())
				->setXFax($billing->getFax())
				->setXCustId($billing->getCustomerId())
				->setXCustomerIp($order->getRemoteIp())
				->setXCustomerTaxId($billing->getTaxId())
				->setXEmail($email)  //Sree 17Nov2008
				->setXEmailCustomer($this->getConfigData('email_customer'))
				->setXMerchantEmail($this->getConfigData('merchant_email'));
			}

			$shipping = $order->getShippingAddress();
			$this->logit('Inside _buildRequest DDD shipping = order->getShippingAddress()', get_class($shipping));
			if (!$shipping) {
				$shipping = $billing;
			}
			if (!empty($shipping)) {
				$this->logit('SHIPPING OBJECT NOT EMPTY', array());			
				$request->setXShipToFirstName($shipping->getFirstname())
				->setXShipToLastName($shipping->getLastname())
				->setXShipToCompany($shipping->getCompany())
				->setXShipToAddress($shipping->getStreet(1))
				->setXShipToCity($shipping->getCity())
				->setXShipToState($shipping->getRegion())
				->setXShipToZip($shipping->getPostcode())
				->setXShipToCountry($shipping->getCountry());

				if(!isset($freight) || $freight<=0) $freight = $shipping->getShippingAmount();
				if(!isset($tax) || $tax<=0) $tax = $shipping->getTaxAmount();
				if(!isset($subtotal) || $subtotal<=0) $subtotal = $shipping->getSubtotal();				
			}

			$request->setXPoNum($payment->getPoNumber())
			->setXTax($tax)
			->setXSubtotal($subtotal)
			->setXFreight($freight);
			$this->logit('LAST FREIGHT AMOUNT', array($freight));
			$this->logit('LAST TAX AMOUNT', array($tax));
			$this->logit('LAST SUB TOTAL', array($subtotal));
		}

		$this->logit('Inside _buildRequest A6', array());

		switch ($payment->getAnetTransMethod()) {
			case self::REQUEST_METHOD_CC:
				if($payment->getCcNumber()){				
					$request->setXCardNum($payment->getCcNumber())
					->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth(), $payment->getCcExpYear()))
					->setXCardCode($payment->getCcCid())
					->setXCardName($payment->getCcOwner())    //SreeAdded
					;
				}
				break;

			case self::REQUEST_METHOD_ECHECK:
				$request->setXBankAbaCode($payment->getEcheckRoutingNumber())
				->setXBankName($payment->getEcheckBankName())
				->setXBankAcctNum($payment->getEcheckAccountNumber())
				->setXBankAcctType($payment->getEcheckAccountType())
				->setXBankAcctName($payment->getEcheckAccountName())
				->setXEcheckType($payment->getEcheckType());
				break;
		}
		$this->logit('Inside _buildRequest A7', array());

		return $request;
	}

	protected function _postRequest(Varien_Object $request)
	{
		$result = Mage::getModel('linkpoint/linkpoint_result');
		
		/**
		* @TODO
		* Sree handle exception
		*/
		$m = $request->getData();

		$this->logit("_postRequest m array", array('m' => $m));

		// Pre-Build Returned results
		$r = array (
		0 => '1',
		1 => '1',
		2 => '1',
		3 => '(TESTMODE) This transaction has been approved.',
		4 => '000000',
		5 => 'P',
		6 => '0',
		7 => '100000018',
		8 => '',
		9 => '2704.99',
		10 => 'CC',
		11 => 'auth_only',
		12 => '',
		13 => 'Sreeprakash',
		14 => 'N.',
		15 => 'Schogini',
		16 => 'XYZ',
		17 => 'City',
		18 => 'Idaho',
		19 => '695038',
		20 => 'US',
		21 => '1234567890',
		22 => '',
		23 => '',
		24 => 'Sreeprakash',
		25 => 'N.',
		26 => 'Schogini',
		27 => 'XYZ',
		28 => 'City',
		29 => 'Idaho',
		30 => '695038',
		31 => 'US',
		32 => '',
		33 => '',
		34 => '',
		35 => '',
		36 => '',
		37 => '382065EC3B4C2F5CDC424A730393D2DF',
		38 => '',
		39 => '',
		40 => '',
		41 => '',
		42 => '',
		43 => '',
		44 => '',
		45 => '',
		46 => '',
		47 => '',
		48 => '',
		49 => '',
		50 => '',
		51 => '',
		52 => '',
		53 => '',
		54 => '',
		55 => '',
		56 => '',
		57 => '',
		58 => '',
		59 => '',
		60 => '',
		61 => '',
		62 => '',
		63 => '',
		64 => '',
		65 => '',
		66 => '',
		67 => '',
		);

		//Replace the values from Magento 
		$r[7]  = $m['x_invoice_num']; //InvoiceNumber
		$r[8]  = ''; //Description
		$r[9]  = $m['x_amount']; //Amount
		$r[10] = $m['x_method']; //Method = CC
		$r[11] = $m['x_type']; //TransactionType
		$r[12] = $m['x_cust_id']; //CustomerId
		$r[13] = $m['x_first_name']; 
		$r[14] = $m['x_last_name'];
		$r[15] = $m['x_company'];
		$r[16] = $m['x_address'];
		$r[17] = $m['x_city'];
		$r[18] = $m['x_state'];
		$r[19] = $m['x_zip'];
		$r[20] = $m['x_country'];
		$r[21] = $m['x_phone'];
		$r[22] = $m['x_fax'];
		$r[23] = '';
		
		//no shipping
		$m['x_ship_to_first_name'] 	= !isset($m['x_ship_to_first_name'])?$m['x_first_name']:$m['x_ship_to_first_name'];
		$m['x_ship_to_first_name'] 	= !isset($m['x_ship_to_first_name'])?$m['x_first_name']:$m['x_ship_to_first_name'];
		$m['x_ship_to_last_name'] 	= !isset($m['x_ship_to_last_name'])?$m['x_last_name']:$m['x_ship_to_last_name'];
		$m['x_ship_to_company'] 	= !isset($m['x_ship_to_company'])?$m['x_company']:$m['x_ship_to_company'];
		$m['x_ship_to_address'] 	= !isset($m['x_ship_to_address'])?$m['x_address']:$m['x_ship_to_address'];
		$m['x_ship_to_city'] 		= !isset($m['x_ship_to_city'])?$m['x_city']:$m['x_ship_to_city'];
		$m['x_ship_to_state'] 		= !isset($m['x_ship_to_state'])?$m['x_state']:$m['x_ship_to_state'];
		$m['x_ship_to_zip'] 		= !isset($m['x_ship_to_zip'])?$m['x_zip']:$m['x_ship_to_zip'];
		$m['x_ship_to_country'] 	= !isset($m['x_ship_to_country'])?$m['x_country']:$m['x_ship_to_country'];

		$r[24] = $m['x_ship_to_first_name'];
		$r[25] = $m['x_ship_to_last_name'];
		$r[26] = $m['x_ship_to_company'];
		$r[27] = $m['x_ship_to_address'];
		$r[28] = $m['x_ship_to_city'];
		$r[29] = $m['x_ship_to_state'];
		$r[30] = $m['x_ship_to_zip'];
		$r[31] = $m['x_ship_to_country'];

		//Dummy Replace the values from LinkPoint 
		$r[0]  = '1';  // response_code
		$r[1]  = '1';  // ResponseSubcode
		$r[2]  = '1';  // ResponseReasonCode
		$r[3]  = '(TESTMODE2) This transaction has been approved.'; //ResponseReasonText
		$r[4]  = '000000'; //ApprovalCode
		$r[5]  = 'P'; //AvsResultCode
		$r[6]  = '0'; //TransactionId
		$r[37] = '382065EC3B4C2F5CDC424A730393D2DF'; //Md5Hash
		$r[39] = ''; //CardCodeResponse

		// Add LinkPoint Here
		$rr = $this->_linpointapi($m);
		$this->logit("_linpointapi call returned back", array('rr' => $rr));

		//Replace the values from LinkPoint 
		$r[0]  = $rr['response_code'];
		$r[1]  = $rr['response_subcode'];
		$r[2]  = $rr['response_reason_code'];
		$r[3]  = $rr['response_reason_text']; //'(TESTMODE2) This transaction has been approved.'; //ResponseReasonText
		$r[4]  = $rr['approval_code']; //'000000'; //ApprovalCode
		$r[5]  = $rr['avs_result_code']; //'P'; //AvsResultCode
		$r[6]  = $rr['transaction_id']; //'0'; //TransactionId
		$r[37] = $rr['md5_hash'];
		$r[39] = $rr['card_code_response'];

		$this->logit("after r array loaded with rr", array('r' => $r));

		if ($r) {
			$this->logit("setting", '');
			$result->setResponseCode( (int)str_replace('"','',$r[0]) );
			#$result->setResponseCode( 1 );
			$this->logit("setting 2", '');
			$result->setResponseSubcode((int)str_replace('"','',$r[1]));
			$this->logit("setting 3", '');
			$result->setResponseReasonCode((int)str_replace('"','',$r[2]));
			$this->logit("setting 4", '');
			$result->setResponseReasonText($r[3]);
			$this->logit("setting 5", '');
			$result->setApprovalCode($r[4]);
			$this->logit("setting 6", '');
			$result->setAvsResultCode($r[5]);
			$this->logit("setting 7", '');
			$result->setTransactionId($r[6]);
			$this->logit("setting 8", '');
			$result->setInvoiceNumber($r[7]);
			$this->logit("setting 9", '');
			$result->setDescription($r[8]);
			$this->logit("setting 10", '');
			$result->setAmount($r[9]);
			$this->logit("setting 11", '');
			$result->setMethod($r[10]);
			$this->logit("setting 12", '');
			$result->setTransactionType($r[11]);
			$this->logit("setting 13", '');
			$result->setCustomerId($r[12]);
			$this->logit("setting 14", '');
			$result->setMd5Hash($r[37]);
			$this->logit("setting 15", '');
			$result->setCardCodeResponseCode($r[39]);
			$this->logit("setting 16", '');			
		} else {
			Mage::throwException(
			Mage::helper('paygate')->__('Error in payment gateway')
			);
		}
		
		return $result;
	}
	
	function _linpointapi($m)
	{
		$this->logit("_linkpointapi-1 inside the function with param m", array('m' => $m));

		$store 	= $m['x_login'];
		$pem 	=  "./" . $store . ".pem";
		
		if (!isset($m['x_tran_key']) || empty($m['x_tran_key'])) { 
			$m['x_tran_key'] = '/app/code/core/Mage/Paygate/Model/';
		}
		
		$pemf = getcwd() . $m['x_tran_key'] . $store . ".pem";
		if(!file_exists($pemf)){
			$cfg = Mage::getConfig();
			$pth = $cfg->getBaseDir();
			$pemf = "$pth/app/code/local/Mage/Linkpoint/Model/{$store}.pem";
		}
		$pem = $pemf;

		$url = $this->getConfigData('cgi_url');      
		$url = $url ? $url : self::CGI_URL;		
		if ($this->getConfigData('test')) {
			$result = 'GOOD';
			$url	= 'https://staging.linkpt.net:1129/LSGSXML';
			$host	= 'staging.linkpt.net';
		} else {
			$result = 'LIVE';
			$url	= "https://secure.linkpt.net:1129/LSGSXML";
			$host	= 'secure.linkpt.net';
		}

		$m['x_amount'] = number_format($m['x_amount'], 2); // This is needed since oid trailing zero goes :)
		$m['x_amount'] = str_replace(",", "", $m['x_amount']); //To avoid XML error		

		$tax = 0;
		if(isset($m['x_tax'])) $tax = $m['x_tax'];
		
		$subtotal = $m['x_amount'];
		if (isset($m['x_subtotal'])) {
			$m['x_subtotal'] = number_format($m['x_subtotal'], 2);
			$subtotal 		 = $m['x_subtotal'];
		}
		$subtotal = str_replace(",", "", $subtotal);

		$shipping =0;
		if(isset($m['x_freight'])) {
			$m['x_freight'] = number_format($m['x_freight'], 2);
			$shipping = $m['x_freight'];
		}

		// To prevent coupon usage error - SGS-002301: Charge total must be the sum of subtotal, tax, value added tax, and shipping.
		$tax_plus_shipping = $tax + $shipping;
		if($tax_plus_shipping == 0){
			$subtotal = $m['x_amount'];
		}else{
			$subtotal = $m['x_amount'] - $tax_plus_shipping;		
		}

		$subtotal = number_format($subtotal, 2);
		$subtotal = str_replace(",", "", $subtotal);

		$mrchnt="
		<merchantinfo>
		<configfile>$store</configfile>
		<keyfile>$pem</keyfile>
		<host>$host</host>
		<port>1129</port>
		</merchantinfo>";

		$addrnum = preg_replace("/[^0-9\.\-\/]/","",$m['x_address']); //292 works
		$append  = true; // add more xml fields like address etc.
		
		// Generate the XML based on the transaction type
		$this->logit("_linkpointapi-1 inside the function with param m", array('m' => $m));
		if ($m['x_type'] == 'AUTH_CAPTURE') { //Authorize and Capture

			$xml = "<order>
				<transactiondetails>
				<oid>{$m['x_invoice_num']}-{$m['x_amount']}-" . substr(uniqid(),-4) . "</oid>
				</transactiondetails>
				$mrchnt
				<orderoptions>
				<result>$result</result>
				<ordertype>SALE</ordertype>
				</orderoptions>
				<payment>";
		} elseif ($m['x_type'] == 'AUTH_ONLY') {  //Authorize Only
			$xml="<order>
				<transactiondetails>
				<oid>{$m['x_invoice_num']}-{$m['x_amount']}-" . substr(uniqid(),-4) . "</oid>
				</transactiondetails>
				$mrchnt
				<orderoptions>
				<result>$result</result>
				<ordertype>PREAUTH</ordertype>
				</orderoptions>
				<payment>";
		}elseif ($m['x_type'] == 'CAPTURE_ONLY' || $m['x_type'] == 'PRIOR_AUTH_CAPTURE') {  //Capture Only
			$oid=""; 
			if (isset($m['x_trans_id'])) $oid=$m['x_trans_id']; // this is oid that is correct and what comes back 
			if ($oid=="") Mage::throwException('Could not get transaction(Order) Id');

			$xml="<order>
				<transactiondetails>
				<oid>$oid</oid>
				</transactiondetails>
				$mrchnt
				<orderoptions>
				<result>LIVE</result>
				<ordertype>POSTAUTH</ordertype>
				</orderoptions>
				<payment>
					<tax>$tax</tax>
					<subtotal>{$subtotal}</subtotal>
					<shipping>{$shipping}</shipping>
					<chargetotal>{$m['x_amount']}</chargetotal>
				</payment>
				</order>";
			$append = false;
		} elseif ($m['x_type'] == 'CREDIT') {  //Refund
			$temp = explode('--', $m['x_trans_id']);
			$oid = $temp[1];
			$xml = "<order>
					  <transactiondetails>
					    <oid>{$oid}-refund-" . substr(uniqid(),-4) . "</oid>
					  </transactiondetails>
					  $mrchnt
					  <orderoptions>
					    <result>$result</result>
					    <ordertype>CREDIT</ordertype>
					  </orderoptions>
					  <payment>
					    <tax>0</tax>
						<chargetotal>{$m['x_amount']}</chargetotal>
					  </payment>
					  <creditcard>
						<cardnumber>{$m['x_card_num']}</cardnumber>
						<cardexpmonth>".substr($m['x_exp_date'],0,2)."</cardexpmonth>
						<cardexpyear>".substr($m['x_exp_date'],-2)."</cardexpyear>
					  </creditcard>					  
					</order>";
			$append = false;
		} elseif ( $m['x_type'] == 'VOID' ) { //Void Only
			$temp = explode('--', $m['x_trans_id']);
			$oid  = $temp[1];
			$xml  = "<order>
					<transactiondetails>
					  <oid>{$oid}</oid>
					</transactiondetails>
					$mrchnt
					<orderoptions>
					  <result>$result</result>
					  <ordertype>VOID</ordertype>
					</orderoptions>
					<payment>
						<tax>0</tax>
						<chargetotal>{$m['x_amount']}</chargetotal>
					</payment>
				</order>";
			$append = false;
		} else {
			Mage::throwException('Unsupported Operation: ' . $m['x_type'] );	
		}
		
		// Append the extra information to the XML
		if ($append) {
			// clean up the strings for XML else you can get
			// an error like this: SGS-020003: Invalid XML
			$m['x_first_name'] 			= htmlentities($m['x_first_name'], ENT_QUOTES, 'UTF-8');
			$m['x_last_name'] 			= htmlentities($m['x_last_name'], ENT_QUOTES, 'UTF-8');
			$m['x_address'] 			= htmlentities($m['x_address'], ENT_QUOTES, 'UTF-8');
			$m['x_ship_to_first_name'] 	= htmlentities($m['x_ship_to_first_name'], ENT_QUOTES, 'UTF-8');
			$m['x_ship_to_last_name'] 	= htmlentities($m['x_ship_to_last_name'], ENT_QUOTES, 'UTF-8');
			$m['x_ship_to_address'] 	= htmlentities($m['x_ship_to_address'], ENT_QUOTES, 'UTF-8');
			
			$xml .= "<tax>$tax</tax>
					<subtotal>{$subtotal}</subtotal>
					<shipping>{$shipping}</shipping>
					<chargetotal>{$m['x_amount']}</chargetotal>
					</payment> 
					<creditcard>
					<cardnumber>{$m['x_card_num']}</cardnumber>
					<cardexpmonth>".substr($m['x_exp_date'],0,2)."</cardexpmonth>
					<cardexpyear>".substr($m['x_exp_date'],-2)."</cardexpyear>
					<cvmvalue>{$m['x_card_code']}</cvmvalue>
					<cvmindicator>provided</cvmindicator>
					</creditcard>
					<billing>
					<name>{$m['x_first_name']}, {$m['x_last_name']}</name>
					<address1>{$m['x_address']}</address1>
					<city>{$m['x_city']}</city>
					<state>{$m['x_state']}</state>
					<zip>{$m['x_zip']}</zip>
					<country>{$m['x_country']}</country>
					<email>{$m['x_email']}</email>
					<phone>{$m['x_phone']}</phone>
					<addrnum>{$addrnum}</addrnum>
					</billing>
					<shipping>
					<name>{$m['x_ship_to_first_name']}, {$m['x_ship_to_last_name']}</name>
					<address1>{$m['x_ship_to_address']}</address1>
					<city>{$m['x_ship_to_city']}</city>
					<state>{$m['x_ship_to_state']}</state>
					<zip>{$m['x_ship_to_zip']}</zip>
					<country>{$m['x_ship_to_country']}</country>
					</shipping>
					<notes>
					<comments></comments>
					</notes>
					</order>
					";
		}
		$this->logit("_linkpointapi xml input", array('xml' => $xml));

		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt ($ch, CURLOPT_SSLCERT, $pemf);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		$result = curl_exec ($ch);

		// Added by Gayatri: v1.1.6
		$curl_error = '';
		$curl_error = curl_error($ch);
		// End Added by Gayatri: v1.1.6
		
		// Modified by Gayatri: v1.1.6
		$this->logit("_linkpointapi curl_error", array('curl_error' => $curl_error));

		curl_close($ch);
		
		// Added by Gayatri: v1.1.6
		if( $curl_error != '' )
		{
			Mage::throwException('Error: ' . $curl_error);
		}
		// End Added by Gayatri: v1.1.6
	
		preg_match_all ("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
//echo '<pre>' . print_r(htmlentities($xml), 1) . print_r(htmlentities($result), 1) . print_r(htmlentities($retarr), 1) . '</pre>';exit;		
		// Load Default Dummy Values
		$rr 						= array();
		$rr['response_code']		= '1';	
		$rr['response_subcode']		= '1';
		$rr['response_reason_code']	= '1';
		$rr['response_reason_text'] = '(TESTMODE2) This transaction has been approved.';
		$rr['approval_code'] 		= '000000'; //ApprovalCode
		$rr['avs_result_code']		= 'P';
		$rr['transaction_id']		= '0';
		$rr['md5_hash']				= '382065EC3B4C2F5CDC424A730393D2DF';
		$rr['card_code_response']	= '';
		$this->logit("_linkpointapi preloading rr with defaults", array('rr' => $rr));
		
		if(!isset($retarr)) {
			$retarr = array('RETARR is EMPTY');
			$this->logit("_linkpointapi returned retarr before if condition", array('rr' => $retarr));
			Mage::throwException('Error: Could not connect to the gateway. Please confirm that port 1129 is open');
		}

		// Now check for approval
		if (( isset($retarr['r_approved']) && $retarr['r_approved']=='APPROVED' )) {
			// SUCCESS
			$this->logit("_linkpointapi SUCCESS", array());
			
			$rr['response_code']		= '1';	
			$rr['response_subcode']		= '1';
			$rr['response_reason_code']	= '1';
			
			// Remove this from here
			if (isset($retarr['r_error']) && !empty($retarr['r_error'])) $rr['response_reason_text'] = $retarr['r_error'];
			
			if (isset($retarr['r_code']) && !empty($retarr['r_code'])) $rr['response_reason_text']	= $retarr['r_code'];
			if ($this->getConfigData('test')) {
				if (isset($retarr['r_message']) && !empty($retarr['r_message'])) $rr['response_reason_text'] = $retarr['r_message'];
			}
			if (isset($retarr['r_ref']) && !empty($retarr['r_ref'])) $rr['approval_code'] 	= $retarr['r_ref'];
			if (isset($retarr['r_avs']) && !empty($retarr['r_avs'])) $rr['avs_result_code']	= $retarr['r_avs'];
			if (isset($retarr['r_ordernum']) && !empty($retarr['r_ordernum'])) $rr['transaction_id']	= $retarr['r_ordernum'];
			
			// added by Gayatri
			if( !isset($retarr['r_avs']) || empty($retarr['r_avs']) ) $retarr['r_avs']='|||||';
			$dd    = array();
			$dd[0] = substr($retarr['r_avs'], 0, 1);
			$dd[1] = substr($retarr['r_avs'], 1, 1);
			$dd[3] = substr($retarr['r_avs'], 3, 1);
			// End Gayatri
			
			// the magento admin can what type of checks to perform
			// the extra condition in the 3 if statements below added by Gayatri
			$err = '';
			if($this->getConfigData('useavs_addr') == 1 && $dd[0] != 'Y') $err .= "Address mismatch. "; 
			if($this->getConfigData('useavs_zip') == 1 && $dd[1] != 'Y') $err .= "Zip code mismatch. "; 
			if($this->getConfigData('useccv') == 1 && $dd[3] != 'M') $err .= "Card code mismatch or is not applicable. ";
			$rr['response_reason_text'] .= "\n" . $err;
			
			$rr['response_reason_text'] .= "\nTransaction ID: " . $rr['transaction_id'];
			
			/* AS OF 10/Jun/2010 WE DO NOT STOP AN ORDER PROCESS WHEN AVS OR CCV FAILS
			   INSTEAD WE STORE THE FAIL MESSAGE ALONG WITH THE ORDER AND SEND THE STORE
			   OWNER AN EMAIL
			// return check failure only in LIVE mode - added by Gayatri on 21/May/2010
			if (!$this->getConfigData('test') && $err != '') {
				$this->logit("_linkpointapi ADDRESS FAILED $err", array());
				$rr['response_code']		= '0';	
				$rr['response_subcode']		= '0';
				$rr['response_reason_code']	= '0';
				$rr['approval_code'] 	= '000000'; //ApprovalCode
				$rr['avs_result_code']	= 'P';
				$rr['transaction_id']	= '0';			
			}
			*/

			if ($err != '') {
				$to  = $this->getConfigData('merchant_email');
				$sub = 'Inv Num #' . $m['x_invoice_num'] . ': AVS or CCV failed';
				$msg = $rr['response_reason_text'] . "\n";
				$msg .= 'AVS Code: ' . $rr['avs_result_code'] . "\n";
				$msg .= 'Transaction ID: ' . $rr['transaction_id'] . "\n";
				$msg .= 'Approval Code: ' . $rr['approval_code'] . "\n";
				$msg .= "\nCUSTOMER INFO\n";
				$msg .= "\nName: {$m['x_first_name']}, {$m['x_last_name']}\nAddress: {$m['x_address']}\nCity: {$m['x_city']}\nState: {$m['x_state']}\nZip: {$m['x_zip']}\nCountry: {$m['x_country']}\nEmail:{$m['x_email']}\nPhone:{$m['x_phone']}\n";
				//@mail($to, $sub, $message, 'From: ' . $to);
			}
		} else {
			//FAILED
			$this->logit("_linkpointapi FAILED", array());
			
			$rr['response_code']		= '0';	
			$rr['response_subcode']		= '0';
			$rr['response_reason_code']	= '0';
			if (isset($retarr['r_error']) && !empty($retarr['r_error'])) $rr['response_reason_text'] = $retarr['r_error'];
			$rr['approval_code'] 	= '000000'; //ApprovalCode
			$rr['avs_result_code']	= 'P';
			$rr['transaction_id']	= '0';
			
			// Friendly messages based on the error type
			if (strpos($retarr['r_error'], 'SGS-000001') == 0) {
				$rr['response_reason_text'] = 'Your transaction has been declined';
			} else if (strpos($retarr['r_error'], 'SGS-002300') == 0) {
				$rr['response_reason_text'] = 'Your transaction has been declined. Please call the bank voice center.';
			} else if (strpos($retarr['r_error'], 'SGS-000002') == 0) {
				$rr['response_reason_text'] = 'Sorry, the gateway is experiencing problems. Please try again after some time.';
			} else if (strpos($retarr['r_error'], 'SGS-002304') == 0) {
				$rr['response_reason_text'] = 'Your credit card has expired or is cancelled.';
			} else if (strpos($retarr['r_error'], 'SGS-002000') == 0) {
				$rr['response_reason_text'] = 'Currently, we are not setup to support this card type.';
			} else if (strpos($retarr['r_error'], 'SGS-005005') == 0) {
				$rr['response_reason_text'] = 'Duplicate transaction. Please try again after some time.';
			} else if (strpos($retarr['r_error'], 'SGS-005003') == 0) {
				$rr['response_reason_text'] = 'Duplicate order.';
			} else if (strpos($retarr['r_error'], 'SGS-005002') == 0) {
				$rr['response_reason_text'] = 'Currently, we are not setup to support this transaction.';
			} else if (isset($retarr['r_error']) && !empty($retarr['r_error'])){
				// instead of showing the specific error to the user show this generic message
				// for all other error transactions.
				$rr['response_reason_text'] = 'Your transaction has been declined.';
			}
		}
		
		$this->logit("_linkpointapi returning rr", array('rr' => $rr));
		return $rr;
	}
	
	function logit($func, $arr=array()) {
		if(!$this->getConfigData('debug')) return; // Set via Admin

		if(!isset($this->pth)||empty($this->pth)){
			$cfg = Mage::getConfig();
			$this->pth = $cfg->getBaseDir();
		}

		$f = $this->pth . '/magento_log.txt';
		if(!is_writable($f))return;

		$a='';
		if(count($arr)>0)$a=var_export($arr,true);
		@file_put_contents( $f , '----- Inside ' . $func . ' =1= ' . date('d/M/Y H:i:s') . ' -----' . "\n" . $a, FILE_APPEND);
	}
}
