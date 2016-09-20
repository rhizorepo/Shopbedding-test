<?php
     
    class SoftwareTec_Clickdesk_Model_Mysql4_Clickdesk_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
    {
        public function _construct()
        {
            //parent::__construct();
            $this->_init('clickdesk/clickdesk');
        }
    }