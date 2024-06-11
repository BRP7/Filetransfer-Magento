<?php
echo "Starting Setup Script";

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('ccc_filetransfer/configuration');

if (!$installer->getConnection()->isTableExists($tableName)) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('ccc_filetransfer/configuration'))
        ->addColumn('configuration_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Configuration Id')
        ->addColumn('username', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'User Name')
        ->addColumn('password', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'Password')
        ->addColumn('port', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'Port')
        ->addColumn('host', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'Host')
        ->setComment('CCC Filetransfer Configuration Table');
    $installer->getConnection()->createTable($table);
}

$tableName = $installer->getTable('ccc_filetransfer/filetransfer');

if (!$installer->getConnection()->isTableExists($tableName)) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('ccc_filetransfer/filetransfer'))
        ->addColumn('filetransfer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Filetransfer Id')
        ->addColumn('file_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'File Path')
        ->addColumn('configuration_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'Configuration Id')
        ->addColumn('file_date', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'Date')
        ->setComment('CCC Filetransfer Filetransfer Table');
    $installer->getConnection()->createTable($table);

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('ccc_filetransfer/filetransfer', 'configuration_id', 'ccc_filetransfer/configuration', 'configuration_id'),
        $installer->getTable('ccc_filetransfer/filetransfer'),
        'configuration_id',
        $installer->getTable('ccc_filetransfer/configuration'),
        'configuration_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
}

$installer->endSetup();
echo "Setup Script Completed";
