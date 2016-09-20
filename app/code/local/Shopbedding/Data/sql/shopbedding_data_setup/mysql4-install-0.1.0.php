<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 2/11/14
 * Time: 6:01 PM
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$dataFlowProfiles = Mage::getModel('dataflow/profile')->getCollection();
foreach ($dataFlowProfiles as $profile) {
    if ($profile->getName() == 'Import and Associate Configurable Products') {
        if ($profile->getActionsXml()) {
            $newViewActionsXml = str_replace('catalog/convert_adapter_product','catalog/convert_adapter_associatedproducts', $profile->getActionsXml());
            $profile->setActionsXml($newViewActionsXml);
            $profile->save();
        }
    }
}

$installer->endSetup();