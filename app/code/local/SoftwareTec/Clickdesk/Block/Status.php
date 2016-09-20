<?php

class SoftwareTec_Clickdesk_Block_Status extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $model = Mage::getModel('clickdesk/clickdesk');

        $this->setWidgetId($model->getWidgetId());
        return parent::_toHtml();
    }
}
