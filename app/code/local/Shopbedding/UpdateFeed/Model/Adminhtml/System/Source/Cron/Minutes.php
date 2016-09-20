<?php
/**
 * Source for cron minutes
 *
 * @category    Find
 * @package     Find_Feed
 */
class Shopbedding_UpdateFeed_Model_Adminhtml_System_Source_Cron_Minutes
{

    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $minutes = array();
        for ($i = 0; $i <= 59; $i++) {
            $minutes[] = array('label' => $i, 'value' => $i);
        }
        return $minutes;
    }
}
