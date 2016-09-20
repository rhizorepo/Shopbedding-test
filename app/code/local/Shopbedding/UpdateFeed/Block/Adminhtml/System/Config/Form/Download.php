<?php
/**
 * Button for download feed file
 *
 * Class Shopbedding_UpdateFeed_Block_Adminhtml_System_Config_Form_Download
 */
class Shopbedding_UpdateFeed_Block_Adminhtml_System_Config_Form_Download
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url1 = $this->getUrl('adminhtml/file/download/type/xlsx');
        $url2 = $this->getUrl('adminhtml/file/download/type/csv');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Download feed (xlsx)')
            ->setOnClick("setLocation('$url1')")
            ->toHtml();


        $html .= "<br><br>" . $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Download feed (csv)')
            ->setOnClick("setLocation('$url2')")
            ->toHtml();

        return $html;
    }
}