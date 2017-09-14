<?php
namespace Gos\Order\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('quickar_order_info')) {
            $table = $setup->getConnection()->newTable(
                    $setup->getTable('quickar_order_info')
                )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Order Id'
                )->addColumn(
                    'duration',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Duration'
                )->addColumn(
                    'init_payment',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Init Payment'
                )->addColumn(
                    'monthly_payment',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    [],
                    'Monthly Payment'
                )->addColumn(
                    'trade_in',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    [],
                    'Trade In'
                )->addColumn(
                    'amount_credit',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    [],
                    'Amount Credit'
                )->addColumn(
                    'rate',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    [],
                    'Rate'
                )->addColumn(
                    'standing_finance',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Standing Finance'
                )->addColumn(
                    'miles',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Miles'
                )->addColumn(
                    'payment_option',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Payment Option'
                )->addColumn(
                    'total_amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Total Amount'
                )->addColumn(
                    'tradein_mileage',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Trade In Mileage'
                );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
?>
