<?php
/**
 * Class Shopbedding_UpdateFeed_Block_Adminhtml_System_Config_Form_Upload
 */
class Shopbedding_UpdateFeed_Block_Adminhtml_System_Config_Form_Upload
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/file/upload');
        $html = <<<HTML
            <input id="import_file" name="upload_file"
                                    value=""
                                    title="Select File to Upload"
                                    type="file">
HTML;

        return $html;
    }
}