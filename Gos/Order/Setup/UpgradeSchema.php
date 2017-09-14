<?php
namespace Gos\Order\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableRealName = 'quickar_order_info';

        if (!$context->getVersion()) {
            // no previous version found, installation, InstallSchema was just executed
            // be careful, since everything below is true for installation!
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            if ($setup->tableExists($tableRealName)) {
                $tableName = $setup->getTable($tableRealName);
                $connection = $setup->getConnection();

                $connection->addColumn(
                    $tableName,
                    'amount_owning',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Amount Owning'
                    ]
                );

                $connection->addColumn(
                    $tableName,
                    'product',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 11,
                        'nullable' => true,
                        'comment' => 'Product ID'
                    ]
                );

                $connection->addColumn(
                    $tableName,
                    'option_price',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Option Price'
                    ]
                );

                $connection->addColumn(
                    $tableName,
                    'option_color',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Option Color'
                    ]
                );

                $connection->addColumn(
                    $tableName,
                    'accessory_price',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Accessory Price'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            if ($setup->tableExists($tableRealName)) {
                $tableName = $setup->getTable($tableRealName);
                $connection = $setup->getConnection();

                $connection->addColumn(
                    $tableName,
                    'created_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Created At'
                    ]
                );

                $connection->addColumn(
                    $tableName,
                    'updated_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Updated At'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
?>
