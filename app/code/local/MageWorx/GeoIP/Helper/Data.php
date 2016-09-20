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

class MageWorx_GeoIP_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_GEOIP_DATABASE_TYPE = 'mageworx_customers/geoip/db_type';
	const XML_GEOIP_DATABASE_PATH = 'mageworx_customers/geoip/db_path';
	const XML_GEOIP_ENABLE_BILLING_COUNTRY  = 'mageworx_customers/geoip/enable_billing_country';
	const XML_GEOIP_ENABLE_SHIPPING_COUNTRY = 'mageworx_customers/geoip/enable_shipping_country';
	const XML_GEOIP_ENABLE_ADDRESS_COUNTRY  = 'mageworx_customers/geoip/enable_address_country';
	const XML_GEOIP_FORCE_STORE_VIEW        = 'mageworx_customers/geoip/force_store_view';
	const XML_GEOIP_CURRENCY_SWITCHER       = 'mageworx_customers/geoip/enable_currency_switcher';

	const XML_GEOIP_IP_LIST         = 'mageworx_customers/geoip/ip_list';
	const XML_GEOIP_USER_AGENT_LIST = 'mageworx_customers/geoip/user_agent_list';

	public function isEnableCurrencySwitcher()
	{
		return Mage::getStoreConfigFlag(self::XML_GEOIP_CURRENCY_SWITCHER);
	}

	public function getForceStoreView()
	{
		return Mage::getStoreConfigFlag(self::XML_GEOIP_FORCE_STORE_VIEW);
	}

	public function isEnableBillingCountry()
	{
		return Mage::getStoreConfig(self::XML_GEOIP_ENABLE_BILLING_COUNTRY);
	}

	public function isEnableShippingCountry()
	{
		return Mage::getStoreConfig(self::XML_GEOIP_ENABLE_SHIPPING_COUNTRY);
	}

	public function isEnableAddressCountry()
	{
		return Mage::getStoreConfig(self::XML_GEOIP_ENABLE_ADDRESS_COUNTRY);
	}

	public function getConfGeoIpDbType()
	{
		return Mage::getStoreConfig(self::XML_GEOIP_DATABASE_TYPE);
	}

	public function isCityDbType()
	{
		return ($this->getConfGeoIpDbType() == MageWorx_GeoIP_Model_Database::GEOIP_CITY_DATABASE);
	}

    public function getIpList()
    {
        return array_filter((array) preg_split('/\r?\n/', Mage::getStoreConfig(self::XML_GEOIP_IP_LIST)));
    }

    public function getUserAgentList()
    {
        return array_filter((array) preg_split('/\r?\n/', Mage::getStoreConfig(self::XML_GEOIP_USER_AGENT_LIST)));
    }

	public function getSwitchLngUrl()
	{
		return Mage::getBaseUrl()."geoip/index/switchLng/";
	}

	public function getFlagPath($name = null)
	{
		$flagName = strtolower($name).'.png';
		$filePath = Mage::getSingleton('core/design_package')->getSkinBaseUrl().DS.'images'.DS.'flags'.DS.$flagName;
		
		if (!file_exists($filePath)) {
			return Mage::getDesign()->getSkinUrl('images/flags/'.$flagName);
		} else {
			return $filePath;
		}
	}

	public function getCountryCurrency()
	{
		$path = Mage::getConfig()->getModuleDir('etc', 'MageWorx_GeoIP').DS.'country-currency.csv';
		if (file_exists($path)) {
			return file($path);
		} else {
			return false;
		}
	}

	public function getCurrency($countryCode)
	{
		$curBase = $this->getCountryCurrency();
		if ($curBase !== false && count($curBase)) {
			$codes = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
			foreach ($curBase as $value) {
				$data = explode(';', $value);
				$curVal = trim($data[1]);
				if ($this->prepareCode($data[0]) == $this->prepareCode($countryCode)) {
					if (strstr($curVal, ',')) {
						$curCodes = explode(',', $curVal);
						if ($curCodes) {
							foreach ($curCodes as $code) {
								$code = trim($code);
								if (in_array($code, $codes)) {
									return $code;
								}
							}
						}
					} else {
						if (in_array($curVal, $codes)) {
							return $curVal;
						}
					}
				}
			}
		}
	}

	public function prepareCode($countryCode)
	{
		return strtoupper(trim($countryCode));
	}

	public function prepareCountryCode($countryCode)
	{
		if (!empty($countryCode) && is_string($countryCode)) {
			return explode(',', $countryCode);
		} else {
			return $countryCode;
		}
	}

	public function getCustomerIp()
	{
		$session = new Varien_Object(Mage::getSingleton('core/session')->getValidatorData());
		return $session->getRemoteAddr();
	}

	public function setCookie($key, $value)
	{
		$version = Mage::getVersion();
        $cookie  = Mage::getModel('core/cookie');
        $lifetime = 180;
        if (version_compare($version, '1.2.1', '<=')) {
            $cookie->set($key, base64_encode($value), $lifetime);
        } else {
            $cookie->setLifetime($lifetime);
            $cookie->set($key, base64_encode($value));
        }
	}

	public function getCookie($key)
	{
		$cookie = Mage::getModel('core/cookie');
		if ($cookie->get($key)) {
			return base64_decode($cookie->get($key));
		} else {
			return false;
		}
	}

	public function getDatabasePath()
	{
	    $path = Mage::getStoreConfig(self::XML_GEOIP_DATABASE_PATH);
	    if ($path{0} != '/' && $path{0} != '\\'){
	        $path = Mage::getBaseDir() . DS . $path;
	    }
	    return $path;
	}

	public function getGeoIpHtml($obj)
	{
		$block = Mage::app()->getLayout()
			->createBlock('core/template')
			->setTemplate('geoip/adminhtml-customer-geoip.phtml')
			->addData(array('item' => $obj))
			->toHtml();

        return $block;
	}
}