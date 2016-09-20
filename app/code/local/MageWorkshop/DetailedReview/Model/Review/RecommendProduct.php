<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Model_Review_RecommendProduct extends Varien_Object
{
    const XML_PATH_RECOMMENDED_PRODUCT = 'detailedreview/social_share_optios/recommend_qty_available';

    protected $_optionArray;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        if (is_null($this->_optionArray)) {
            $helper = Mage::helper('detailedreview');
            $this->_optionArray = array('0' => $helper->__('Please, select variant'));
            if ($options = Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review_RecommendProduct::XML_PATH_RECOMMENDED_PRODUCT)) {
                foreach (explode(',', $options) as $value) {
                    $this->_optionArray[$value] = $helper->__($value);
                }
            }
        }
        return $this->_optionArray;
    }

    /**
     * @param int $key
     * @return mixed
     */
    public function getOptionValue($key) {
        $options = $this->getOptionArray();
        return array_key_exists($key, $options) ? $options[$key] : $options[0];
    }

}
