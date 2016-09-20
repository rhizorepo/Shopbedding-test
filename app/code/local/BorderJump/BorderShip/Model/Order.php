<?php
 
class BorderJump_BorderShip_Model_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bordership/order');
    }
    
    public function loadByMageOrder($order) {
        /* Legacy support */
        $bsOrder = $this->load($order->getIncrementId(), 'order_reference');
        if ($bsOrder->hasData() && ! $bsOrder->isEmpty()) {
            return $bsOrder;
        }
        /* End legacy support */
        
        return $this->load($order->getEntityId(), 'order_id');
    }
}
