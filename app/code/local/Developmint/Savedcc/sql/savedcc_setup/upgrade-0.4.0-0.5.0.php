<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('savedcc/savedcreditcards')}` ADD COLUMN `admin_only` enum('y','n') NOT NULL default 'n';
");
$installer->endSetup();


