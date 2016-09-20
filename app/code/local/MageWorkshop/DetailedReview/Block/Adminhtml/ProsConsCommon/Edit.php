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
abstract class MageWorkshop_DetailedReview_Block_Adminhtml_ProsConsCommon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_entityType;
    protected $_entityClass;
    protected $_entity;

    /**
     * @inherit
     */
    public function __construct()
    {
        $this->_entityClass = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
        $this->_entity = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);

        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_' . $this->_entityClass;
        $this->_blockGroup = 'detailedreview';

        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('detailedreview')->__('Save %s', $this->_entity));
        $this->_updateButton('save', 'id', 'save_button');

        $this->_updateButton('delete', 'label', Mage::helper('detailedreview')->__('Delete Item'));

        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
                'class'   => 'save',
            ),
            -100);
    }

    /**
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit'
        ));
    }

    /**
     * @inherit
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function saveAndContinueEdit(urlTemplate) {
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = template.evaluate();
                editForm.submit(url);
            }
        ";
        return parent::_prepareLayout();
    }

    /**
     * @inherit
     */
    public function getHeaderText()
    {
        if (Mage::registry('proscons_data') && Mage::registry('proscons_data')->getEntityId()) {
            return Mage::helper('detailedreview')->__("Edit Item '%s'", $this->escapeHtml(Mage::registry('proscons_data')->getName()));
        } else {
            return Mage::helper('detailedreview')->__('Add New %s', $this->_entity);
        }
    }
}
