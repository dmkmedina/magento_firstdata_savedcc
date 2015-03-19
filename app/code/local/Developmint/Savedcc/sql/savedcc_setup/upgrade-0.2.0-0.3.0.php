<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('savedcc/savedcreditcards')}` ADD COLUMN `for_autoship` enum('y','n') NOT NULL default 'n';
");
$installer->endSetup();


