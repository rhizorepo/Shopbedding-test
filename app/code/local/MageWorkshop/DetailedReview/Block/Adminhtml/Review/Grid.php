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
class MageWorkshop_DetailedReview_Block_Adminhtml_Review_Grid extends Mage_Adminhtml_Block_Review_Grid
{
    /**
     * @inherit
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $helper = Mage::helper('detailedreview');
        $this->getMassactionBlock()->addItem('ban_author_for', array(
            'label'      => $helper->__('Prevent this Author from Posting Reviews'),
            'url'        => $this->getUrl('adminhtml/mageworkshop_detailedreview_customer/massBanning'),
            'additional' => array(
                'status' => array(
                    'name'   => 'ban_author_for',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $helper->__("For how long Author’s IP should be banned."),
                    'values' => array(
                        30   => $helper->__('30 Days'),
                        90   => $helper->__('90 Days'),
                        180  => $helper->__('180 Days'),
                        360  => $helper->__('360 Days'),
                        9999 => $helper->__('Permanently'),
                    )
                ))
            )
        );
        Mage::dispatchEvent('detailedreview_adminhtml_review_grid_prepare_massaction', array('block' => $this));
        return $this;
    }
}
