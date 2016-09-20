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
class MageWorkshop_DetailedReview_Block_Adminhtml_ProsConsCommon_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_entityType;
    protected $_entityName;
    protected $_className;

    /**
     * @inherit
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
    }

    /**
     * @inherit
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('detailedreview');
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('entity_id' => $this->getRequest()->getParam('entity_id'))),
                'method' => 'post',
            )
        );

        $fieldSet = $form->addFieldset('add_question_form', array('legend' => $helper->__('General Information')));

        $fieldSet->addField('name', 'text', array(
            'label' => $helper->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldSet->addField('status', 'select', array(
            'label' => $helper->__('Status'),
            'name'   => 'status',
            'required' => true,
            'values' => Mage::getModel('detailedreview/source_common_status')->toOptionArray()
        ));

        $fieldSet->addField('wrote_by', 'select', array(
            'label'  => $this->__('Wrote By'),
            'name'   => 'wrote_by',
            'required' => true,
            'values' => Mage::getModel('detailedreview/source_common_wroteby')->toOptionArray(),
            'value' => '1'
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldSet->addField('store_ids', 'multiselect', array(
                'name'      => 'store_ids[]',
                'label'     => $helper->__('Store View'),
                'title'     => $helper->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
                'value'     => Mage::registry('proscons_data')->getStoreIds()
            ));
        } else {
            $fieldSet->addField('store_ids', 'hidden', array(
                'name'      => 'store_ids[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
        }

        $fieldSet->addField('sort_order', 'text', array(
            'label'    => Mage::helper('detailedreview')->__('Sort Order'),
            'name'     => 'sort_order',
            'required' => false
        ));

        $fieldSet->addField('entity_type', 'hidden', array(
            'name'     => 'entity_type',
            'required' => false
        ));

        if (Mage::registry('proscons_data')) {
            $form->setValues(Mage::registry('proscons_data')->getData());
        }
        Mage::dispatchEvent('detailedreview_adminhtml_prosconscommon_edit_prepare_form', array('form'=>$form));
        $this->setForm($form);
        $form->setUseContainer(true);
        return parent::_prepareForm();
    }
}
