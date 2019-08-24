<?php 
namespace Naxero\Translation\Setup;

use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * Installs DB schema for the module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        $installer = $setup;
        $installer->startSetup();

        // Define the files table
        $table1 = $installer->getConnection()
            ->newTable($installer->getTable('naxero_translation_file'))
            ->addColumn(
                'file_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'File ID'
            )
            ->addColumn('file_path', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
            ->addColumn('file_content', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
            ->addColumn('file_is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is The File Active ?')
            ->addColumn('file_creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('file_update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->addColumn('file_override', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
            ->addIndex($installer->getIdxName('translation_file_index', ['file_id']), ['file_id'])
            ->setComment('Naxero Translation Files');

        // Define the logs table
        $table2 = $installer->getConnection()
        ->newTable($installer->getTable('naxero_translation_logs'))
        ->addColumn(
            'file_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'File ID'
        )
        ->addColumn('file_row', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Row number')
        ->addColumn('comment', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
        ->setComment('Naxero Translation Logs');

        // Create the tables
        $installer->getConnection()->createTable($table1);
        $installer->getConnection()->createTable($table2);

        // En the setup
        $installer->endSetup();
    }
}