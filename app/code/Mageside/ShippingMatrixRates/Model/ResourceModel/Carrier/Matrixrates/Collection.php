<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates;

/**
 * Shipping matrix rates collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Directory/country table name
     *
     * @var string
     */
    protected $_countryTable;

    /**
     * Directory/country_region table name
     *
     * @var string
     */
    protected $_regionTable;

    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates',
            'Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates'
        );
        $this->_countryTable = $this->getTable('directory_country');
        $this->_regionTable = $this->getTable('directory_country_region');
    }

    /**
     * Initialize select, add country iso3 code and region name
     *
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $this->_select->joinLeft(
            ['country_table' => $this->_countryTable],
            'country_table.country_id = main_table.dest_country_id',
            ['dest_country' => 'iso3_code']
        )->joinLeft(
            ['region_table' => $this->_regionTable],
            'region_table.region_id = main_table.dest_region_id',
            ['dest_region' => 'code']
        );

        $this->addOrder('dest_country', self::SORT_ORDER_ASC);
        $this->addOrder('dest_region', self::SORT_ORDER_ASC);
        $this->addOrder('dest_zip', self::SORT_ORDER_ASC);
    }

    public function setExportColumnsView()
    {
        $connection = $this->getConnection();

        // return initial imported values into decimal columns
        $decimalColumns = [
            'weight_from', 'weight_to',
            'qty_from', 'qty_to',
            'price_from', 'price_to',
        ];
        foreach ($decimalColumns as $decimalColumn) {
            if (strpos($decimalColumn, '_from') !== false) {
                $this->_select->columns([
                    $decimalColumn => new \Zend_Db_Expr(
                        $connection->getCheckSql(
                            'main_table.'.$decimalColumn.' < 0',
                            '"*"',
                            'main_table.'.$decimalColumn
                        )
                    ),
                ]);
            } elseif (strpos($decimalColumn, '_to') !== false) {
                $this->_select->columns([
                    $decimalColumn => new \Zend_Db_Expr(
                        $connection->getCheckSql(
                            'main_table.'.$decimalColumn.' > 999998',
                            '"*"',
                            'main_table.'.$decimalColumn
                        )
                    ),
                ]);
            }
        }

        // we should export rows in the same order as it was imported
        $this->resetOrders();

        return $this;
    }

    public function resetOrders()
    {
        return $this->_orders = [];
    }

    /**
     * Add website filter to collection
     *
     * @param int $websiteId
     * @return \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates\Collection
     */
    public function setWebsiteFilter($websiteId)
    {
        return $this->addFieldToFilter('website_id', $websiteId);
    }

    /**
     * Add country filter to collection
     *
     * @param string $countryId
     * @return \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates\Collection
     */
    public function setCountryFilter($countryId)
    {
        return $this->addFieldToFilter('dest_country_id', $countryId);
    }
}
