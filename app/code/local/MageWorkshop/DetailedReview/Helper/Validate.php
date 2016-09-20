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
class MageWorkshop_DetailedReview_Helper_Validate extends Mage_Core_Helper_Abstract
{
    const VALIDATION_TYPE_FRONTEND = 'frontend';
    const VALIDATION_TYPE_BACKEND  = 'backend';

    protected $_validationRules = array ();

    public function __construct()
    {
        $this->_validationRules = array (
            'nickname' => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-4 maximum-length-20',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => 4, 'max' => 20, 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' => 'Nickname',
                'error' => $this->__('Nickname must be min 4 and max 20 characters')
            ),
            'title'    => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-4 maximum-length-50 not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => 4, 'max' => 50, 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' => 'Review Title',
                'error' => $this->__('Title must be min 4 and max 50 characters')
            ),
            'detail'   => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-4 maximum-length-5000',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => 4, 'max' => 5000, 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' =>'Overall Review',
                'error' => $this->__('Review must be min 4 and max 5000 characters')
            ),
            'user_pros' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-255 not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => 255, 'encoding' => 'UTF-8')
                ),
                'label' => 'Pros',
                'error' => $this->__('Pros have max 255 characters')
            ),
            'good_detail' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-255 not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => 255, 'encoding' => 'UTF-8')
                ),
                'label' => 'what do you like about this item?',
                'error' => $this->__('Must be max 255 characters')
            ),
            'user_cons' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-255 not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => 255, 'encoding' => 'UTF-8')
                ),
                'label' => 'Cons',
                'error' => $this->__('Cons have max 255 characters')
            ),
            'no_good_detail' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-255 not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => 255, 'encoding' => 'UTF-8')
                ),
                'label' => 'what do you dislike about this item?',
                'error' => $this->__('Must be max 255 characters')
            ),
            'video' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-youtube-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'Regex' => array('pattern' => '/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/')
                ),
                'label' => 'video',
                'error' => $this->__('Wrong to link video')
            ),
            'location' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-50 not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => 50, 'encoding' => 'UTF-8')
                ),
                'label' => 'Location',
                'error' => $this->__('Location has only letters, numbers and whitespace')
            ),
            'age' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-digits',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'Int' => true
                ),
                'label' => 'age',
                'error' => $this->__('Age must be integer')
            ),
            'height' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-number',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'Float' => true
                ),
                'allow_empty' => true,
                'label' => 'height',
                'error' => $this->__('Height must be number')
            )
        );


        if (!Mage::getSingleton('customer/session')->isLoggedIn() && Mage::getStoreConfig('detailedreview/settings_customer/email_field')) {
            $this->_validationRules['customer_email'] = array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-email',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'EmailAddress' => array(
                        'allow' => Zend_Validate_Hostname::ALLOW_URI
                    ),
                ),
                'required' => true,
                'label' => 'Email',
                'error' => $this->__('Please enter a valid email address. For example johndoe@domain.com.')
            );
        }
    }

    public function getFieldsToValidate()
    {
        return array_keys($this->_validationRules);
    }

    public function isRequired($field)
    {
        return isset($this->_validationRules[$field]['required']) ? $this->_validationRules[$field]['required'] : false;
    }

    public function getLabel($field)
    {
        return isset($this->_validationRules[$field]['label']) ? $this->_validationRules[$field]['label'] : '';
    }

    public function getFieldError($field)
    {
        return isset($this->_validationRules[$field]['error']) ? $this->_validationRules[$field]['error'] : 'Unable to post the review.';
    }

    public function getValidationRules($field = null, $area = self::VALIDATION_TYPE_FRONTEND)
    {
        return $this->_validationRules[$field][$area];
    }
}
