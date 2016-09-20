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
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */
class Hunter_Crawler_Block_Result extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup      = 'hunter_crawler';
        $this->_controller      = 'result';
        $this->_headerText      = $this->__('Crawler Result');
        $this->_addButton('goto_queue', array(
            'label'   => Mage::helper('hunter_crawler')->__('Go to queue'),
            'onclick' => "setLocation('{$this->getUrl('*/queue/index')}')",
        ));
        parent::__construct();
        $this->_removeButton('add');
    }
}

