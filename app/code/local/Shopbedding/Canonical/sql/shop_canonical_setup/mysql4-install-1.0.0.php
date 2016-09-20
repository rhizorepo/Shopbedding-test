<?php

$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `canonical_rules` (
  `id` int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  `source` varchar(1024) NOT NULL default '',
  `target` varchar(1024) NOT NULL default '',
  `created_time` date NOT NULL,
  `update_time` date NOT NULL,
  KEY `source` (`source`)
) ENGINE=InnoDB charset=utf8 COLLATE=utf8_unicode_ci");


$installer->endSetup();