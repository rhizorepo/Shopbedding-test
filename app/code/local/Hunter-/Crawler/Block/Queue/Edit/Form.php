<?php
/**
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Hunter
 * @category    Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */
class Hunter_Crawler_Block_Queue_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _getModel()
    {
        return Mage::registry('current_model');
    }

    protected function _getHelper()
    {
        return Mage::helper('hunter_crawler');
    }

    protected function _getModelTitle()
    {
        return 'Crawler Queue';
    }

    protected function _prepareForm()
    {
        $model  = $this->_getModel();
        $modelTitle = $this->_getModelTitle();
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => $this->_getHelper()->__($modelTitle . ' Item'),
            'class'     => 'fieldset-wide',
        ));

        if ($model && $model->getId()) {
            $modelPk = $model->getResource()->getIdFieldName();
            $fieldset->addField($modelPk, 'hidden', array(
                'name' => $modelPk,
            ));
        }

        $fieldset->addField('page_key', 'text', array(
            'name'      => 'page_key',
            'label'     => $this->_getHelper()->__('Page URL'),
            'title'     => $this->_getHelper()->__('Page URL'),
            'required'  => true,
        ));

        $options = array_merge(
            array('' => 'Please select the entity type'),
            Mage::getModel('hunter_crawler/source_entitytype')->toOptionArray()
        );
        $fieldset->addField('entity_type', 'select', array(
            'name'      => 'entity_type',
            'label'     => $this->_getHelper()->__('Entity type'),
            'title'     => $this->_getHelper()->__('Entity type'),
            'options'   => $options,
            'required'  => true,
        ));

        if($model){
            $form->setValues($model->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
