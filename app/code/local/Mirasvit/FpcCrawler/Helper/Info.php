<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Full Page Cache
 * @version   1.0.12
 * @build     587
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Helper_Info extends Mage_Core_Helper_Abstract
{
    public function getCrawlUrlLimitInfo($logged = null)
    {
        $config = Mage::getSingleton('fpccrawler/config');
        $crawlUrlLimit = $logged ? $config->getCrawlUrlLimit(true) : $config->getCrawlUrlLimit();
        if ($crawlUrlLimit >= Mirasvit_FpcCrawler_Model_Config::CRAWL_URL_DEFAULT_LIMIT) {
            $crawlUrlLimit = 'unlimited';
        }
        $html = $this->__('Limit for crawled urls: <b>%s</b>', $crawlUrlLimit);

        return $html;
    }

    public function getCronInfo($logged = null)
    {
        $html = array();

        $crawlerInfo = $logged ? $this->_getLastCronTime('fpc_crawlerlogged') : $this->_getLastCronTime('fpc_crawler');

        $html[] = $this->__('Last cron run time: <b>%s</b>', $this->_getLastCronTime(null));
        $html[] = $this->__('Last crawler job run time: <b>%s</b>', $crawlerInfo);
        $html[] = $this->__('Last URLs import run time: <b>%s</b>', $this->_getLastCronTime('fpc_log_import'));
        $html[] = $this->__('Last cache clear time (expired cache): <b>%s</b>', $this->_getLastCronTime('fpc_cache_clean_old'));

        return implode('<br>', $html);
    }

    protected function _getLastCronTime($jobCode)
    {
        $time = '-';

        $collection = Mage::getModel('cron/schedule')->getCollection()
            ->setOrder('executed_at', 'desc');
        if ($jobCode) {
            $collection->addFieldToFilter('job_code', $jobCode);
        }

        $collection->getSelect()->limit('1');
        $cron = $collection->getFirstItem();

        if ($cron->getExecutedAt()) {
            $time = Mage::getSingleton('core/date')->date('d.m.Y H:i', strtotime($cron->getExecutedAt()));
        }

        return $time;
    }
}
