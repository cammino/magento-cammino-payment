<?php

$installer = $this;
$installer->startSetup();


$installer->run("
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` 
        ADD `cammino_payment_transaction_id` VARCHAR(255) DEFAULT NULL,
        ADD `cammino_payment_url` VARCHAR(255) DEFAULT NULL,
        ADD `cammino_payment_digitable_line` VARCHAR(255) DEFAULT NULL,
    ");

$installer->endSetup();
