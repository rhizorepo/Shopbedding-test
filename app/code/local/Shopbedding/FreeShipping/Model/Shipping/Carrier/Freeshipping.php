<?php

/**
 * Free shipping model
 *
 * @category   Mage
 * @package    Mage_Shipping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shopbedding_FreeShipping_Model_Shipping_Carrier_Freeshipping
    extends Mage_Shipping_Model_Carrier_Freeshipping
{

    /**
     * rebuilt to not apply FS when  'free_shipping_subtotal' is not set
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Core_Model_Abstract
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
//      $packageValue = $request->getBaseCurrency()->convert($request->getPackageValueWithDiscount(), $request->getPackageCurrency());
        $packageValue = $request->getPackageValueWithDiscount();

        $this->_updateFreeMethodQuote($request);

        $allow = ($request->getFreeShipping())
            || ($packageValue >= $this->getConfigData('free_shipping_subtotal') &&
                $this->getConfigData('free_shipping_subtotal') > 0);

        //by cw, start for free shipping coupon
        $couponCode = Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getCouponCode();

        $oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
        $rule_data= $oRule->getData();
        //end free shipping coupon

        if (($request->getFreeShipping())
            && ($request->getPackageValueWithDiscount() >= $this->getConfigData('free_shipping_subtotal') || $rule_data['simple_free_shipping'] )
        ) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('freeshipping');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('freeshipping');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $result->append($method);
        }

        return $result;
    }

}
