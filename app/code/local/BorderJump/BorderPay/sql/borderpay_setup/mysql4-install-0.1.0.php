<?php 
$installer = $this;
$installer->startSetup();

$installer->run("
    
    DROP TABLE IF EXISTS {$this->getTable('borderpay_payments')};
    CREATE TABLE {$this->getTable('borderpay_payments')} (
      `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
      `payment_id` INT( 111 ) NOT NULL ,
      `order_number` VARCHAR( 255 ) NULL ,
      `processed` TINYINT( 1 ) NULL ,
      PRIMARY KEY ( `id` )
    ) ENGINE = InnoDB;
    
");

$installer->endSetup();
