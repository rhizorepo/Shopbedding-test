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

class MageWorx_GeoIP_Model_Observer
{
	public function geoipAutoswitcher()
	{
		if (Mage::app()->getStore()->isAdmin()) {
			return ;
		}
//		$customerIp = '62.147.0.1';     // FRANCE
//		$customerIp = '24.24.24.24';    // USA
//		$customerIp = '62.146.105.136'; // GERMANY

		$helper       = Mage::helper('geoip');
		$customerIp   = $helper->getCustomerIp();
		$request      = Mage::app()->getRequest();
        $requestStore = $request->getParam('___store');
        $response     = Mage::app()->getResponse();

//        $customerIp = '190.220.155.106'; //argentina

        $isWebsiteScope = Mage::getStoreConfigFlag('mageworx_customers/geoip/store_switcher_scope');
        if ($requestStore && $isWebsiteScope) {
            $helper->setCookie('geoip_store_code', $requestStore);
        }

		$currencyCookie    = $helper->getCookie('currency_code');
		$customerStoreCode = $helper->getCookie('geoip_store_code');

		if ($customerStoreCode && $helper->getForceStoreView()) {
			Mage::app()->setCurrentStore($customerStoreCode);
		}

        $isEnabled = Mage::getStoreConfigFlag('mageworx_customers/geoip/enable_store_switcher');
        $disableKey = Mage::getStoreConfig('mageworx_customers/geoip/disable_store_switcher_key');
        $exceptionUrls = array_filter((array) preg_split('/\r?\n/', Mage::getStoreConfig('mageworx_customers/geoip/store_switcher_exception_urls')));
        $cookieLifetime = Mage::getStoreConfig('web/cookie/cookie_lifetime');

        $isException = false;

        if ($helper->isEnableCurrencySwitcher()) {
        	$mageStore = Mage::app()->getStore();
            if ($mageStore->getCurrentCurrencyCode() != $currencyCookie) {
                $currency = null;
                if ($currencyCookie) {
                    $currency = $currencyCookie;
                } else {
                    $geoip    = Mage::getSingleton('geoip/geoip')->getGeoIP($customerIp);
                    $currency = $helper->getCurrency($geoip->getCode());
                }
                if ($currency && ($mageStore->getCurrentCurrencyCode() != $currency)) {
                    $mageStore->setCurrentCurrencyCode($currency);
                    $helper->setCookie('currency_code', $currency);

                    if (Mage::getSingleton('checkout/session')->getQuote()) {
                        Mage::getSingleton('checkout/session')->getQuote()
                            ->collectTotals()
                            ->save();
                    }
                } else {
                    $helper->setCookie('currency_code', $mageStore->getCurrentCurrencyCode());
                }
            }
        }

        if (!empty($exceptionUrls)) {
            $requestString = $request->getRequestString();
            foreach ($exceptionUrls as $url) {
                $url = str_replace('*', '.*?', $url);
                if (preg_match('!^' . $url . '$!i', $requestString)) {
                    $isException = true;
                    break;
                }
            }
        }

        $ipList = $helper->getIpList();
        if ($ipList) {
            foreach ($ipList as $ip) {
                $ip = str_replace(array('*', '.'), array('\d+', '\.'), $ip);
                if (preg_match("/^{$ip}$/", $customerIp)) {
                    $isException = true;
                    break;
                }
            }
        }

        $userAgentList = $helper->getUserAgentList();
        $userAgent = Mage::helper('geoip/http')->getHttpUserAgent();
        if ($userAgentList && $userAgent) {
	        foreach ($userAgentList as $agent) {
	        	$agent = str_replace('*', '.*', $agent);
	            if (preg_match("/{$agent}$/i", $userAgent)) {
	                $isException = true;
	                break;
	            }
	        }
        }

        if ($isException) {
            return;
        }

        if ($request->getQuery('_store_switcher_') == $disableKey || $request->getCookie('_store_switcher_') == $disableKey) {
            Mage::app()->getCookie()->set('_store_switcher_', $disableKey, $cookieLifetime);
        } elseif ($isEnabled) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();

            foreach (Mage::app()->getStores() as $store) {
            	if ($store->getIsActive() == 1) {
	                if ($isWebsiteScope && $store->getWebsiteId() != $websiteId) {
	                    continue;
	                }
	                $stores[$store->getCode()] = $store;
            	}
            }

    		if (count($stores) > 1) {
    			$currentStoreCode = Mage::app()->getStore()->getCode();
    			if ($customerStoreCode && $customerStoreCode != $currentStoreCode && !$helper->getForceStoreView()) {
    				return;
    			}
    		    if (!$customerStoreCode) {
    		        $geoip = Mage::getSingleton('geoip/geoip')->getGeoIP($customerIp);
    		        $customerCountryCode = $helper->prepareCode($geoip->getCode());
    		    }
    		    if (isset($customerCountryCode)) {
                    foreach ($stores as $store) {
                        $storeCountryCodes = $helper->prepareCountryCode($store->getGeoipCountryCode());
                        if (is_array($storeCountryCodes) && in_array($customerCountryCode, $storeCountryCodes)) {
                            $customerStoreCode = $store->getCode();
                            break;
                        }
                    }
                    if ($customerStoreCode) {
                        $helper->setCookie('geoip_store_code', $customerStoreCode);
                        Mage::app()->getCookie()->set('store', $customerStoreCode, true);
                    }
    		    }
    			if ($customerStoreCode && $customerStoreCode != $currentStoreCode) {
				    return $response->setRedirect(Mage::app()->getStore($customerStoreCode)->getBaseUrl(). ltrim($request->getRequestString(), '/'));
    			}
    		}
        }
	}

	public function setCurrency()
	{
		if (Mage::helper('geoip')->isEnableCurrencySwitcher()) {
			$filter   = new Zend_Filter_StripTags();
			$currency = $filter->filter(Mage::app()->getFrontController()->getRequest()->getParam('currency'));
			Mage::helper('geoip')->setCookie('currency_code', $currency);
		}
	}

    public function saveStoreCountries($observer)
    {
        $store = $observer->getStore();
        $storeEdited = Mage::getModel('core/store')->load($store->getStoreId());

        if(!$storeEdited->getGeoipCountryCode() && $store->getGeoipCountryCode()){
            $db = Mage::getModel('core/resource')->getConnection('core_write');
            $query = "UPDATE ".$db->getTableName('core_store')." SET geoip_country_code = '".$store->getGeoipCountryCode()."' WHERE store_id = ".$store->getStoreId();
            $db->query($query);
        }
    }
}
