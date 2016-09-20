<?php

$installer = $this;
$installer->startSetup();
$installer->run("

 -- DROP TABLE IF EXISTS {$this->getTable('clickdesk')};
    CREATE TABLE {$this->getTable('clickdesk')} (
        `clickdesk_id` int(11) unsigned NOT NULL auto_increment,
        `widgetid` varchar(255) NOT NULL default '',
        PRIMARY KEY (`clickdesk_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
