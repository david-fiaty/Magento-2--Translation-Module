<?php 
namespace Naxero\Translation\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for the module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = 'naxero_translation_file';

        if ($installer->getConnection()->isTableExists($tableName) != true) {

            $table = $installer->getConnection()
                ->newTable($installer->getTable($tableName))
                ->addColumn(
                    'file_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'File ID'
                )
                ->addColumn('file_path', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
                ->addColumn('file_content', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
                ->addColumn('file_is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is The File Active?')
                ->addColumn('file_creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
                ->addColumn('file_update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
                ->addIndex($installer->getIdxName('translation_file_index', ['file_id']), ['file_id'])
                ->setComment('Naxero Translation files');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

}