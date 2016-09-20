<?php
class Shopbedding_Byos_IndexController extends Mage_Core_Controller_Front_Action {

    public function setAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }


}
?>
