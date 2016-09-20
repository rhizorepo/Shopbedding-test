<?php

class Shopbedding_Canonical_Model_Rule extends  Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('shop_canonical/rule');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $this->setUpdateTime(date('Y-m-d H:i:s'));
        if(!$this->getId()){
            $this->setCreatedTime(date('Y-m-d H:i:s'));
        }

        $this->setSource(trim($this->getSource(), '/'));
        $this->setTarget(trim($this->getTarget(), '/'));

        return $this;
    }


}