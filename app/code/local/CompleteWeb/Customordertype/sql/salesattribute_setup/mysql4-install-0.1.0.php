<?php
$installer = $this;
$installer->startSetup();

$attribute  = array(
	'type'          => 'int',
	'backend_type'  => 'text',
	'frontend_input' => 'text',
	'is_user_defined' => true,
	'label'         => 'Order Type',
	'visible'       => true,
	'required'      => false,
	'user_defined'  => false,
	'searchable'    => false,
	'filterable'    => false,
	'comparable'    => false,
	'default'       => 0
);

$installer->addAttribute('order', 'order_type', $attribute);
$installer->addAttribute('quote', 'order_type', $attribute);
$installer->endSetup();
	 