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

class MageWorx_GeoIP_Model_Core_Store extends Mage_Core_Model_Store
{
    protected function _construct()
    {
    	parent::_construct();
    }

    protected function _beforeSave()
    {
    	$code = $this->getGeoipCountryCode();
    	if ($code) {
            $db = Mage::getModel('core/resource')->getConnection('core_write');
    		$code = implode(',', $code);
    		$this->setGeoipCountryCode($code);
    	}
    	parent::_beforeSave();
    }

	protected function _afterLoad()
    {
    	$code = $this->getGeoipCountryCode();
    	if ($code) {
    		$code = explode(',', $code);
    		$this->setGeoipCountryCode($code);
    	}
    	parent::_afterLoad();
    }
}