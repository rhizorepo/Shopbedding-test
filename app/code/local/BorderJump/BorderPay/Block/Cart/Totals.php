<?php
class BorderJump_BorderPay_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    public function displayGrandtotal()
    {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
        if ($payment->getMethodInstance()->getCode() != 'borderpay') {
            return $this->displayBaseGrandtotal();
        }
        $firstTotal = reset($this->_totals);
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getGrandTotal();
            return Mage::app()->getStore()->getCurrentCurrency()->format($total, array(), true);
        }
        return '-';
    }
}