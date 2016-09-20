<?php
/**
 * Source for cron hours
 *
 * @category    Find
 * @package     Find_Feed
 */
class Shopbedding_UpdateFeed_Model_Adminhtml_System_Source_Cron_Hours
{
    /**
     * Fetch options array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $hours = array();
        for ($i = 0; $i <= 23; $i++) {
            $hours[] = array('label' => $i, 'value' => $i);
        }
        return $hours;
    }
}
