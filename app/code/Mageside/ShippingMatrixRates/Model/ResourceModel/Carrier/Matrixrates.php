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
class Matrixrates extends AbstractMatrixrates
{
    /**
     * Array of destination conditions by priority
     *
     * @var array
     */
    protected $_destinationConditions = [];

    protected $_destinationCountryConditions = [
        "dest_country_id = :country_id",
        "dest_country_id = '0'"

    ];

    protected $_destinationRegionConditions = [
        " AND dest_region_id = :region_id",
        " AND dest_region_id = '0'"
    ];

    protected $_destinationCityConditions = [
        " AND LOWER(dest_city) = LOWER(:city)",
        " AND dest_city = '*'"
    ];

    public $destinationZipcodeConditions = [];

    public $bind = [];

    /**
     * Return matrix rates array or false by rate request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $items = $request->getAllItems();
        if (empty($items) || ($items=='')) {
            return [];
        }

        /** @var $method "per_item|per_row|per_group|highest_group" */
        $method = $this->_carrierConfig->getConfig('condition_name');
        $itemsArray = $this->_itemHandler->_prepareItems($items);

        if ($method == 'per_group' || $method == 'highest_group') {
            $itemsPrepared = $this->_itemHandler->_sortItemsByGroup($itemsArray);
        } else {
            $itemsPrepared = $itemsArray;
        }

        $this->_destinationConditions = $this->createDestinationConditionsArray($request);

        $result = [];
        $options = $this->_getShippingGroupLabels();
        $hasCommonRates = true;
        foreach ($itemsPrepared as $key => $item) {
            if ($hasCommonRates) {
                $shippingGroup = array_key_exists($item['shipping_group'], $options) ?
                    $options[$item['shipping_group']] :
                    $item['shipping_group'];
                $bind = [
                    ':website_id' => (int)$request->getWebsiteId(),
                    ':country_id' => $request->getDestCountryId(),
                    ':region_id' => (int)$request->getDestRegionId(),
                    ':city' => $request->getDestCity() ? $request->getDestCity() : '*',
                    ':weight' => $method == 'per_item' ? $item['weight'] / $item['qty'] : $item['weight'],
                    ':qty' => $method == 'per_item' ? 1 : $item['qty'],
                    ':price' => $method == 'per_item' ? $item['price'] / $item['qty'] : $item['price'],
                    ':shipping_group' => $shippingGroup,
                    ':customer_group' => $this->_getCustomerGroupCode() ? $this->_getCustomerGroupCode() : '*',
                ];

                $bind = array_merge($bind, $this->bind);

                //Get rate data
                $itemRateResult = $this->_getResult($bind);

                if (!empty($itemRateResult)) {
                    $itemRateResult = $this->_logicHandler->calculateLogic($itemRateResult, $item);
                }

                //Multiplication result
                if ($method == 'per_item') {
                    $itemRateResult = $this->_merger->_multipleResult($itemRateResult, $item['qty']);
                }

                //Merge rate results
                $result = ($method == 'highest_group') ?
                    $this->_merger->_mergeResults($itemRateResult, $result, 'highest') :
                    $this->_merger->_mergeResults($itemRateResult, $result, 'sum_first');

                $hasCommonRates = empty($result) ? false : true;
            }
        }

        return $result;
    }

    protected function createDestinationConditionsArray($request)
    {
        $conditions = [];
        foreach ($this->_destinationCountryConditions as $countryCondition) {
            foreach ($this->_destinationRegionConditions as $regionCondition) {
                foreach ($this->_destinationCityConditions as $cityCondition) {
                    if (empty($this->destinationZipcodeConditions)) {
                        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $request */
                        $zipCodeString = $request->getDestPostcode() ?
                            $request->getDestPostcode() :
                            '*';
                        $country = $request->getDestCountryId();
                        $this->createZipCodeConditions($country, $zipCodeString);
                    }
                    foreach ($this->destinationZipcodeConditions as $zipCodeCondition) {
                        $conditions[] = $countryCondition . $regionCondition . $cityCondition . $zipCodeCondition;
                    }
                }
            }
        }

        return $conditions;
    }

    /**
     * Tested
     *
     * @param $country
     * @param $zipString
     * @return $this
     */
    public function createZipCodeConditions($country, $zipString)
    {
        $zip = trim($zipString);
        $sqlConditions = [];
        $zipCodeMaxLength = $this->_carrierConfig->getConfig('zip_code_max_length') ?
            $this->_carrierConfig->getConfig('zip_code_max_length') :
            0;

        if ($zipString == '*') {
            $sqlConditions[] = " AND dest_zip_from = '*'";
            $this->destinationZipcodeConditions = $sqlConditions;

            return $this;
        }

        $sqlConditions[] = " AND LOWER(:postcode_eq) = LOWER(dest_zip_from)";
        $bind[':postcode_eq'] = $zip;

        if (is_numeric($zip)) {
            $sqlConditions[] = ' AND :postcode_between BETWEEN dest_zip_from AND dest_zip_to';
            $bind[':postcode_between'] = ($zipCodeMaxLength > 0 ? substr($zip, 0, $zipCodeMaxLength) : $zip);
        } else {
            if ($country == 'GB' || $country == 'IE') {
                if ($country == 'GB') {
                    //If country is UK
                    $longPostcode = substr_replace($zip, "", -3);
                    $longMatchPostcode = $shortMatchPostcode = trim($longPostcode);
                    if (preg_match('/^([a-zA-Z]*).*$/', $zip, $zipMatches) === 1) {
                        $shortMatchPostcode = $zipMatches[1];
                    }
                }
                else {
                    //If country is Ireland
                    $longMatchPostcode = $shortMatchPostcode = trim($zip);
                    if (preg_match('/^(([a-zA-Z]*)\d*).*$/', $zip, $zipMatches) === 1) {
                        $longMatchPostcode = $zipMatches[1];
                        $shortMatchPostcode = $zipMatches[2];
                    }
                }
                $sqlConditions[] = ' AND LOWER(dest_zip_from) = LOWER(:postcode_gb_long)';
                $bind[':postcode_gb_long'] = $longMatchPostcode;
                if (preg_match('/^([a-zA-Z]+)(\d+)$/', $longMatchPostcode, $matches) === 1) {
                    $fromOperator = $this->_carrierConfig->getConfig('from_filter_operator') == 'gt' ? '<' : '<=';
                    $toOperator = $this->_carrierConfig->getConfig('to_filter_operator') == 'lteq' ? '>=' : '>';
                    $gbZipRegExp = '^'.strtolower($matches[1]).'[0-9]+$';
                    $gbZipLetters = strtolower($matches[1]);
                    $gbZipNumbers = $matches[2];
                    $sqlConditions[] =
                        " AND LOWER(dest_zip_from) REGEXP :postcode_gb_regexp" .
                        " AND CONVERT(TRIM(LEADING :postcode_gb_letters FROM LOWER(dest_zip_from)),UNSIGNED INTEGER) {$fromOperator} :postcode_gb_num" .
                        " AND LOWER(dest_zip_to) REGEXP :postcode_gb_regexp" .
                        " AND CONVERT(TRIM(LEADING :postcode_gb_letters FROM LOWER(dest_zip_to)),UNSIGNED INTEGER) {$toOperator} :postcode_gb_num";
                    $bind[':postcode_gb_regexp'] = $gbZipRegExp;
                    $bind[':postcode_gb_letters'] = $gbZipLetters;
                    $bind[':postcode_gb_num'] =  $gbZipNumbers;
                }
                $sqlConditions[] = ' AND LOWER(dest_zip_from) = LOWER(:postcode_gb_short)';
                $bind[':postcode_gb_short'] = $shortMatchPostcode;
            } elseif ($country == 'CA') {
                //If country is Canada
                $shortPart = substr($zip, 0, 3);
                if (strlen($shortPart) >= 3 && is_numeric($shortPart[1]) && ctype_alpha($shortPart[2])) {
                    $zipFromRegExp='^'.strtolower($shortPart[0]).'[0-'.$shortPart[1].']';
                    $zipToRegExp='^'.strtolower($shortPart[0]).'['.$shortPart[1].'-9]';
                    $sqlConditions[] = ' AND LOWER(dest_zip_from) REGEXP :postcode_from AND LOWER(dest_zip_to) REGEXP :postcode_to';
                    $bind[':postcode_from'] = $zipFromRegExp;
                    $bind[':postcode_to'] =  $zipToRegExp;
                }
            } elseif (preg_match('/^(\d+)\s*[a-zA-Z]+$|^((\d+)\s*-\s*\d+)$/', $zip, $matches) === 1) {
                $firstPart = isset($matches[3]) ?
                    ($zipCodeMaxLength > 0 ? substr($matches[3], 0, $zipCodeMaxLength) : $matches[3]) :
                    ($zipCodeMaxLength > 0 ? substr($matches[1], 0, $zipCodeMaxLength) : $matches[1]);

                $digits = isset($matches[2]) ?
                    ($zipCodeMaxLength > 0 ?
                        substr(str_replace(['-', ' '], '', $matches[2]), 0, $zipCodeMaxLength) :
                        str_replace(['-', ' '], '', $matches[2])
                    ) :
                    '';

                if (!empty($digits) && $digits != $firstPart) {
                    $sqlConditions[] = " AND :postcode_digit_formated = dest_zip_from";
                    $bind[':postcode_digit_formated'] = $digits;
                }

                $sqlConditions[] = " AND :postcode_digit_first = dest_zip_from";
                $bind[':postcode_digit_first'] = $firstPart;

                $sqlConditions[] = ' AND :postcode_between BETWEEN dest_zip_from AND dest_zip_to';
                $bind[':postcode_between'] = $firstPart;
            }
        }

        $sqlConditions[] = " AND LOWER(:postcode_like) LIKE LOWER(dest_zip_from)";
        $bind[':postcode_like'] = $zip;

        $sqlConditions[] = " AND dest_zip_from = '*'";

        $this->destinationZipcodeConditions = $sqlConditions;
        $this->bind = $bind;

        return $this;
    }

    /**
     * Get shipping group array labels
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getShippingGroupLabels()
    {
        $attributeObject = $this->_eavConfig->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'shipping_group'
        );
        $selectOptions = $attributeObject->getSource()->getAllOptions(false);

        $options = [];
        foreach ($selectOptions as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }

    protected function _getResult($bind)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable());

        $this->_applyRequestFiltersToSelect($select);

        $whereExpression = 'CASE';
        $priority = 0;
        while ($caseSelect = $this->_getDestinationConditionSelect($connection, $priority)) {
            $whereExpression .= " WHEN ({$caseSelect}) > 0 THEN ({$this->_destinationConditions[$priority]})";
            $priority++;
        }

        if ($priority) {
            $whereExpression .= ' ELSE 1=2 END';
            $select->where($whereExpression);
        }

        $result = $connection->fetchAll($select, $bind);

        if ($result) {
            // Normalize destination zip code
            foreach ($result as $key => $resultRow) {
                if ($resultRow['dest_zip_from'] == '*') {
                    $result[$key]['dest_zip_from'] = '';
                }
                if ($resultRow['dest_zip_to'] == '*') {
                    $result[$key]['dest_zip_to'] = '';
                }
                if ($resultRow['dest_city'] == '*') {
                    $result[$key]['dest_city'] = '';
                }
            }
        }

        return $result;
    }

    /**
     * Return destination select by priority
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param number $priority
     * @return array|bool
     */
    protected function _getDestinationConditionSelect($connection, $priority)
    {
        if (!isset($this->_destinationConditions[$priority])) {
            return false;
        }

        $select = $connection->select()
            ->from($this->getMainTable(), ['count' => new \Zend_Db_Expr('count(1)')])
            ->where($this->_destinationConditions[$priority]);

        $this->_applyRequestFiltersToSelect($select);

        return $select;
    }

    /**
     * Return destination select by priority
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _applyRequestFiltersToSelect($select)
    {
        $fromOperator = $this->_carrierConfig->getConfig('from_filter_operator') == 'gt' ? '<' : '<=';
        $toOperator = $this->_carrierConfig->getConfig('to_filter_operator') == 'lteq' ? '>=' : '>';

        $select->where('weight_from ' . $fromOperator . ' :weight')
            ->where('weight_to ' . $toOperator . ' :weight')
            ->where('qty_from ' . $fromOperator . ' :qty')
            ->where('qty_to ' . $toOperator . ' :qty')
            ->where('price_from ' . $fromOperator . ' :price')
            ->where('price_to ' . $toOperator . ' :price')
            ->where('LOWER(shipping_group) = LOWER(:shipping_group) OR shipping_group = "*"')
            ->where('LOWER(customer_group) = LOWER(:customer_group) OR customer_group = "*"')
            ->where('website_id = :website_id');

        return $select;
    }

    /**
     * @return string
     */
    protected function _getCustomerGroupCode()
    {
        if ($ruleData = $this->_coreRegistry->registry('rule_data')) {
            $groupId = $ruleData->getCustomerGroupId();
        } else {
            $groupId = $this->_customerSession->getCustomerGroupId();
        }

        return $this->_customerGroupRepository->getById($groupId)->getCode();
    }
}
