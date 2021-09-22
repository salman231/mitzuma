<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace  Mageside\ShippingMatrixRates\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Recurring
 * @package Mageside\ShippingMatrixRates\Setup
 */
class Recurring implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Increase field length to avoid error placing order if 'shipping_method' name too long.
         */
        $setup->getConnection()->modifyColumn(
            $setup->getTable('quote_address'),
            'shipping_method',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 255,
            ]
        );

        $installer->endSetup();
    }
}
