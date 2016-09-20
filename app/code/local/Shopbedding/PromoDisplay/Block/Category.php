<?php
class Shopbedding_PromoDisplay_Block_Category extends Mage_Core_Block_Template {

    /**
     * Static block identifier
     *
     * @var string
     */
	protected $_block;
	
    /**
     * From date
     *
     * @var string
     */
	protected $_from;
	
    /**
     * To date
     *
     * @var string
     */
	protected $_to;
	
	public function __construct() {
		$this->setTemplate('promodisplay/banner.phtml');
		$this->_block = Mage::getStoreConfig('general/defaults/category_promo_cms');
		$this->_from = strtotime(Mage::getStoreConfig('general/defaults/show_category_from_date'));
		$this->_to = strtotime(Mage::getStoreConfig('general/defaults/show_category_to_date'));
        parent::_construct();
	}


    /**
     * dsveshinskiy - fix issue with FPC
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'PROMODISPLAY_CATEGORY',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate()
        );
    }
	
    /**
     * Render block HTML
     * 
     * @return string
     */
    protected function _toHtml() {
		$current = strtotime(date('m/d/y'));
		
		if(Mage::getStoreConfig('general/defaults/show_category')
				&& ($current >= $this->_from)
				&& ($current <= $this->_to)
			) {
			return parent::_toHtml();
		}
	}
	
	/**
	* Retrieve static block identifier
	* 
	*/
	public function getBlock() {
		return $this->_block;
	}
	
}