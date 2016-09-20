<?php

class SoftwareTec_Clickdesk_Model_Mysql4_Clickdesk extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('clickdesk/clickdesk', 'clickdesk_id');
    }

}
