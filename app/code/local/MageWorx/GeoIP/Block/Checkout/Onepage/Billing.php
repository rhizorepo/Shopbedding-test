<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * GeoIP extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_GeoIP_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    protected function _construct()
    {
		parent::_construct();
    }

    public function getCountryHtmlSelect($type)
    {
    	if (Mage::helper('geoip')->isEnableBillingCountry()) {
			$geoip     = Mage::getSingleton('geoip/geoip')->getGeoIP(Mage::helper('geoip')->getCustomerIp());
	        $countryId = $this->getAddress()->getCountryId();
	        if (is_null($countryId)) {
	            $countryId = Mage::getStoreConfig('general/country/default');
	        }
	        if ($geoip->getCode() && $countryId != $geoip->getCode()) {
	        	$countryId = $geoip->getCode();
	        }
	        $select = $this->getLayout()->createBlock('core/html_select')
	            ->setName($type.'[country_id]')
	            ->setId($type.':country_id')
	            ->setTitle(Mage::helper('checkout')->__('Country'))
	            ->setClass('validate-select')
	            ->setValue($countryId)
	            ->setOptions($this->getCountryOptions());
	        if ($type === 'shipping') {
	            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
	        }

	        return $select->getHtml();
    	} else {
    		return parent::getCountryHtmlSelect($type);
    	}
    }
}