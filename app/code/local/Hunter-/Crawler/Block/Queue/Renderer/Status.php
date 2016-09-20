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
class Hunter_Crawler_Block_Queue_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $statusMessage = '';
        if ($row->isLocked()) {
            $statusMessage = '<span style="color:red;">'
                . Hunter_Crawler_Model_Queue::QUEUE_ITEM_LOCK_STATUS_MESSAGE
                . '</span>';
        }
        return $statusMessage;
    }
}
