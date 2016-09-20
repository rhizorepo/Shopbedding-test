<?php
class BorderJump_BorderShip_Block_System_Config_Form_Field_Export extends Varien_Data_Form_Element_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $buttonBlock = $element->getForm()->getParent()->getLayout()->createBlock('adminhtml/widget_button');
        
        $params = array(
            'website' => $buttonBlock->getRequest()->getParam('website')
        );
        
        $data = array(
            'label'   => Mage::helper('bordership')->__('Export CSV'),
            'onclick' => 'setLocation(\''.Mage::helper('adminhtml')->getUrl("bordership/*/exportTablerates", $params) . 'conditionName/\' + $(\'carriers_tablerate_condition_name\').value + \'/tablerates.csv\' )',
            'class'   => '',
        );
        
        $buttonBlock->setData($data);
        $html = '<tr><td class="label">' . $element->getLabelHtml() . '</td><td class="value">' . $buttonBlock->toHtml() . '</td>';
        return $html;
    }
}