<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE {$installer->getTable('heartbeat_warnings')} (
    `warning_id` int(11) NOT NULL auto_increment,
    `warning` varchar(255) NOT NULL,
    PRIMARY KEY (warning_id),
    UNIQUE (warning)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;
");
$installer->endSetup();