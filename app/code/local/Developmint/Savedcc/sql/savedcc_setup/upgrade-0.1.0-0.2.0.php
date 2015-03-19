<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `{$installer->getTable('savedcc/savedcclegacy')}` (
      `id` int(11) NOT NULL auto_increment,
      `customer_id` int(11) NOT NULL,
      `has_saved` enum('y','n') NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
");
$installer->endSetup();

/*
 ALTER TABLE developmint_autoship_payments ADD COLUMN `refunded` enum('y','n') NOT NULL default 'n'
 */


