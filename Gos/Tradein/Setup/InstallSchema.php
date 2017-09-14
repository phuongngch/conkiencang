<?php
/**
 * Gos_Tradein extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Gos
 *                     @package   Gos_Tradein
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Gos\Tradein\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('gos_tradein_tradein')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('gos_tradein_tradein')
            )
            ->addColumn(
                'tradein_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Tradein ID'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein State'
            )
            ->addColumn(
                'license',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein License'
            )
            ->addColumn(
                'number_kms',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Number Kms'
            )
            ->addColumn(
                'year',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Year'
            )
            ->addColumn(
                'vehicle_make',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Vehicle Make'
            )
            ->addColumn(
                'vehicle_model',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Vehicle Model'
            )
            ->addColumn(
                'condition',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Condition'
            )
            ->addColumn(
                'one_owner',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable => false'],
                'Tradein Ône Owner'
            )
            ->addColumn(
                'never_written_off',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable => false'],
                'Tradein Never Written Off'
            )
            ->addColumn(
                'commercially',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable => false'],
                'Tradein Commercially'
            )
            ->addColumn(
                'vin',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Vin'
            )
            ->addColumn(
                'nvic',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Nvic'
            )
            ->addColumn(
                'valuatation',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Tradein Valuatation'
            )

            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Tradein Created At'
            )
            ->addColumn(
                'last_updated',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Tradein Updated At'
            )
            ->setComment('Tradein Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('gos_tradein_tradein'),
                $setup->getIdxName(
                    $installer->getTable('gos_tradein_tradein'),
                    ['state','license','number_kms','year','vehicle_make','vehicle_model','condition','vin','nvic','valuatation'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['state','license','number_kms','year','vehicle_make','vehicle_model','condition','vin','nvic','valuatation'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

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

        $installer->endSetup();
    }
}
