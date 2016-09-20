<?php
class BorderJump_BorderShip_Model_Currency extends Mage_Directory_Model_Currency
{
    /**
     * Convert price to currency format
     *
     * @param   double $price
     * @param   string $toCurrency
     * @return  double
     */
    public function convert($price, $toCurrency=null)
    {
        if (is_null($toCurrency)) {
            return $price;
        } 
        elseif ($rate = $this->getRate($toCurrency)) {
            return ceil($price*$rate*100)/100;
	}
    }
}
