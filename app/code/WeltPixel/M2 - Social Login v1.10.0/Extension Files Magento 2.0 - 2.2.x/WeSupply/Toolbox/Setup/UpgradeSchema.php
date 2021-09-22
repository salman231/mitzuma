<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package WeSupply\Toolbox\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $setup->getConnection()->addColumn(
                $setup->getTable('wesupply_orders'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Store Id'
                ]
            );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable('wesupply_orders'),
                    $setup->getIdxName('wesupply_orders', ['store_id']),
                    ['store_id']
                );
        }


        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $setup->startSetup();
            $installer = $setup;
            $installer->startSetup();

            $table = $installer->getConnection()
                ->newTable($installer->getTable('wesupply_returns_list'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'return_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    50,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Return id'
                );

            $installer->getConnection()->createTable($table);
            $installer->endSetup();

        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('wesupply_orders'),
                'is_excluded',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'unsigned' => true,
                    'default' => '0',
                    'comment' => 'Order was excluded from export'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {

            /** sales_quote */
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'delivery_timestamp',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' =>'Delivery Timestamp'
                ]
            );

            /** sales order */
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'delivery_timestamp',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' =>'Delivery Timestamp'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'delivery_utc_offset',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' =>'Delivery UTC Offset'
                ]
            );
        }
    }
}
