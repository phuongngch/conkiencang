<?php
namespace Gos\Tradein\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup,
                            ModuleContextInterface $context){
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.2.0') < 0) {

            $installer = $setup;

            if (!$installer->tableExists('gos_tradein_glass')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('gos_tradein_glass')
            )
            ->addColumn(
                'glass_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Glass ID'
            )
            ->addColumn(
                'glass_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Glass Code'
            )

            ->addColumn(
                'glass_mth',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Glass Month'
            )

            ->addColumn(
                'glass_make',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Glass Make'
            )

            ->addColumn(
                'glass_model',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Glass Family'
            )

            ->addColumn(
                'glass_variant',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Glass Variant'
            )

            ->addColumn(
                'glass_series',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Series'
            )

            ->addColumn(
                'glass_style',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Style'
            )

            ->addColumn(
                'glass_engine',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Engine'
            )

            ->addColumn(
                'glass_cc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass CC'
            )

            ->addColumn(
                'glass_size',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Size'
            )

            ->addColumn(
                'glass_transmission',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Transmission'
            )

            ->addColumn(
                'glass_width',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Width'
            )

            ->addColumn(
                'glass_nvic',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass NVIC'
            )

            ->addColumn(
                'glass_year',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass Year'
            )

            ->addColumn(
                'glass_bt',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass BT'
            )

            ->addColumn(
                'glass_et',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass ET'
            )

            ->addColumn(
                'glass_tt',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass TT'
            )

            ->addColumn(
                'glass_cc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => true'],
                'Glass CC'
            )

            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Glass Row Created At'
            )
            ->addColumn(
                'last_updated',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Glass Row Updated At'
            )
            ->setComment('Glass Table');
            $installer->getConnection()->createTable($table);
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/glass_upgrade.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Glass tablet created');

        }

        }

        $setup->endSetup();
    }
}


?>