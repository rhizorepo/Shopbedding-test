<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * One page checkout success page
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Onepage_Success extends Mage_Core_Block_Template
{
    /**
     * @deprecated after 1.4.0.1
     */
    private $_order;

    /* Put magic pixel for Commission Junction right here.
     *
     */


    public function getTrackingPixels()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

        if ($orderId)
            $order = Mage::getModel('sales/order')->load($orderId);

        $imagetag = "";
        $chanAdvImageTag = "";

        // Sorry for the mess.  I did not realize that another pixel would be added, otherwise
        // I would have named the variables more carefully, and formatted the code a little nicer.


        if ($order)
        {

                // $imagetag = "<p style=\"color:red;\">This is the magic pixel. The order status is ".$order->getStatus()."</p>";

                $orderitems = $order->getAllItems();

                // These are for commission junction
                $enterprise_id = "1517059";
                $action_id = "335500";

                $chanAdvSMCID = "53000605";
                $chanAdvOrderVal = $order->getSubtotal();
                $chanAdvOrderID = $order->getId();
                $chanAdvProductList = "";

                $imagetag = "<img src=\"https://www.emjcd.com/u?CID=".$enterprise_id."&TYPE=".$action_id."&OID=".$order->getId();
                $item_counter = 0;

                $first = true;


		$nonadvertisingcost = 0;

                foreach ($orderitems as $orderitem)
                {

                    if ( $orderitem->getPrice() > 0 )
                    {
                        $item_counter ++;

                        $keyvaluepairs = "";

                        // Then item is "real" and we can submit it for commission.

                        $keyvaluepairs .= "&ITEM".$item_counter."=".$orderitem->getSku();
                        $keyvaluepairs .= "&AMT".$item_counter."=".round(100*$orderitem->getPrice())/100;
                        $keyvaluepairs .= "&QTY".$item_counter."=".round($orderitem->getQtyOrdered());

                        if ($first)
                            $first = false;
                        else
                            $chanAdvProductList .= ",";

                        $chanAdvProductList .= $orderitem->getSku();

                        $imagetag .= $keyvaluepairs;


			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$orderitem->getSku() );

			$nonadvertisingcost += ( is_numeric( $product->getCcfProductioncost() ) ) ? $product->getCcfProductioncost() : 0;

                    }


                }

                $imagetag .= "&CURRENCY=USD&METHOD=IMG\" height=\"1\" width=\"20\">\n";


		// for bing tracking code:

		$purchasetotal = round($order->getSubtotal())/100;

		$adwordscode  = <<<EOD
<!-- Google Code for Purchase/Sale Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1068404346;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "666666";
var google_conversion_label = "purchase";
var google_conversion_value = 0;
if ($purchasetotal) {
  google_conversion_value = $purchasetotal;
}
/* ]]> */
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1068404346/?value=$purchasetotal&amp;label=purchase&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
EOD;

		// for Bing tracking code

		$taxcost = round($order->getTaxAmount()*100)/100;
		$shippingcost = round($order->getShippingAmount()*100)/100;
		$nonadvertisingcost = round( $nonadvertisingcost*100 )/100;
		$revenue = round($order->getSubtotal()*100)/100;

		// $bingcode = '<script type="text/javascript"> if (!window.mstag) mstag = {loadTag : function(){},time : (new Date()).getTime()};</script><script id="mstag_tops"type="text/javascript"src="//flex.atdmt.com/mstag/site/ef6f8d5c-8853-41b9-a3ed-0bd0d6bdc07a/mstag.js"></script><script type="text/javascript">mstag.loadTag("analytics", {dedup:"1",domainId:"24171",type:"1",taxcost:"'.$taxcost.'",shippingcost:"'.$shippingcost.'",nonadvertisingcost:"'.$nonadvertisingcost.'",revenue:"'.$revenue.'",actionid:"21581"})</script><noscript><iframe src="//flex.atdmt.com/mstag/tag/ef6f8d5c-8853-41b9-a3ed-0bd0d6bdc07a/analytics.html?dedup=1&domainId=24171&type=1&taxcost=&shippingcost=&nonadvertisingcost=&revenue=&actionid=21581"frameborder="0"scrolling="no"width="1"height="1"style="visibility:hidden;display:none"></iframe></noscript>';
		$bingcode = '<script type="text/javascript"> if (!window.mstag) mstag = {loadTag : function(){},time : (new Date()).getTime()};</script><script id="mstag_tops"type="text/javascript"src="//flex.atdmt.com/mstag/site/ef6f8d5c-8853-41b9-a3ed-0bd0d6bdc07a/mstag.js"></script><script type="text/javascript">mstag.loadTag("conversion", {cp:"5050",dedup:"1"})</script><noscript><iframe src="//flex.atdmt.com/mstag/tag/ef6f8d5c-8853-41b9-a3ed-0bd0d6bdc07a/conversion.html?cp=5050&dedup=1"frameborder="0"scrolling="no"width="1"height="1"style="visibility:hidden;display:none"></iframe></noscript>';

        }


	$imagetag .= $adwordscode."\n\n\n";
	$imagetag .= $bingcode."\n\n\n";


	// Mage::log( print_r( $order ) );

        return $imagetag;
    }


    /**
     * Retrieve identifier of created order
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getOrderId()
    {
        return $this->_getData('order_id');
    }

    /**
     * Check order print availability
     *
     * @return bool
     * @deprecated after 1.4.0.1
     */
    public function canPrint()
    {
        return $this->_getData('can_view_order');
    }

    /**
     * Get url for order detale print
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getPrintUrl()
    {
        return $this->_getData('print_url');
    }

    /**
     * Get url for view order details
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getViewOrderUrl()
    {
        return $this->_getData('view_order_id');
    }

    /**
     * See if the order has state, visible on frontend
     *
     * @return bool
     */
    public function isOrderVisible()
    {
        return (bool)$this->_getData('is_order_visible');
    }

    /**
     * Getter for recurring profile view page
     *
     * @param $profile
     */
    public function getProfileUrl(Varien_Object $profile)
    {
        return $this->getUrl('sales/recurring_profile/view', array('profile' => $profile->getId()));
    }

    /**
     * Initialize data and prepare it for output
     */
    protected function _beforeToHtml()
    {
        $this->_prepareLastOrder();
        $this->_prepareLastBillingAgreement();
        $this->_prepareLastRecurringProfiles();
        return parent::_beforeToHtml();
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     */
    protected function _prepareLastOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(),
                    Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
                $this->addData(array(
                    'is_order_visible' => $isVisible,
                    'view_order_id' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                    'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                    'can_print_order' => $isVisible,
                    'can_view_order'  => Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible,
                    'order_id'  => $order->getIncrementId(),
                ));
            }
        }
    }

    /**
     * Prepare billing agreement data from an identifier in the session
     */
    protected function _prepareLastBillingAgreement()
    {
        $agreementId = Mage::getSingleton('checkout/session')->getLastBillingAgreementId();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($agreementId && $customerId) {
            $agreement = Mage::getModel('sales/billing_agreement')->load($agreementId);
            if ($agreement->getId() && $customerId == $agreement->getCustomerId()) {
                $this->addData(array(
                    'agreement_ref_id' => $agreement->getReferenceId(),
                    'agreement_url' => $this->getUrl('sales/billing_agreement/view',
                        array('agreement' => $agreementId)
                    ),
                ));
            }
        }
    }

    /**
     * Prepare recurring payment profiles from the session
     */
    protected function _prepareLastRecurringProfiles()
    {
        $profileIds = Mage::getSingleton('checkout/session')->getLastRecurringProfileIds();
        if ($profileIds && is_array($profileIds)) {
            $collection = Mage::getModel('sales/recurring_profile')->getCollection()
                ->addFieldToFilter('profile_id', array('in' => $profileIds))
            ;
            $profiles = array();
            foreach ($collection as $profile) {
                $profiles[] = $profile;
            }
            if ($profiles) {
                $this->setRecurringProfiles($profiles);
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $this->setCanViewProfiles(true);
                }
            }
        }
    }
}
