<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order_payment'),
    'cammino_payment_transaction_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'comment' => 'Cammino Payment Transaction ID'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order_payment'),
    'cammino_payment_url',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'comment' => 'Cammino Payment URL'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order_payment'),
    'cammino_payment_digitable_line',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'comment' => 'Cammino Payment Digitable Line'
    )
);

$installer->endSetup();
