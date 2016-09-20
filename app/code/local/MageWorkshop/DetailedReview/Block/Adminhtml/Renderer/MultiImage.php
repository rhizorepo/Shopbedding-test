<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Renderer_MultiImage
    extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @inherit
     */
    protected function _construct()
    {
        $this->setTemplate('detailedreview/multiImage.phtml');
    }

    /**
     * @inherit
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->assign(array(
            'element' => $element
        ));
        $html = $this->toHtml() . $element->getAfterElementHtml();
        return $html;
    }
}
