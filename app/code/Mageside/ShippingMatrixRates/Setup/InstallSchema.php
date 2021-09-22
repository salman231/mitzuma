<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'shipping_matrixrates'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('shipping_matrixrates')
        )->addColumn(
            'pk',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Primary key'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'default' => '0'
            ],
            'Website Id'
        )->addColumn(
            'dest_country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            [
                'nullable' => false,
                'default' => '0'
            ],
            'Destination coutry ISO/2 or ISO/3 code'
        )->addColumn(
            'dest_region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'default' => '0'
            ],
            'Destination Region Id'
        )->addColumn(
            'dest_city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [
                'nullable' => false,
                'default' => '*'
            ],
            'Destination City'
        )->addColumn(
            'dest_zip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [
                'nullable' => false,
                'default' => '*'
            ],
            'Destination Post Code (Zip)'
        )->addColumn(
            'weight_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Weight From'
        )->addColumn(
            'weight_to',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Weight To'
        )->addColumn(
            'qty_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Quantity From'
        )->addColumn(
            'qty_to',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Quantity To'
        )->addColumn(
            'price_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Price From'
        )->addColumn(
            'price_to',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Price To'
        )->addColumn(
            'shipping_group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            [
                'nullable' => false,
                'default' => '*'
            ],
            'Shipping Group'
        )->addColumn(
            'customer_group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            [
                'nullable' => false,
                'default' => '*'
            ],
            'Customer Group'
        )->addColumn(
            'calc_logic',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'default' => ''
            ],
            'Calculation Logic'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Price'
        )->addColumn(
            'cost',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Cost'
        )->addColumn(
            'delivery_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [
                'nullable' => false
            ],
            'Delivery Method'
        )->addColumn(
            'notes',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [
                'nullable' => false
            ],
            'Notes'
        )->addIndex(
            $installer->getIdxName(
                'shipping_matrixrates',
                ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'dest_city',
                'weight_from', 'weight_to', 'qty_from', 'qty_to', 'price_from', 'price_to',
                'shipping_group', 'customer_group', 'delivery_method'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'dest_city',
            'weight_from', 'weight_to', 'qty_from', 'qty_to', 'price_from', 'price_to',
            'shipping_group', 'customer_group', 'delivery_method'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Shipping Matrix Rates'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
