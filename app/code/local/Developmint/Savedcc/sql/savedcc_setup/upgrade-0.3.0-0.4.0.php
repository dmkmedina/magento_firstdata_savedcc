<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('savedcc/savedcreditcards')}` ADD COLUMN `gateway` enum('P','F') NOT NULL default 'F';
");
$installer->endSetup();


