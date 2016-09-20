<?php
 
class BorderJump_BorderShip_Model_Mysql4_Track extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('bordership/track', 'id');
    }
}
