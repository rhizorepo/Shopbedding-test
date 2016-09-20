<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRGeoIp
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

/**
 * Class MageWorkshop_DRGeoIp_Model_GeoIp
 *
 * @method string getIp()
 * @method string getCountryCode()
 * @method string getCountryName()
 * @method string getRegionCode()
 * @method string getRegionName()
 * @method string getCity()
 * @method string getZipCode()
 * @method string getTimeZone()
 * @method string getLatitude()
 * @method string getLongitude()
 * @method string getMetroCode()
 * @method string getIsEmpty()
 * @method string setIsEmpty(bool $value)
 */
class MageWorkshop_DRGeoIp_Model_GeoIp extends Varien_Object
{
    /**
     * @param string $ip
     */
    public function __construct($ip)
    {
        // freegeoip.net provides a public HTTP API for software developers to search the geolocation of IP addresses.
        $this->setData((array)json_decode(file_get_contents('http://freegeoip.net/json/' . $ip)));
        $this->_hasData();
    }

    private function _hasData()
    {
        $this->setIsEmpty(true);
        foreach($this->getData() as $key => $value) {
            if (!empty($value) && $key != 'ip' && $key != 'is_empty') {
                $this->setIsEmpty(false);
                break;
            }
        }
    }
}
