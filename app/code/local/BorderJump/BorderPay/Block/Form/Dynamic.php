<?php

class BorderJump_BorderPay_Block_Form_Dynamic extends Mage_Payment_Block_Form_Ccsave {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('borderpay/payment/form/dynamic.phtml');
    }
    
    public function getCcAvailableTypes() {
        return array(
            //'amex' => 'American Express',
            'visa' => 'Visa',
            'master' => 'MasterCard',
            'discover' => 'Discover'//,
            //'diners_club' => 'Diner\'s Club',
            //'jcb' => 'JCB'
        );
    }
    
    public function getPaymentMethods($country_code = null, $currency_code = null) {
        $response = $this->getMethod()->apiMerchantPaymentServices();
        if ($response) {
            $services = array();
            foreach ($response as $service_data) {
                $service = $service_data['service'];
                $services[$service['name']] = $service['payment_service_id'];
            }
            return $services;
        }
    }
}
