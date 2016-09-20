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

class MageWorx_GeoIP_Block_Customer_Address_Edit extends Mage_Customer_Block_Address_Edit
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

	public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
	{
		if (Mage::helper('geoip')->isEnableAddressCountry()) {
			$geoip = Mage::getSingleton('geoip/geoip')->getGeoIP(Mage::helper('geoip')->getCustomerIp());
			if ($geoip->getCode()) {
				$defValue = $geoip->getCode();
			}
    	}
    	return parent::getCountryHtmlSelect($defValue, $name, $id, $title);
	}
}