<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->modifyColumn(
    $installer->getTable('sales_flat_order_payment'),
    'cammino_payment_url',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'comment'  => 'Cammino Payment URL'
    )
);

$installer->endSetup();
