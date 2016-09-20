<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_customerIdentifier;

    /**
     * @return $this
     */
    public function clearCacheAfterInstall()
    {
        /** @var array $allTypes */
        $allTypes = Mage::app()->useCache();
        foreach($allTypes as $type => $key) {
            Mage::app()->getCacheInstance()->cleanType($type);
            Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function reindexDataAfterInstall()
    {
        $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
        /** @var Mage_Index_Model_Process $process */
        foreach ($processes as $process) {
            if ($process->getStatus() != Mage_Index_Model_Process::STATUS_RUNNING) {
                $process->reindexAll();
            }
        }
        return $this;
    }

}
