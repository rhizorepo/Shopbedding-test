<?php
/**
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Hunter
 * @category    Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */
class Hunter_Crawler_Block_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup      = 'hunter_crawler';
        $this->_controller      = 'queue';
        $this->_headerText      = $this->__('Crawler Queue');
        $this->_addButtonLabel  = $this->__('Add new URL to queue');
        $this->_addButton('goto_results', array(
            'label'   => Mage::helper('hunter_crawler')->__('Go to results'),
            'onclick' => "setLocation('{$this->getUrl('*/result/index')}')",
        ));

        /** @var Hunter_Crawler_Helper_Data $helper */
        $helper = Mage::helper('hunter_crawler');
        if (!$helper->isLockedCron()) {
            $this->_addButton('lock_fpc_refresh', array(
                'label'   => Mage::helper('hunter_crawler')->__('Stop FPC refresh cron'),
                'onclick' => "setLocation('{$this->getUrl('*/*/lockRefresh')}')",
            ));
        } else {
            $this->_addButton('unlock_fpc_refresh', array(
                'label'   => Mage::helper('hunter_crawler')->__('Unlock FPC refresh cron'),
                'onclick' => "setLocation('{$this->getUrl('*/*/unlockRefresh')}')",
            ));
        }

        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }
}
