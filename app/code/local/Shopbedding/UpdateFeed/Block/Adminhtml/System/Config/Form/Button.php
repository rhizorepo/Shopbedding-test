<?php

class Shopbedding_UpdateFeed_Block_Adminhtml_System_Config_Form_Button extends
    Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/cron/runUpdateFeedCron');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Update Feed Now')
            ->setOnClick("setLocation('$url')")
            ->toHtml();

        return $html;
    }
}