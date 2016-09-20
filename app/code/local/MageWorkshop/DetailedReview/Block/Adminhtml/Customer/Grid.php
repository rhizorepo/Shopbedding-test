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
class MageWorkshop_DetailedReview_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    /**
     * @inherit
     */
    public function setCollection($collection)
    {
        if ($this->_isActive()) {
            $collection->addAttributeToSelect('is_banned_write_review');
        }
        parent::setCollection($collection);
    }

    /**
     * @inherit
     */
    public function addColumn($name, $params)
    {
        if ($this->_isActive()) {
            if ($name == 'action') {
                $helper = Mage::helper('detailedreview');
                self::addColumn(
                    'is_banned_write_review',
                    array(
                            'header'  => $helper->__('Is Banned from Write Review'),
                            'align'   => 'center',
                            'width'   => '80px',
                            'type'    => 'options',
                            'options' => array(
                                0 => $helper->__('No'),
                                1 => $helper->__('Yes')
                            ),
                            'default' => '0',
                            'index'   => 'is_banned_write_review'
                        )
                );
            }
        }
        return parent::addColumn($name, $params);
    }

    /**
     * @inherit
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if ($this->_isActive()) {
            $helper = Mage::helper('detailedreview');
            $this->getMassactionBlock()->addItem(
                'is_banned_write_review',
                array(
                    'label'      => $helper->__('Prevent this Customer from Posting Reviews'),
                    'url'        => $this->getUrl('adminhtml/mageworkshop_detailedreview_customer/massCustomerBanning'),
                    'additional' => array(
                        'status'     => array(
                            'name'   => 'is_banned_write_review',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $helper->__('What do?'),
                            'values' => array(
                                1 => $helper->__('Ban'),
                                0 => $helper->__('Lift Ban')
                            )
                        )
                    )
                )
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isActive()
    {
        return (bool) Mage::getStoreConfig('detailedreview/settings/enable');
    }

}
