<?php 
$installer = $this;
$installer->startSetup();

$installer->run("
    
    DROP TABLE IF EXISTS {$this->getTable('bordership_orders')};
    CREATE TABLE {$this->getTable('bordership_orders')} (
      `order_id` INT( 10 ) NOT NULL ,
      `order_number` VARCHAR( 255 ) NOT NULL ,
      `order_reference` VARCHAR( 255 ) NOT NULL ,
      PRIMARY KEY ( `order_number` )
    ) ENGINE = InnoDB;
    
    DROP TABLE IF EXISTS {$this->getTable('bordership_tracks')};
    CREATE TABLE {$this->getTable('bordership_tracks')} (
      `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
      `track_id` INT( 11 ) NOT NULL ,
      `bjl_track_id` INT( 11 ) NOT NULL ,
      `parcel_identification_number` VARCHAR( 255 ) ,
      `inbound_parcel_number` VARCHAR( 255 ) ,
      PRIMARY KEY ( `id` )
    ) ENGINE = InnoDB;
    
    DROP TABLE IF EXISTS `bordership_tablerate`;
    CREATE TABLE `bordership_tablerate` (
      `id` int( 11 ) NOT NULL AUTO_INCREMENT,
      `website_id` int( 11 ) NOT NULL DEFAULT '0',
      `dest_country_id` varchar( 4 ) NOT NULL DEFAULT '0',
      `dest_region_id` int( 11 ) NOT NULL DEFAULT '0',
      `dest_zip` varchar( 10 ) NOT NULL DEFAULT '*',
      `condition_name` varchar( 20 ) NOT NULL,
      `condition_value` decimal( 12,4 ) NOT NULL DEFAULT '0.0000',
      `weight` int( 11 ) NOT NULL,
      `price` int( 11 ) NOT NULL,
      PRIMARY KEY ( `id` )
    ) ENGINE=InnoDB ;

    INSERT INTO {$this->getTable('sales_order_status')} (`status`,`label`) VALUES('pending_borderjump','Pending BorderJump');
    INSERT INTO {$this->getTable('sales_order_status_state')} (`status`,`state`,`is_default`) VALUES('pending_borderjump','pending_payment',0);

");

$installer->endSetup();
