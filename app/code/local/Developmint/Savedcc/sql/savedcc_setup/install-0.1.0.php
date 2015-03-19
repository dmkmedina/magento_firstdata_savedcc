<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `{$installer->getTable('savedcc/savedcreditcards')}` (
      `cc_id` int(11) NOT NULL auto_increment,
      `customer_id` int(11) NOT NULL,
      `token` varchar(90) NOT NULL,
      `last4` varchar(4) NOT NULL,
      `card_type` varchar(2) NOT NULL,
      `active` ENUM('y','n'),
      `exp_year` varchar(4) NOT NULL,
      `exp_month` varchar(2) NOT NULL,
      `full_name` varchar(255) NOT NULL,
      PRIMARY KEY  (`cc_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");
$installer->endSetup();


