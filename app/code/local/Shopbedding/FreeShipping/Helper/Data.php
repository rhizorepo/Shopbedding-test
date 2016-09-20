<?php

class Shopbedding_FreeShipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_rates = array();

    public function formatSortRates($rateGroups)
    {
        if (!$this->_rates){
            $rates = array();
            foreach ($rateGroups as $group){
                $rates = array_merge($rates, $group);
            }
            foreach ($rates as $rate){
                $this->_rates[$rate->getPrice()] = $rate;
            }
            ksort($this->_rates);
        }
        return $this->_rates;
    }
}