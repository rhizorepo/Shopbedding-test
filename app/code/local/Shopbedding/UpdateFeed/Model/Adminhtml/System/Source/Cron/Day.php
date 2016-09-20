<?php
/**
 * Source for cron frequency 
 *
 * @category    Find
 * @package     Find_Feed
 */
class Shopbedding_UpdateFeed_Model_Adminhtml_System_Source_Cron_Day
{
    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array_combine(
            range(1, 7), array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
        );
    }
}
