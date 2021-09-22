<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

/**
 * Shipping matrix rates
 */
namespace Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Import extends AbstractMatrixrates
{
    /**
     * Import matrix rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Array of unique matrix rates keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = [];

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Count of imported matrix rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * Delimiter symbol for countries and regions
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * Array of table columns
     *
     * @var array
     */
    protected $_tableColumns = [
        'website_id'        => 'Website',
        'dest_country_id'   => 'Country',
        'dest_region_id'    => 'Region/State',
        'dest_city'         => 'City',
        'dest_zip_from'     => 'Zip From',
        'dest_zip_to'       => 'Zip To',
        'weight_from'       => 'Weight From',
        'weight_to'         => 'Weight From',
        'qty_from'          => 'Qty From',
        'qty_to'            => 'Qty To',
        'price_from'        => 'Price From',
        'price_to'          => 'Price To',
        'shipping_group'    => 'Shipping Group',
        'customer_group'    => 'Customer Group',
        'calc_logic'        => 'Advanced Calculations',
        'price'             => 'Shipping Price',
        'cost'              => 'Cost',
        'delivery_method'   => 'Delivery Method Name',
        'notes'             => 'Notes',
    ];

    /**
     * @var array
     */
    protected $_additionalTableColumns = [
        'original_record_data',
    ];

    public function getTableColumns()
    {
        return $this->_tableColumns;
    }

    public function getTableColumnNames()
    {
        return array_keys($this->getTableColumns());
    }

    public function getTableColumnCaption($name)
    {
        $tableColumns = $this->getTableColumns();

        return $tableColumns[$name];
    }

    /**
     * Upload matrix rates file and import data from it
     *
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Import
     * @see https://wiki.corp.x.com/display/MCOMS/Magento+Filesystem+Decisions
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        $settings = $object->getData('groups');
        if (empty($settings['matrixrates']['fields']['import']['value']['tmp_name'])) {
            return $this;
        }

        $csvFile = $settings['matrixrates']['fields']['import']['value']['tmp_name'];
        $website = $this->_storeManager->getWebsite($object->getScopeId());

        $this->_importWebsiteId = (int)$website->getId();
        $this->_importUniqueHash = [];
        $this->_importErrors = [];
        $this->_importedRows = 0;

        $tmpDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($csvFile);
        $stream = $tmpDirectory->openFile($path);

        // check and skip headers
        $delimiter = $this->_carrierConfig->getConfig('csv_delimiter') ?
            $this->_carrierConfig->getConfig('csv_delimiter') :
            ',';
        $headers = $stream->readCsv(0, $delimiter);
        if ($headers === false || count($headers) < (count($this->getTableColumnNames()) - 1)) {
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct Matrix Rates File Format.'));
        }

        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            $rowNumber = 1;
            $importData = [];

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website
            $condition = [
                'website_id = ?' => $this->_importWebsiteId,
            ];
            $connection->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $stream->readCsv(0, $delimiter))) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                /** @var array $rows */
                $rows = $this->_getImportRows($csvLine, $rowNumber);
                if ($rows !== false) {
                    $importData = array_merge($importData, $rows);
                }

                if (count($importData) >= 5000) {
                    $this->_saveImportData($importData);
                    $importData = [];
                }
            }
            $this->_saveImportData($importData);
            $stream->close();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollback();
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            $connection->rollback();
            $stream->close();
            $this->_logger->critical($e);
            if (preg_match('/(Duplicate entry (.*)) for key/', $e->getMessage(), $matches) === 1) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Found duplicate record with data %1', $matches[2])
                );
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        $connection->commit();

        if ($this->_importErrors) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->_importErrors)
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }

        return $this;
    }

    /**
     * Load directory countries
     *
     * @return \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Import
     */
    protected function _loadDirectoryCountries()
    {
        if ($this->_importIso2Countries !== null && $this->_importIso3Countries !== null) {
            return $this;
        }

        $this->_importIso2Countries = [];
        $this->_importIso3Countries = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Country\Collection */
        $collection = $this->_countryCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->_importIso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->_importIso3Countries[$row['iso3_code']] = $row['country_id'];
        }

        return $this;
    }

    /**
     * Load directory regions
     *
     * @return \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Import
     */
    protected function _loadDirectoryRegions()
    {
        if ($this->_importRegions !== null) {
            return $this;
        }

        $this->_importRegions = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
        $collection = $this->_regionCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->_importRegions[$row['country_id']][$row['code']] = (int)$row['region_id'];
        }

        return $this;
    }

    /**
     * Validate row for import and return matrix rates array or false
     * Errors will be added to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getImportRows($row, $rowNumber = 0)
    {
        $countColumns = count($this->getTableColumnNames()) - 1;

        // validate row
        if (count($row) < $countColumns) {
            $this->_importErrors[] = __('Please correct Matrix Rates format in the Row #%1.', $rowNumber);
            return false;
        }

        // we do not process extra columns
        $row = array_slice($row, 0, $countColumns);
        $originalRecord = $this->_getCsvRecord($row);

        // strip whitespace from the beginning and end of each row
        // and fill empty cells with asterisk
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
            if ($row[$k] == '') {
                $row[$k] = '*';
            }
        }

        array_unshift($row, $this->_importWebsiteId);
        $row = array_combine($this->getTableColumnNames(), $row);

        $row = $this->_getDecimalColumns($row, $rowNumber);
        if (!$row) {
            return false;
        }

        $rows = [];
        $item = $row;
        $rowZipTo = $row['dest_zip_to'];
        $zipFromRange = $this->_carrierConfig->getConfig('zip_from_range');
        $writeOriginalData = true;
        $destinationCountries = $this->_getDestinationCountries($row, $rowNumber);
        if ($destinationCountries === false) {
            return false;
        }
        foreach ($destinationCountries as $country) {
            $destinationRegions = $this->_getDestinationRegions($row, $country, $rowNumber);
            if ($destinationRegions === false) {
                return false;
            }
            foreach ($destinationRegions as $region) {
                $destinationCities = $this->_getDestinationCities($row, $rowNumber);
                if ($destinationCities === false) {
                    return false;
                }
                foreach ($destinationCities as $city) {
                    $destinationZips = $this->_getDestinationZips($row, $rowNumber);
                    if ($destinationZips === false) {
                        return false;
                    }
                    foreach ($destinationZips as $zip) {
                        $zipFrom = $zip;
                        $zipTo = $rowZipTo;
                        if ($zipTo === '*' && $zipFromRange) {
                            if (preg_match('/^(\d+)-(\d+)$/', trim($zip), $matchesZip) === 1) {
                                $zipFrom = $matchesZip[1];
                                $zipTo = $matchesZip[2];
                            } elseif (preg_match('/^([a-zA-Z]+\d+)-([a-zA-Z]+\d+)$/', trim($zip), $matchesZip) === 1) {
                                $zipFrom = $matchesZip[1];
                                $zipTo = $matchesZip[2];
                            }
                        }
                        $shippingGroup = $this->_getShippingGroup($row, $rowNumber);
                        if ($shippingGroup === false) {
                            return false;
                        }
                        foreach ($shippingGroup as $shipGroup) {
                            $customerGroup = $this->_getCustomerGroup($row, $rowNumber);
                            if ($customerGroup === false) {
                                return false;
                            }
                            foreach ($customerGroup as $custGroup) {
                                $item['dest_country_id'] = $country;
                                $item['dest_region_id'] = $region;
                                $item['dest_city'] = $city;
                                $item['dest_zip_from'] = $zipFrom;
                                $item['dest_zip_to'] = $zipTo;
                                $item['shipping_group'] = $shipGroup;
                                $item['customer_group'] = $custGroup;
                                $item['original_record_data'] = null;
                                if ($writeOriginalData) {
                                    $item['original_record_data'] = $originalRecord;
                                    $writeOriginalData = false;
                                }
                                $rows[] = $item;
                            }
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Import
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $this->getConnection()->insertArray(
                $this->getMainTable(),
                array_merge($this->getTableColumnNames(), $this->_additionalTableColumns),
                $data
            );
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * Process decimal columns
     *
     * @param $row
     * @param $rowNumber
     * @return bool
     */
    protected function _getDecimalColumns($row, $rowNumber)
    {
        $decimalColumns = [
            'weight_from', 'weight_to',
            'qty_from', 'qty_to',
            'price_from', 'price_to',
        ];
        foreach ($decimalColumns as $decimalColumn) {
            if ($row[$decimalColumn] == '*') {
                if (strpos($decimalColumn, '_from') !== false) {
                    $row[$decimalColumn] = -0.1;
                } elseif (strpos($decimalColumn, '_to') !== false) {
                    $row[$decimalColumn] = 999999;
                }
            } elseif (($value = $this->_parseDecimalValue($row[$decimalColumn])) !== false) {
                $row[$decimalColumn] = $value;
            } else {
                $this->_importErrors[] = __(
                    'Please correct %1 "%2" in the Row #%3.',
                    $this->getTableColumnCaption($decimalColumn),
                    $row[$decimalColumn],
                    $rowNumber
                );
                return false;
            }
        }

        return $row;
    }

    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     * @return bool|float
     */
    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (double)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }

    /**
     * Parse and validate countries data
     *
     * @param $row
     * @param $rowNumber
     * @return array|bool
     */
    protected function _getDestinationCountries($row, $rowNumber)
    {
        $data = explode($this->_delimiter, $row['dest_country_id']);
        $countries = [];
        if (count($data) > 0) {
            foreach ($data as $country) {
                $country = trim($country);
                if (isset($this->_importIso2Countries[$country])) {
                    $countries[] = $this->_importIso2Countries[$country];
                } elseif (isset($this->_importIso3Countries[$country])) {
                    $countries[] = $this->_importIso3Countries[$country];
                } elseif ($country == '*') {
                    $countries[] = '0';
                } else {
                    $this->_importErrors[] = __(
                        'Please correct Country "%1" in the Row #%2.',
                        $row['dest_country_id'],
                        $rowNumber
                    );
                    return false;
                }
            }
        } else {
            $this->_importErrors[] = __(
                'Please correct Country "%1" in the Row #%2.',
                $row['dest_country_id'],
                $rowNumber
            );
            return false;
        }

        return $countries;
    }

    /**
     * Parse and validate regions data
     *
     * @param $row
     * @param $country
     * @param $rowNumber
     * @return array|bool
     */
    protected function _getDestinationRegions($row, $country, $rowNumber)
    {
        $data = explode($this->_delimiter, $row['dest_region_id']);
        $regions = [];
        if (count($data) > 0) {
            foreach ($data as $region) {
                $region = trim($region);
                if ($country != '0' && isset($this->_importRegions[$country][$region])) {
                    $regions[] = $this->_importRegions[$country][$region];
                } elseif ($region == '*') {
                    $regions[] = 0;
                } else {
                    $this->_importErrors[] = __(
                        'Please correct Region/State "%1" in the Row #%2.',
                        $row['dest_region_id'],
                        $rowNumber
                    );
                    return false;
                }
            }
        } else {
            $this->_importErrors[] = __(
                'Please correct Region/State "%1" in the Row #%2.',
                $row['dest_region_id'],
                $rowNumber
            );
            return false;
        }

        return $regions;
    }

    /**
     * @param $row
     * @return array|bool
     */
    protected function _getDestinationCities($row, $rowNumber)
    {
        $data = explode($this->_delimiter, $row['dest_city']);
        $cities = [];
        if (count($data) > 0) {
            foreach ($data as $city) {
                $city = trim($city);
                $cities[] = $city;
            }
        } else {
            $this->_importErrors[] = __(
                'Please correct City "%1" in the Row #%2.',
                $row['dest_city'],
                $rowNumber
            );
            return false;
        }

        return $cities;
    }

    /**
     * @param $row
     * @return array|bool
     */
    protected function _getDestinationZips($row, $rowNumber)
    {
        $data = explode($this->_delimiter, $row['dest_zip_from']);
        $zips = [];
        if (count($data) > 0) {
            $range = null;
            foreach ($data as $zip) {
                $zip = trim($zip);
                $zips[] = $zip;
            }
        } else {
            $this->_importErrors[] = __(
                'Please correct Zip "%1" in the Row #%2.',
                $row['dest_zip_from'],
                $rowNumber
            );
            return false;
        }

        return $zips;
    }

    /**
     * @param $row
     * @return array|bool
     */
    protected function _getShippingGroup($row, $rowNumber)
    {
        $data = explode($this->_delimiter, $row['shipping_group']);
        $shippingGroup = [];
        if (count($data) > 0) {
            $range = null;
            foreach ($data as $group) {
                $group = trim($group);
                $shippingGroup[] = $group;
            }
        } else {
            $this->_importErrors[] = __(
                'Please correct Shipping Group "%1" in the Row #%2.',
                $row['shipping_group'],
                $rowNumber
            );
            return false;
        }

        return $shippingGroup;
    }

    /**
     * @param $row
     * @return array|bool
     */
    protected function _getCustomerGroup($row, $rowNumber)
    {
        $data = explode($this->_delimiter, $row['customer_group']);
        $customerGroup = [];
        if (count($data) > 0) {
            $range = null;
            foreach ($data as $group) {
                $group = trim($group);
                $customerGroup[] = $group;
            }
        } else {
            $this->_importErrors[] = __(
                'Please correct Customer Group "%1" in the Row #%2.',
                $row['customer_group'],
                $rowNumber
            );
            return false;
        }

        return $customerGroup;
    }

    /**
     * @param $row
     * @return string
     */
    protected function _getCsvRecord($row)
    {
        $data = [];
        foreach ($row as $column) {
            $data[] = '"' .
                str_replace(
                    ['"', '\\'],
                    ['""', '\\\\'],
                    $column
                ) .
                '"';
        }
        $delimiter = $this->_carrierConfig->getConfig('csv_delimiter') ?
            $this->_carrierConfig->getConfig('csv_delimiter') :
            ',';

        return implode($delimiter, $data);
    }
}
