<?php

namespace Mageside\ShippingMatrixRates\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the ShippingMatrixRates module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('shipping_matrixrates'),
                'original_record_data',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 1024,
                    'nullable'  => true,
                    'comment'   => 'Original Data'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('shipping_matrixrates'),
                'dest_zip_to',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 10,
                    'nullable'  => true,
                    'comment'   => 'Destination Post Code (Zip)',
                    'after'     => 'dest_zip'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('shipping_matrixrates'),
                'dest_zip',
                'dest_zip_from',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 10,
                    'nullable'  => false,
                    'default'   => '*',
                    'comment'   => 'Destination Post Code (Zip)',
                ]
            );
        }

        /**
         * Increase field length to avoid error placing order if 'shipping_method' name too long.
         */
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable('quote_address'),
                'shipping_method',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 255,
                ]
            );
        }
    }
}
