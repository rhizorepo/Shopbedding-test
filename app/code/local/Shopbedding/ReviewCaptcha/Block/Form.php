<?php
/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Gorilla Core Team <core@magentocommerce.com>
 */
# require_once 'lib/recaptcha/recaptchalib.php';
require_once Mage::getBaseDir('lib').DS.'/recaptcha/recaptchalib.php';
class Shopbedding_ReviewCaptcha_Block_Form extends Mage_Review_Block_Form
{
    protected $_retings;
    
    public function getCaptchaHtml()
    {
        $publickey = '6LeCwcUSAAAAAAVbOCnYimH8uGa-nvVd1NBMdjW9';
        return recaptcha_get_html($publickey);
    }

    public function getAction()
    {
        $url = parent::getAction();
        return $url . '';
    }

    public function getCacheKeyInfo()
    {
        $productId = Mage::app()->getRequest()->getParam('id', false);
        return array(
            'SHOPBEDDING_REVIEWCAPTCHA_FORM',
            Mage::app()->getStore()->getCode(),
            'template' => $this->getTemplate(),
            'productId' => $productId
        );
        
    }
    
    /**
     * Return colection ratings
     * 
     * @see Mage_Review_Block_Form::getRatings()
     */
    public function getRatings()
    {
        if ($this->_retings === null) {
            $this->_retings = parent::getRatings();
        }
        
        return $this->_retings;
    }
    
    /**
     * Return collection of stars for rating
     * 
     * @return array
     */
    public function getRatingStars()
    {
        $ret    = array();
        
        $active = true;
        $data   = (array) $this->_viewVars['data']->getRatings();
        foreach ($this->getRatings() as $rating) {
            $voice = !empty($data[$rating->getId()]) ? $data[$rating->getId()] : false;
            foreach ($rating->getOptions() as $i=>$option) {/* @var $option Mage_Rating_Model_Rating_Option */
                if ($voice == false) {
                    $active = false;
                }
                
                $ret[$i+1] = $active;
                
                if ($option->getId() == $voice) {
                    $active = false;
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * Return true if $option is selected
     * 
     * @param Mage_Rating_Model_Rating_Option $option
     * @return boolean
     */
    public function isSelectedStar(Mage_Rating_Model_Rating_Option $option)
    {
        $data = (array) $this->_viewVars['data']->getRatings();
        return !empty($data[$option->getRatingId()]) ? $data[$option->getRatingId()] == $option->getId() : false;
    }
}
